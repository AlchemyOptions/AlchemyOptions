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

if( class_exists( __NAMESPACE__ . '\Network_Options' ) ) {
    return;
}

class Network_Options {
    private $options;

    public function __construct( $options ) {
        if ( ! function_exists( 'alch_network_options_id' ) || ! is_admin() || ! alch_is_not_empty_array( $options ) ) {
            return;
        }

        $this->options = $options;

        add_action( 'admin_init', array( $this, 'add_options' ) );
    }

    public function add_options() {
        $saved_settings = get_option( alch_network_options_id(), array() );

        $this->options = apply_filters( alch_network_options_id() . '_args', $this->options );

        if ( $saved_settings !== $this->options ) {
            update_option( alch_network_options_id(), $this->options );
        }
    }
}