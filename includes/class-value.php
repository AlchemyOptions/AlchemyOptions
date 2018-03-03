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

if( ! class_exists( __NAMESPACE__ . '\Value' ) ) {
    class Value {
        private $value;

        public function __construct( $rawValue, $network = false ) {
            $this->value = $rawValue;
            $this->isNetworlValue = $network;

            $this->filter_value_by_type();
        }

        public function filter_value_by_type() {
            switch ( $this->value['type'] ) {
                case 'slider' :
                    $this->value['value'] = (int) $this->value['value'];
                break;
                case 'radio' :
                case 'image-radio' :
                    $this->value['value'] = $this->value['value'][0];
                break;
                case 'post-type-select' :
                case 'taxonomy-select' :
                    $this->value['value'] = $this->modify_bespoke_select_value( $this->value['value'] );
                break;
                case 'field-group' :
                    $this->value['value'] = $this->modify_field_group_value( $this->value['value'] );
                break;
                case 'upload' :
                    $this->value['value'] = $this->modify_upload_value( $this->value['value'] );
                break;
                case 'repeater' :
                    $this->value['value'] = $this->modify_repeater_value( $this->value['value'] );
                break;
                case 'editor' :
                    $this->value['value'] = wp_specialchars_decode( $this->value['value'] );
                break;
                default : break;
            }
        }

        public function modify_bespoke_select_value( $value ) {
            if( $value['ids'] ) {
                return $value['ids'];
            }

            return [];
        }

        public function modify_upload_value( $value ) {
            if( $this->isNetworlValue ) {
                switch_to_blog(1);

                $valueToReturn = $this->get_attached_image( $value );

                restore_current_blog();
            } else {
                $valueToReturn = $this->get_attached_image( $value );
            }

            return $valueToReturn;
        }

        public function get_attached_image( $value ) {
            $valueToReturn = $value;
            $imageMeta = wp_get_attachment_metadata( $value );

            if( is_array( $imageMeta['sizes'] ) ) {
                $valueToReturn = array(
                    'id' => (int) $value,
                    'sizes' => array(),
                );

                foreach( $imageMeta['sizes'] as $sizeTitle => $sizeValue ) {
                    $valueToReturn['sizes'][$sizeTitle] = wp_get_attachment_image_src( $value, $sizeTitle );
                }

                $valueToReturn['sizes']['full'] = wp_get_attachment_image_src( $value, 'full' );
            }

            return $valueToReturn;
        }

        public function modify_field_group_value( $value ) {
            $modVal = [];

            foreach ( $value as $id => $val ) {
                if( '' !== $val['value'] && ! is_null( $val['value'] ) ) {
                    $modVal[$id] = $val['value'];
                }
            }

            return $modVal;
        }

        public function modify_repeater_value( $value ) {
            if( '' === $value ) {
                return array();
            }

            //remove temporarily hidden fields
            $value = array_filter($value, function( $item ){
                return $item['isVisible'] === 'true';
            });

            $value = array_map(function( $item ){
                $values = array();

                foreach ( $item['fields'] as $key => $val ) {
                    //$key can be 'null' in a field with no id
                    if( 'null' !== $key ) {
                        $repeaterCheck = explode( ':', $val['type'] );

                        if( 2 === count( $repeaterCheck ) && 'repeater' === $repeaterCheck[0] ) {
                            $item['fields'][$key]['type'] = 'repeater';

                            if( ! $item['fields'][$key]['value'] ) {
                                $item['fields'][$key]['value'] = [];
                            }

                            $values[$key] = $this->modify_repeater_value( $item['fields'][$key]['value'] );
                        } else {
                            $valInst = new self( $val, $this->isNetworlValue );

                            $values[$key] = $valInst->get_value();
                        }
                    }
                }

                if( isset( $item['typeID'] ) ) {
                    return array(
                        'type' => $item['typeID'],
                        'value' => $values
                    );
                }

                return $values;
            }, $value);

            return $value;
        }

        public function get_value() {
            return $this->value['value'];
        }
    }
}