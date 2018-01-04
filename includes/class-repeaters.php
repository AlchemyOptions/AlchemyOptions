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

if( class_exists( __NAMESPACE__ . '\Repeaters' ) ) {
    return;
}

class Repeaters {
    private $repeaters;

    public function __construct( $repeaters ) {
        if ( ! function_exists( 'alch_repeaters_id' ) || ! is_admin() || ! alch_is_not_empty_array( $repeaters ) ) {
            return;
        }

        $this->repeaters = $repeaters;

        add_action( 'admin_init', array( $this, 'add_repeaters' ) );
    }

    public function add_repeaters() {
        $saved_settings = get_option( alch_repeaters_id(), array() );

        $this->repeaters = apply_filters( alch_repeaters_id() . '_args', $this->repeaters );

        if ( $saved_settings !== $this->repeaters ) {
            update_option( alch_repeaters_id(), $this->repeaters );
        }
    }
}