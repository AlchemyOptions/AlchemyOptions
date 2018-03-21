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

if( class_exists( __NAMESPACE__ . '\Meta_Boxes' ) ) {
    return;
}

class Meta_Boxes {
    private static $boxes;

    function __construct( $boxes = array() ) {
        if( ! empty( $boxes ) ) {
            foreach ( $boxes as $box ) {
                new Meta_Box( $box );
            }
        }
    }

    public static function get_meta_boxes() {
        if ( ! isset( self::$boxes ) ) {
            self::$boxes = array();
        }

        return self::$boxes;
    }

    public static function add_meta_box( $box ) {
        if ( ! isset( self::$boxes ) ) {
            self::$boxes = array();
        }

        self::$boxes[] = $box;
    }
}