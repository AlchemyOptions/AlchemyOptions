<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_radio_field' ) ) {
    function alch_radio_field( $data, $value = '' ) {
        $value = '' !== $value ? $value : get_option( $data[ 'name' ], '' );

        return alch_populate_field_template( 'radio', array(
            'name' => $data[ 'name' ],
            'title' => $data[ 'title' ],
            'choices' => alch_get_radios_html( array(
                'checked' => $value,
                'radios' => $data[ 'choices' ]
            ) ),
            'description' => $data[ 'desc' ]
        ) );
    }
}