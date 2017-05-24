<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_password_field' ) ) {
    function alch_password_field( $data, $value = '' ) {
        $value = '' !== $value ? $value : get_option( $data[ 'id' ], '' );

        return alch_populate_field_template( 'password', array(
            'type' => 'password',
            'id' => esc_attr( $data[ 'id' ] ),
            'title' => $data[ 'title' ],
            'toggle-title' => $data[ 'toggle-title-text' ],
            'attributes' => alch_concat_attributes( array(
                'type' => 'password',
                'id' => $data[ 'id' ],
                'name' => $data[ 'id' ],
                'class' => 'alchemy__input',
                'value' => $value
            ) ),
            'description' => $data[ 'desc' ]
        ) );
    }
}