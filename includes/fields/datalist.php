<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_datalist_field' ) ) {
    function alch_datalist_field( $data, $value = '' ) {
        $id = isset( $data[ 'id' ] ) ? esc_attr( $data[ 'id' ] ) : '';

        if( ! $id ) {
            return "";
        };

        $storedVal = get_option( $id, '' );
        $valToHave = '' !== $value
            ? $value
            : '' !== $storedVal
                ? $storedVal[ 'value' ]
                : '';

        var_dump($id);
        var_dump($storedVal);

        $choices = isset( $data[ 'choices' ] ) ? $data[ 'choices' ] : array();
        $choicesCount = count( $choices );

        $choicesHTML = '';

        if( $choicesCount <= 10 ) {
            if( ! alch_array_has_string_keys( $choices ) && 'array' !== gettype( $choices[0] ) ) {
                $choices = array_map( function( $item ){
                    return array( 'value' => $item, 'label' => $item );
                }, $choices );
            }

            foreach( $choices as $i => $choice ) {
                $choices[ $i ][ 'disabled' ] = isset( $choice[ 'disabled' ] ) ? $choice[ 'disabled' ] : false;
                $choices[ $i ][ 'checked' ] = isset( $choice[ 'checked' ] ) ? $choice[ 'checked' ] : false;
            }

            if( '' !== $storedVal ) {
                foreach ( $choices as $i => $choice ) {
                    $choices[ $i ][ 'checked' ] = in_array( $choice[ 'value' ], $storedVal );
                }
            }

            $choicesHTML .= '<fieldset>';
            $choicesHTML .= isset( $data[ 'title' ] ) ? '<legend class="field__label">' . $data[ 'title' ] . '</legend>' : '';
            $choicesHTML .= alch_get_radios_html( array(
                'id' => $id,
                'choices' => $choices
            ) );
            $choicesHTML .= '</fieldset>';

        } else if ( $choicesCount <= 30 ) {
            $choicesHTML .= isset( $data[ 'title' ] ) ? '<label class="field__label" for="' . $id . '">' . $data[ 'title' ] . '</label>' : "";

            $choicesHTML .= sprintf( '<select%1$s>', alch_concat_attributes( array(
                'id' => $id,
                'name' => $id,
                'class' => 'alchemy__input alchemy__input--select',
            ) ) );

            $choicesHTML .= alch_concat_select_options( array(
                'selected' => $valToHave,
                'choices' => isset( $data[ 'choices' ] ) ? $data[ 'choices' ] : array(),
            ) );

            $choicesHTML .= '</select>';
        } else if ( $choicesCount <= 100 ) {
            $choicesHTML .= isset( $data[ 'title' ] ) ? '<label class="field__label" for="' . $id . '">' . $data[ 'title' ] . '</label>' : "";
            $choicesHTML .= '<div class="datalist jsAlchemyDatalist jsAlchemyDatalistInplace" data-source=\'' . json_encode( $choices ) . '\'>';
            $choicesHTML .= sprintf( '<input %1$s />', alch_concat_attributes( array(
                'id' => $id,
                'name' => $id,
                'value' => $valToHave,
                'class' => 'jsAlchemyDatalistInput',
                'type' => 'search'
            ) ) );
            $choicesHTML .= '</div>';
        } else {
            $choicesHTML .= isset( $data[ 'title' ] ) ? '<label class="field__label" for="' . $id . '">' . $data[ 'title' ] . '</label>' : "";
            $choicesHTML .= '<div class="datalist jsAlchemyDatalist" data-nonce=\'' . json_encode( array( 'id' => $id . '_datalist_nonce', 'value' => wp_create_nonce( $id . '_datalist_nonce' ) ) ) . '\'>';
            $choicesHTML .= sprintf( '<input %1$s />', alch_concat_attributes( array(
                'id' => $id,
                'name' => $id,
                'value' => $valToHave,
                'class' => 'jsAlchemyDatalistInput',
                'type' => 'search'
            ) ) );
            $choicesHTML .= '</div>';
        }

        return alch_populate_field_template( 'datalist', array(
            'id' => $id,
            'choices' => $choicesHTML,
            'description' => isset( $data[ 'desc' ] ) ? $data[ 'desc' ] : '',
        ) );
    }
}