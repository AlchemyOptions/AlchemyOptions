<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_email_field' ) ) {
    function alch_email_field( $data, $value = '' ) {
        $value = '' !== $value ? $value : get_option( $data[ 'id' ], '' );
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        return alch_populate_field_template( 'text', array(
            'type' => 'email',
            'id' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'attributes' => alch_concat_attributes( array(
                'type' => 'email',
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input',
                'value' => $value
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}