<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_url_field' ) ) {
    function alch_url_field( $data, $value = '' ) {
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        if( ! $id ) {
            return "";
        }

        $value = '' !== $value ? $value : get_option( $id, '' );

        return alch_populate_field_template( 'text', array(
            'type' => 'url',
            'id' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'attributes' => alch_concat_attributes( array(
                'placeholder' => 'http://',
                'type' => 'url',
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input',
                'value' => $value[ 'value' ]
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}