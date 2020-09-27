<?php

namespace Alchemy\Fields;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( interface_exists( __NAMESPACE__ . '\Field_Interface' ) ) {
    return;
}

interface Field_Interface {
    public function register_type( $types );
    public function enqueue_assets();
    public function get_option_html( $data, $savedValue, $isMeta );
    public function sanitize_value( $value );
    public function prepare_value( $value, $id );
}