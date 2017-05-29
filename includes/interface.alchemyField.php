<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

interface iAlchemy_Field {
    public function get_html( $data );
    public function normalize_field_keys( $field );
}