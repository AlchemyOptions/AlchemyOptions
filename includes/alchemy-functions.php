<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_options_id' ) ) {
    function alch_options_id() {
        return apply_filters( 'alch_options_id', 'alchemy_options' );
    }
}

if ( ! function_exists('alch_network_options_id') ) {
    function alch_network_options_id() {
        return apply_filters( 'alch_multisite_options_id', 'alchemy_multisite_options' );
    }
}

if ( ! function_exists( 'alch_max_repeater_nesting_level' ) ) {
    function alch_max_repeater_nesting_level() {
        return apply_filters( 'alch_max_repeater_nesting_level', 3 );
    }
}

if ( ! function_exists( 'alch_get_option' ) ) {
    function alch_get_option( $optionID, $default = "" ) {
        $savedValue = get_option( $optionID );

        if( $savedValue['value'] ) {
            $valueInst = new Alchemy_Value( $savedValue );

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

            return apply_filters( "alch_value_{$optionID}", $valueInst->get_value() );
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

if ( ! function_exists( 'alch_get_network_option' ) ) {
    function alch_get_network_option( $optionID, $default = "" ) {
        $savedValue = get_site_option( $optionID );

        if( $savedValue['value'] ) {
            $valueInst = new Alchemy_Value( $savedValue, true );

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