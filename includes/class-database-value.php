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

if( ! class_exists( __NAMESPACE__ . '\Database_Value' ) ) {

    class Database_Value {
        private $value;

        public function __construct( $rawValue ) {
            $this->value = $rawValue;

            $this->sanitize_value_by_type();
        }

        public function sanitize_value_by_type() {
            switch ( $this->value['type'] ) {
                case 'url' :
                case 'text' :
                case 'password' :
                case 'tel' :
                case 'colorpicker' :
                case 'button-group' :
                case 'datepicker' :
                case 'upload' :
                case 'slider' :
                    $this->value['value'] = alch_kses_stripslashes( sanitize_text_field( $this->value['value'] ) );
                break;
                case 'textarea' :
                    $this->value['value'] = $this->sanitize_textarea_field( $this->value['value'] );
                break;
                case 'field-group' :
                    $this->value['value'] = $this->sanitize_field_group_field( $this->value['value'] );
                break;
                case 'repeater' :
                    $this->value['value'] = $this->sanitize_repeater_field( $this->value['value'] );
                break;
                case 'editor' :
                    $this->value['value'] = $this->sanitize_editor_field( $this->value['value'] );
                break;
                case 'email' :
                    $this->value['value'] = sanitize_email( $this->value['value'] );
                break;
                default : break;
            }
        }

        public function sanitize_textarea_field( $value ) {
            $allow_html = apply_filters( 'alch_allow_html_in_textarea', false );

            if ( ! $allow_html ) {
                return sanitize_textarea_field( alch_kses_stripslashes( $value ) );
            }

            return alch_kses_stripslashes( $value );
        }

        public function sanitize_field_group_field( $value ) {
            $valToReturn = array();

            if( is_array( $value ) ) {
                foreach( $value as $fieldID => $field ){
                    $safeVal = new self( $field );

                    $valToReturn[$fieldID] = array(
                        'type' => $field['type'],
                        'value' => $safeVal->get_safe_value(),
                    );
                }
            }

            return $valToReturn;
        }

        public function sanitize_editor_field( $value ) {
            global $allowedposttags;

            $allowed_html = apply_filters( 'alch_allowed_editor_html_tags', $allowedposttags );
            $allowed_protocols = apply_filters('alch_allowed_editor_protocols', wp_allowed_protocols());

            return wp_kses( alch_kses_stripslashes( $value ), $allowed_html, $allowed_protocols );
        }

        public function sanitize_repeater_field( $value ) {
            return array_map(function( $item ){
                $item['fields'] = array_map(function( $field ){
                    $safeVal = new self( $field );

                    return array(
                        'type' => $field['type'],
                        'value' => $safeVal->get_safe_value(),
                    );
                }, $item['fields']);

                return $item;
            }, $value);
        }

        public function get_safe_value() {
            return $this->value['value'];
        }
    }
}