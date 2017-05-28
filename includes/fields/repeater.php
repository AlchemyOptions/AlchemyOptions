<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_repeater_field' ) ) {
    function alch_repeater_field( $data, $value = '' ) {
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        if( ! $id ) {
            return "";
        }

        $repeateeID = $data[ 'repeatees' ][0][ 'repeatee_id' ];

        $value = '' !== $value ? $value : get_option( $id, '' );

        return alch_populate_field_template( 'repeater', array(
            'id' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'add' => sprintf(
                '<button%1$s data-nonce=\'%5$s\' data-repeatee-id=\'%4$s\' data-repeater-id=\'%3$s\'>%2$s</button>',
                    alch_concat_attributes( array(
                        'class' => 'button button-primary jsAlchemyRepeaterAdd',
                        'type' => 'button'
                    ) ),
                    __( 'Add new', 'alchemy-options' ),
                    $id,
                    $repeateeID,
                    json_encode( array( 'id' => $id . '_repeater_nonce', 'value' => wp_create_nonce( $id . '_repeater_nonce' ) ) )
            ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}