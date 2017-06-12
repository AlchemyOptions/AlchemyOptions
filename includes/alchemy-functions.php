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

            return apply_filters( "alch_value_{$optionID}", $valueInst->get_value() );
        }

        return $default;
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