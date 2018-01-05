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

if( class_exists( __NAMESPACE__ . '\Variations' ) ) {
    return;
}

class Variations {
    private $variations;

    public function __construct( $variations ) {
        if ( ! function_exists( 'alch_variations_id' ) || ! is_admin() || ! alch_is_not_empty_array( $variations ) ) {
            return;
        }

        $this->variations = $variations;

        add_action( 'admin_init', array( $this, 'add_variations' ) );
    }

    public function add_variations() {
        $saved_settings = get_option( alch_variations_id(), array() );

        $this->variations = apply_filters( alch_variations_id() . '_args', $this->variations );

        if ( $saved_settings !== $this->variations ) {
            update_option( alch_variations_id(), $this->variations );
        }
    }
}