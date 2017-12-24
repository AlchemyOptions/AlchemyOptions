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
        }
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

                $optionsHTML .= $optionFields->get_fields_html( array( $option ) );
            } else {
                // posts without id (like sections)
            }
        }

        echo $optionsHTML;
    }
}