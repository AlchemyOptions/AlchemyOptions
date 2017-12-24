<?php

/**
 * @package Alchemy_Options\Includes
 *
 */

namespace Alchemy_Options\Includes;

//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Meta_Box' ) ) {
    return;
}

class Meta_Box {
    private $options;

    public function __construct( $options ) {
        if( ! isset( $options['post-types'] ) ) {
            return;
        }

        $this->options = $options;

        if( alch_is_not_empty_array( $options['post-types'] ) ) {
            foreach ( $options['post-types'] as $postType ) {
                if( post_type_exists( $postType ) ) {
                    add_action( 'add_meta_boxes_' . $postType, array( $this, 'add_meta_box' ) );
                    add_action( 'save_post', array( $this, 'save_meta_box' ), 1, 2 );
                }
            }

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        }
    }

    public function enqueue_assets() {
        wp_register_script( 'select2-scripts', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/vendor/select2/js/select2.min.js', array(), '4.0.3', true );
        wp_register_script( 'alchemy-scripts', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/scripts/alchemy.min.js', $this->get_scripts_deps(), ALCHEMY_OPTIONS_VERSION, true );
        wp_localize_script( 'alchemy-scripts', 'alchemyData', array(
            'adminURL' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alchemy_ajax_nonce' )
        ) );

        wp_register_style( 'alchemy-jquery', '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', array(), '1.12.1' );
        wp_register_style( 'select2-style', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/vendor/select2/css/select2.min.css', array(), '4.0.3' );
        wp_register_style( 'alchemy-styles', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/styles/alchemy.css', array( 'alchemy-jquery', 'select2-style' ), ALCHEMY_OPTIONS_VERSION );

        wp_enqueue_media();
        wp_enqueue_script( 'alchemy-scripts' );

        wp_enqueue_style( 'alchemy-styles' );
    }

    public function get_scripts_deps() {
        $deps = array(
            'jquery'
        );

        if( isset( $this->options['meta']['options'] ) && alch_is_not_empty_array( $this->options['meta']['options'] ) ) {
            $types = array_unique( alchemy_array_flatten( $this->walk_the_fields( $this->options['meta']['options'] ) ) );

            if( in_array( 'colorpicker', $types ) ) {
                $deps[] = 'iris';
            }

            if( in_array( 'slider', $types ) ) {
                $deps[] = 'jquery-ui-slider';
            }

            if( in_array( 'datepicker', $types ) ) {
                $deps[] = 'jquery-ui-datepicker';
            }

            if( in_array( 'repeater', $types ) ) {
                $deps[] = 'jquery-ui-sortable';
            }

            if( in_array( 'datalist', $types ) || in_array( 'post-type-select', $types ) || in_array( 'taxonomy-select', $types ) ) {
                $deps[] = 'select2-scripts';
            }
        }

        return $deps;
    }

    public function walk_the_fields( $fields, $repeaters = array() ) {
        $types = [];

        if( count( $repeaters ) > 0 ) {
            foreach ( $repeaters as $repeater ) {
                if( count( $repeater['fields'] ) > 0 ) {
                    foreach ( $repeater['fields'] as $field ) {
                        array_push( $fields, $field );
                    }
                }
            }
        }

        foreach ( $fields as $field ) {
            $types[] = $field['type'];

            if( 'sections' === $field['type'] ) {
                foreach( $field['sections'] as $section ) {
                    $types[] = $this->walk_the_fields( $section['options'] );
                }
            }

            if( 'field-group' === $field['type'] ) {
                $types[] = $this->walk_the_fields( $field['fields'] );
            }
        }

        return $types;
    }

    public function add_meta_box() {
        $metaBoxId = isset( $this->options['id'] ) ? $this->options['id'] : '';

        if( '' !== $metaBoxId ) {
            add_meta_box(
                $this->options['id'],
                $this->options['title'],
                array( $this, 'meta_box_html' ),
                $this->options['post-types']
            );
        }
    }

    public function save_meta_box( $post_id ) {
        $nonce_name = isset( $_POST[$this->options['id'] . '_meta_nonce'] ) ? $_POST[$this->options['id'] . '_meta_nonce'] : '';

        if ( ! wp_verify_nonce( $nonce_name, $this->options['id'] . '_save_meta' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // todo maybe current_user_can check

        foreach ( $this->options['meta']['options'] as $option ) {
            $passedValue = isset( $option['id'] ) && isset( $_POST[$option['id']] ) ? $_POST[$option['id']] : '';

            if( 'checkbox' === $option['type'] && "" === $passedValue ) {
                $passedValue = array();
            }

            if( 'datalist' === $option['type'] ) {
                $passedValue = array( $passedValue );
            }

            if( 'post-type-select' === $option['type'] ) {
                $passedValue = array(
                    'type' => $option['post-type'],
                    'ids' => $passedValue
                );
            }

            if( 'taxonomy-select' === $option['type'] ) {
                $passedValue = array(
                    'taxonomy' => $option['taxonomy'],
                    'ids' => $passedValue
                );
            }

            if( isset( $option['id'] ) ) {
                $value = new Database_Value( array(
                    'type' => $option['type'],
                    'value' => $passedValue
                ) );

                update_post_meta( $post_id, $option['id'], array(
                    'type' => $option['type'],
                    'value' => $value->get_safe_value()
                ) );
            }
        }
    }

    public function meta_box_html( $post ) {
        wp_nonce_field( $this->options['id'] . '_save_meta', $this->options['id'] . '_meta_nonce' );

        $optionsHTML = '';

        foreach ( $this->options['meta']['options'] as $option ) {
            if( isset( $option['id'] ) ) {
                $optionFields = new Fields_Loader(false, array(
                    'meta' => true,
                    'postID' => $post->ID,
                    'key' => $option['id']
                ));

                $optionsHTML .= '<div class="wrap alchemy">';
                $optionsHTML .= $optionFields->get_fields_html( array( $option ) );
                $optionsHTML .= '</div>';
            } else {
                // posts without id (like sections)
            }
        }

        echo $optionsHTML;
    }
}