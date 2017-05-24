<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_url_field' ) ) {
    function alch_url_field( $data, $value = '' ) {
        $value = '' !== $value ? $value : get_option( $data[ 'id' ], '' );

        return alch_populate_field_template( 'text', array(
            'type' => 'url',
            'id' => esc_attr( $data[ 'id' ] ),
            'title' => $data[ 'title' ],
            'attributes' => alch_concat_attributes( array(
                'placeholder' => 'http://',
                'type' => 'url',
                'id' => $data[ 'id' ],
                'name' => $data[ 'id' ],
                'class' => 'alchemy__input',
                'value' => $value
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : null,
        ) );
    }
}