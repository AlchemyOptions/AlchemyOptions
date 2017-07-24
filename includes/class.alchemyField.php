<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Field' ) ) {

    class Alchemy_Field implements iAlchemy_Field {
        protected $template = '';

        protected $networkField = false;

        public function __construct( $networkField = false ) {
            $this->template = '';
            $this->networkField = $networkField;
        }

        public function normalize_field_keys( $field ) {
            $field[ 'description' ] = isset( $field[ 'desc' ] ) ? sprintf( '<div class="field__description"><p>%s</p></div>', $field[ 'desc' ] ) : '';
            $field[ 'title' ] = isset( $field[ 'title' ] ) ? $field[ 'title' ] : '';

            unset( $field[ 'desc' ] );

            if( ! isset( $field[ 'id' ] ) ) {
                //can be a sections or a textblock field

                return $field;
            }

            $savedData = $this->networkField
                ? get_site_option( $field[ 'id' ] )
                : get_option( $field[ 'id' ] );

            if( ! isset( $field[ 'value' ] ) ) {
                $field[ 'value' ] = is_array( $savedData ) ? $savedData[ 'value' ] : '';
            }

            return $field;
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

            if( is_array( $attrs ) && count( $attrs ) > 0 ) {
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