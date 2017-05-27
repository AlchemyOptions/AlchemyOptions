<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_password_field' ) ) {
    function alch_password_field( $data, $value = '' ) {
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        if( ! $id ) {
            return "";
        }

        $value = '' !== $value ? $value : get_option( $id, '' );

        return alch_populate_field_template( 'password', array(
            'id' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'toggle-title' => isset( $data[ 'toggle-title-text' ] ) ? $data[ 'toggle-title-text' ] : '',
            'attributes' => alch_concat_attributes( array(
                'type' => 'password',
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input',
                'value' => $value[ 'value' ]
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}