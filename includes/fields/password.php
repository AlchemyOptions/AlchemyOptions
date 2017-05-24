<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_password_field' ) ) {
    function alch_password_field( $data, $value = '' ) {
        $value = '' !== $value ? $value : get_option( $data[ 'id' ], '' );
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        return alch_populate_field_template( 'password', array(
            'type' => 'password',
            'id' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'toggle-title' => isset( $data[ 'toggle-title-text' ] ) ? $data[ 'toggle-title-text' ] : '',
            'attributes' => alch_concat_attributes( array(
                'type' => 'password',
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input',
                'value' => $value
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}