<?php

namespace Alchemy\Fields;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( interface_exists( __NAMESPACE__ . '\Field_Interface' ) ) {
    return;
}

interface Field_Interface {
    public function register_type( array $types ) : array;
    public function enqueue_assets() : void;
    public function get_option_html( array $data, $savedValue, string $type ) : string;
    public function sanitize_value( $value );
    public function prepare_value( $value, $id );
}