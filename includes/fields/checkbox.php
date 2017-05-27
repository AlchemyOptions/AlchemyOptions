<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_checkbox_field' ) ) {
    function alch_checkbox_field( $data, $value = '' ) {
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        if( ! $id ) {
            return "";
        }

        $choices = isset( $data[ 'choices' ] ) ? $data[ 'choices' ] : array();

        if( ! alch_array_has_string_keys( $choices ) && 'array' !== gettype( $choices[0] ) ) {
            $choices = array_map( function( $item ){
                return array( 'value' => $item, 'label' => $item );
            }, $choices );
        }

        foreach( $choices as $i => $choice ) {
            $choices[ $i ][ 'disabled' ] = isset( $choice[ 'disabled' ] ) ? $choice[ 'disabled' ] : false;
            $choices[ $i ][ 'checked' ] = isset( $choice[ 'checked' ] ) ? $choice[ 'checked' ] : false;
        }

        $value = '' !== $value ? $value : get_option( $id, '' );

        if( '' !== $value && is_array( $value[ 'value' ] ) ) {
            foreach ( $choices as $i => $choice ) {
                $choices[ $i ][ 'checked' ] = in_array( $choice[ 'value' ], $value[ 'value' ] );
            }
        }

        return alch_populate_field_template( 'checkbox', array(
            'name' => $id,
            'title' => isset( $data[ 'title' ] ) ? $data[ 'title' ] : '',
            'choices' => alch_get_checkboxes_html( array(
                'id' => $id,
                'choices' => $choices
            ) ),
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}