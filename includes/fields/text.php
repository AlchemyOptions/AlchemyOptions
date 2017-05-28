<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_text_field' ) ) {
    function alch_text_field( $data, $value = '' ) {
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        if( ! $id ) {
            return "";
        }

        $storedVal = get_option( $data[ 'id' ], '' );

        $valToHave = '' !== $value
            ? $value
            : '' !== $storedVal
                ? $storedVal[ 'value' ]
                : '';

        return alch_populate_field_template( 'text', array(
            'type' => 'text',
            'id' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'attributes' => alch_concat_attributes( array(
                'type' => 'text',
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input',
                'value' => esc_attr( $valToHave )
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}