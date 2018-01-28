<?php

use Alchemy_Options\Includes;

//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_options_id' ) ) {
    function alch_options_id() {
        return apply_filters( 'alch_options_id', 'alchemy_options' );
    }
}

if ( ! function_exists( 'alch_repeaters_id' ) ) {
    function alch_repeaters_id() {
        return apply_filters( 'alch_repeaters_id', 'alchemy_repeaters' );
    }
}

if ( ! function_exists('alch_network_options_id') ) {
    function alch_network_options_id() {
        return apply_filters( 'alch_network_options_id', 'alchemy_network_options' );
    }
}

if ( ! function_exists( 'alch_get_option' ) ) {
    function alch_get_option( $optionID, $default = "" ) {
        $savedValue = get_option( $optionID );

        if( ! $savedValue ) {
            return $default;
        }

        return apply_filters( "alch_value_{$optionID}", alch_parse_saved_value( $savedValue, $default ) );
    }
}

if ( ! function_exists( 'alch_get_post_meta' ) ) {
    function alch_get_post_meta( $postID, $metaID, $default = "" ) {
        $savedValue = get_post_meta( $postID, $metaID, true );

        if( ! $savedValue ) {
            return $default;
        }

        return alch_parse_saved_value( $savedValue, $default );
    }
}

if( ! function_exists( 'alch_parse_saved_value' ) ) {
    function alch_parse_saved_value( $savedValue, $default ) {
        if( $savedValue['value'] ) {
            $valueInst = new Includes\Value( $savedValue );

            //todo: check other fields so that they return the default value correctly
            switch ( $savedValue['type'] ) {
                case 'post-type-select' :
                case 'taxonomy-select' :
                case 'field-group':
                case 'repeater':
                    if( count( $valueInst->get_value() ) === 0 ) {
                        return $default;
                    }
                break;
                default : break;
            }

            return $valueInst->get_value();
        }

        return $default;
    }
}

if( ! function_exists( 'alchemy_array_flatten' ) ) {
    function alchemy_array_flatten($array = null) {
        $result = array();

        if (!is_array($array)) {
            $array = func_get_args();
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, alchemy_array_flatten($value));
            } else {
                $result = array_merge($result, array($key => $value));
            }
        }

        return $result;
    }
}

if( ! function_exists('alch_is_not_empty_array') ) {
    function alch_is_not_empty_array ($value ) {
        return is_array( $value ) && count( $value ) > 0;
    }
}

if( ! function_exists( 'alch_kses_stripslashes' ) ) {
    function alch_kses_stripslashes( $string ) {
        return preg_replace("%\\\\'%", "'", wp_kses_stripslashes( $string ));
    }
}

if ( ! function_exists( 'alch_get_network_option' ) ) {
    function alch_get_network_option( $optionID, $default = "" ) {
        $savedValue = get_site_option( $optionID );

        if( $savedValue['value'] ) {
            $valueInst = new Includes\Value( $savedValue, true );

            return apply_filters( "alch_network_value_{$optionID}", $valueInst->get_value() );
        }

        return $default;
    }
}

if( ! function_exists( 'alch_delete_value' ) ) {
    function alch_delete_value( $optionID ) {
        return update_option( $optionID, '' );
    }
}