<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_select_field' ) ) {
    function alch_select_field( $data, $value = '' ) {
        $storedVal = get_option( $data[ 'id' ], '' );
        $valToHave = '' !== $value
            ? $value
            : $storedVal[ 'value' ];

        $valToHave = ( '' == $valToHave && isset( $data[ 'selected' ] ) ) ? $data[ 'selected' ] : $valToHave;

        $id = isset( $data[ 'id' ] ) ? $data[ 'id' ] : '';

        return alch_populate_field_template( 'select', array(
            'id' => esc_attr( $id ),
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'attributes' => alch_concat_attributes( array(
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input alchemy__input--select',
            ) ),
            'options' => alch_concat_select_options( array(
                'selected' => $valToHave,
                'options' => isset( $data[ 'options' ] ) ? $data[ 'options' ] : array(),
                'optgroups' => isset( $data[ 'optgroups' ] ) ? $data[ 'optgroups' ] : array(),
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}