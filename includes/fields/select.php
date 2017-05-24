<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_select_field' ) ) {
    function alch_select_field( $data, $value = '' ) {
        $value = '' !== $value ? $value : get_option( $data[ 'id' ], '' );

        return alch_populate_field_template( 'select', array(
            'id' => esc_attr( $data[ 'id' ] ),
            'title' => $data[ 'title' ],
            'attributes' => alch_concat_attributes( array(
                'id' => $data[ 'id' ],
                'name' => $data[ 'id' ],
                'class' => 'alchemy__input alchemy__input--select',
            ) ),
            'options' => alch_concat_select_options( array(
                'selected' => $value,
                'options' => $data[ 'options' ]
            ) ),
            'description' => $data[ 'desc' ]
        ) );
    }
}