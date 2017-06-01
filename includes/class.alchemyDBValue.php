<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_DB_Value' ) ) {
    class Alchemy_DB_Value {
        private $value;

        public function __construct( $rawValue ) {
            $this->value = $rawValue;

            $this->sanitize_value_by_type();
        }

        public function sanitize_value_by_type() {
            switch ( $this->value[ 'type' ] ) {
                case 'url' :
                case 'text' :
                case 'password' :
                case 'tel' :
                case 'colorpicker' :
                case 'datepicker' :
                    $this->value[ 'value' ] = sanitize_text_field( $this->value[ 'value' ] );
                break;
                case 'repeater' :
                    $this->value[ 'value' ] = $this->sanitize_repeater_field( $this->value[ 'value' ] );
                break;
                case 'email' :
                    $this->value[ 'value' ] = sanitize_email( $this->value[ 'value' ] );
                break;
                default : break;
            }
        }

        public function sanitize_repeater_field( $value ) {
            //todo: wel... sanitize it
            return $value;
        }

        public function get_safe_value() {
            return $this->value[ 'value' ];
        }
    }
}