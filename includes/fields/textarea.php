<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_textarea_field' ) ) {
    function alch_textarea_field( $data, $value = '' ) {
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        if( ! $id ) {
            return "";
        }

        $storedVal = get_option( $id, '' );
        $valToHave = '' !== $value
            ? $value
            : '' !== $storedVal
                ? $storedVal[ 'value' ]
                : '';

        return alch_populate_field_template( 'textarea', array(
            'type' => 'textarea',
            'id' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'attributes' => alch_concat_attributes( array(
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input alchemy__input--textarea'
            ) ),
            'value' => esc_textarea( $valToHave ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}