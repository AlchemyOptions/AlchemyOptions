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

        $value = '' !== $value ? $value : get_option( $id, '' );

        return alch_populate_field_template( 'text', array(
            'type' => 'text',
            'id' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'attributes' => alch_concat_attributes( array(
                'type' => 'text',
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input',
                'value' => esc_attr( $value[ 'value' ] )
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}