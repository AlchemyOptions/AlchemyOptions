<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_textarea_field' ) ) {
    function alch_textarea_field( $data, $value = '' ) {
        $value = '' !== $value ? $value : get_option( $data[ 'id' ], '' );

        return alch_populate_field_template( 'textarea', array(
            'type' => 'textarea',
            'id' => esc_attr( $data[ 'id' ] ),
            'title' => $data[ 'title' ],
            'attributes' => alch_concat_attributes( array(
                'id' => $data[ 'id' ],
                'name' => $data[ 'id' ],
                'class' => 'alchemy__input alchemy__input--textarea'
            ) ),
            'value' => esc_textarea( $value ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : null,
        ) );
    }
}