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

if( ! class_exists( __NAMESPACE__ . '\Field' ) ) {

    class Field implements Field_Interface {
        protected $template = '';

        protected $networkField = false;

        protected $options = array();

        public function __construct( $networkField = false, $options = array() ) {
            $this->template = '';
            $this->networkField = $networkField;
            $this->options = $options;
        }

        public function normalize_field_keys( $field ) {
            $field[ 'description' ] = isset( $field[ 'desc' ] ) ? sprintf( '<div class="field__description"><p>%s</p></div>', $field[ 'desc' ] ) : '';
            $field[ 'title' ] = isset( $field[ 'title' ] ) ? $field[ 'title' ] : '';

            unset( $field[ 'desc' ] );

            if( ! isset( $field[ 'id' ] ) ) {
                //can be a sections or a textblock field

                return $field;
            }

            if( alch_is_not_empty_array( $this->options ) && isset( $this->options['meta'] ) && $this->options['meta'] ) {
                $key = $this->options['key'] !== 'alchemy-section' ? $this->options['key'] : $field['id'];

                $savedData = get_post_meta( $this->options['postID'], $key, true );
            } else {
                $savedData = $this->networkField
                    ? get_site_option( $field[ 'id' ] )
                    : get_option( $field[ 'id' ] );
            }

            if( ! isset( $field['variations'] ) ) {
                $savedVariations = $this->get_saved_variations();

                if( alch_is_not_empty_array( $savedVariations ) ) {
                    $field['variations'] = array();
                    $field['variations'] = $this->add_default_variation( $savedVariations );
                    $field['variations-select'] = $this->get_variations_html(
                        $field['id'],
                        $field['variations']
                    );
                    $field['variations-data'] = ',"variations":' . json_encode(array_map(function( $variation ){
                        return $variation['id'];
                    }, $field['variations']));
                } else {
                    $field['variations'] = array();
                    $field['variations-select'] = '';
                }
            } else {
                $field['variations'] = $this->add_default_variation( $field['variations'] );
                $field['variations-select'] = $this->get_variations_html(
                    $field['id'],
                    $field['variations']
                );
                $field['variations-data'] = ',"variations":' . json_encode(array_map(function( $variation ){
                    return $variation['id'];
                }, $field['variations']));
            }

            if( ! isset( $field['value'] ) ) {
                if( is_array( $savedData ) ) {
                    if( isset( $savedData['value']['variations'] ) && alch_is_not_empty_array( $savedData['value']['variations'] ) ) {
                        $field['value'] = array(
                            'alchemy-variations' => $savedData['value']['variations']
                        );
                    } else if ( alch_is_not_empty_array( $field['variations'] ) && ! alch_is_not_empty_array( $savedData['variations'] ) ) {
                        $field['value'] = array(
                            'alchemy-variations' => array(
                                alchemy_default_variation_id() => $savedData['value']
                            )
                        );
                    } else {
                        $field['value'] = $savedData['value'];
                    }
                } else {
                    $field['value'] = "";
                }
            }

            return $field;
        }

        public function add_default_variation( $variations ) {
            array_unshift( $variations, array(
                'id' => apply_filters( alchemy_default_variation_id(), 'alchemy_default_variation_id' ),
                'title' => apply_filters( alchemy_default_variation_title(), 'Default' )
            ) );

            return $variations;
        }

        public function get_saved_variations() {
            $savedVariations = get_option( alch_variations_id(), array() );

            if( alch_is_not_empty_array( $savedVariations ) ) {
                return $savedVariations;
            }

            return array();
        }

        public function get_variations_html( $id, $variations ) {
            if( ! alch_is_not_empty_array( $variations ) ) {
                return '';
            }

            $variationsHTML = sprintf(
                '<div class="variations"><label class="field__label" for="%1$s">%2$s</label><select id="%1$s" data-field-id="%3$s" class="jsAlchemyVariationsSelect">',
                $id . '_alchemy_variations_select',
                __( 'This field has variations', 'alchemy-options' ),
                $id
            );

            foreach ( $variations as $variation ) {
                $variationsHTML .= sprintf(
                    '<option value="%2$s">%1$s</option>',
                    $variation['title'],
                    $variation['id']
                );
            }

            $variationsHTML .= '</select></div>';

            return $variationsHTML;
        }

        public function get_html( $data ) {
            $fieldHTML = $this->template;

            foreach ( $data as $key => $val ) {
                if( 'string' === gettype( $val ) ) {
                    $fieldHTML = str_replace( "{{" . strtoupper( $key ) . "}}", $val, $fieldHTML );
                }
            }

            return $fieldHTML;
        }

        public function is_disabled( $value ) {
            return disabled( $value, true, false );
        }

        public function make_label( $text ) {
            return strtolower( str_replace( " ", "_", trim( $text ) ) );
        }

        public function concat_attributes( $attrs ) {
            $attrString = "";

            if( isset( $attrs ) && alch_is_not_empty_array( $attrs ) ) {
                foreach ( $attrs as $attrName => $attrValue ) {
                    $attrString .= sprintf( ' %1$s="%2$s"', $attrName, esc_attr( $attrValue ) );
                }
            }

            return $attrString;
        }

        public function array_has_string_keys( $array ) {
            return count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
        }
    }
}