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
            Meta_Boxes::add_meta_box( $this->options );

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
        global $pagenow;

        wp_register_script( 'select2-scripts', ALCHEMY_OPTIONS_DIR_URL . 'assets/vendor/select2/js/select2.min.js', array(), '4.0.3', true );
        wp_register_script( 'alchemy-scripts', ALCHEMY_OPTIONS_DIR_URL . 'assets/scripts/alchemy.min.js', $this->get_scripts_deps(), ALCHEMY_OPTIONS_VERSION, true );
        wp_localize_script( 'alchemy-scripts', 'alchemyData', array(
            'adminURL' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alchemy_ajax_nonce' )
        ) );

        wp_register_style( 'alchemy-jquery', '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', array(), '1.12.1' );
        wp_register_style( 'select2-style', ALCHEMY_OPTIONS_DIR_URL . 'assets/vendor/select2/css/select2.min.css', array(), '4.0.3' );
        wp_register_style( 'alchemy-styles', ALCHEMY_OPTIONS_DIR_URL . 'assets/styles/alchemy.css', array( 'alchemy-jquery', 'select2-style' ), ALCHEMY_OPTIONS_VERSION );

        // enqueue only on post editing screens
        if ( $pagenow === 'post-new.php' || $pagenow === 'post.php'  ) {
            wp_enqueue_media();
            wp_enqueue_script( 'alchemy-scripts' );

            wp_enqueue_style( 'alchemy-styles' );
        }
    }

    public function get_scripts_deps() {
        global $typenow;

        $boxes = Meta_Boxes::get_meta_boxes();
        $allOptionsForPostType = array();

        foreach ( $boxes as $box ) {
            if( in_array( $typenow, $box['post-types'] ) && ! empty( $box['meta']['options'] ) ) {
                foreach ( $box['meta']['options'] as $opt ) {
                    $allOptionsForPostType[] = $opt;
                }
            }
        }

        $deps = array(
            'jquery'
        );

        if( alch_is_not_empty_array( $allOptionsForPostType ) ) {
            $hasRepeaters = array_filter($allOptionsForPostType, function( $option ) {
                return strpos( $option['type'], 'repeater:' ) === 0;
            });
            $repeaters = array();

            if( alch_is_not_empty_array( $hasRepeaters ) ) {
                $repeatersIDs = array_map(function($repeater){
                    return explode( ':', $repeater['type'] )[1];
                }, $hasRepeaters);

                $savedRepeaters = get_option( alch_repeaters_id(), array() ) ;

                $repeaters = array_filter( $savedRepeaters, function( $repeater ) use ( $repeatersIDs ) {
                    return in_array( $repeater['id'], $repeatersIDs );
                } );
            }

            $types = array_unique( alchemy_array_flatten( $this->walk_the_fields( $allOptionsForPostType, $repeaters ) ) );

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
                if( isset( $repeater['fields'] ) ) {
                    if( alch_is_not_empty_array( $repeater['fields'] ) ) {
                        foreach ( $repeater['fields'] as $field ) {
                            array_push( $fields, $field );
                        }
                    }
                } else if( isset( $repeater['field-types'] ) ) {
                    if( alch_is_not_empty_array( $repeater['field-types'] ) ) {
                        foreach ( $repeater['field-types'] as $fieldType ) {
                            if( isset( $fieldType['fields'] ) && alch_is_not_empty_array( $fieldType['fields'] ) ) {
                                foreach ( $fieldType['fields'] as $field ) {
                                    array_push( $fields, $field );
                                }
                            }
                        }
                    }
                }
            }

            $types[] = 'repeater';
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
            $this->save_option( $post_id, $option );
        }
    }

    public function save_option( $post_id, $option ) {
        if( strpos( $option['type'], 'repeater:' ) === 0 ) {
            $fieldType = 'repeater';
            $passedValue = $this->normalise_repeater_value( $option );
        } else {
            $fieldType = $option['type'];
            $passedValue = $this->normalize_passed_value( $post_id, $option );
        }

        if( isset( $option['id'] ) ) {
            $value = new Database_Value( array(
                'type' => $fieldType,
                'value' => $passedValue
            ) );

            update_post_meta( $post_id, $option['id'], array(
                'type' => $fieldType,
                'value' => $value->get_safe_value()
            ) );
        }
    }

    public function normalise_repeater_value( $option ) {
        $passedValue = isset( $option['id'] ) && isset( $_POST[$option['id']] ) ? $_POST[$option['id']] : '';

        return json_decode( alch_kses_stripslashes( $passedValue ), true );
    }

    public function normalize_passed_value( $post_id, $option ) {
        $passedValue = isset( $option['id'] ) && isset( $_POST[$option['id']] ) ? $_POST[$option['id']] : '';

        switch( $option['type'] ) {
            case 'sections' :
                if( isset( $option['sections'] ) && alch_is_not_empty_array( $option['sections'] ) ) {
                    foreach( $option['sections'] as $passedSection ) {
                        if( isset( $passedSection['options'] ) && alch_is_not_empty_array( $passedSection['options'] ) ) {
                            foreach ( $passedSection['options'] as $passedOption ) {
                                $this->save_option( $post_id, $passedOption );
                            }
                        }
                    }
                }
            break;
            case 'checkbox' :
                if( "" === $passedValue ) {
                    $passedValue = array();
                }
            break;
            case 'datalist' :
                $passedValue = array( $passedValue );
            break;
            case 'post-type-select' :
                $passedValue = array(
                    'type' => $option['post-type'],
                    'ids' => $passedValue
                );
            break;
            case 'taxonomy-select' :
                $passedValue = array(
                    'taxonomy' => $option['taxonomy'],
                    'ids' => $passedValue
                );
            break;
            case 'field-group' :
                $newPassedValue = array();

                foreach ( $passedValue as $id => $value ) {
                    $neededField = array_filter( $option['fields'], function( $fld ) use( $id ) {
                        return $fld['id'] == $id;
                    } );

                    $neededField = array_values( $neededField );

                    $newPassedValue[$id] = array(
                        'type' => $neededField[0]['type'],
                        'value' => $value
                    );
                }

                $passedValue = $newPassedValue;
            break;
            default : break;
        }

        return $passedValue;
    }

    public function meta_box_html( $post ) {
        wp_nonce_field( $this->options['id'] . '_save_meta', $this->options['id'] . '_meta_nonce' );

        $optionsHTML = '';

        $optionsHTML .= '<div class="wrap alchemy jsAlchemyMetaBox">';

        foreach ( $this->options['meta']['options'] as $option ) {
            if( isset( $option['id'] ) || 'sections' === $option['type'] ) {
                $optionFields = new Fields_Loader(false, array(
                    'meta' => true,
                    'postID' => $post->ID,
                    'key' => isset( $option['id'] ) ? $option['id'] : "alchemy-section"
                ));
                $optionsHTML .= $optionFields->get_fields_html( array( $option ) );
            }
        }

        $optionsHTML .= '</div>';

        echo $optionsHTML;

        //hack to include editor assets. Will be removed when support of the wp_enqueue_editor() is high and there's a way to get the default editor settings for posts
        echo '<div class="hidden">';
        wp_editor( '', 'alchemy-temp-editor' );
        echo '</div>';
    }
}