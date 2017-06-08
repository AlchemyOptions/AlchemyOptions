<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Value' ) ) {
    class Alchemy_Value {
        private $value;

        public function __construct( $rawValue ) {
            $this->value = $rawValue;

            $this->filter_value_by_type();
        }

        public function filter_value_by_type() {
            switch ( $this->value[ 'type' ] ) {
                case 'repeater' :
                    $this->value[ 'value' ] = $this->filter_repeater_value( $this->value[ 'value' ] );
                break;
                default : break;
            }
        }

        public function filter_repeater_value( $value ) {
            //remove temporarily hidden fields
            $value = array_filter($value, function( $item ){
                return $item[ 'isVisible' ] === 'true';
            });

            return $value;
        }

        public function get_value() {
            return $this->value[ 'value' ];
        }
    }
}