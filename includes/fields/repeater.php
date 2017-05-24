<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if ( ! function_exists( 'alch_repeater_field' ) ) {
    function alch_repeater_field( $data ) {

        //todo: store repeaters top-level, concatenate ids with a delimiter to target
        //todo: each repeatee has hidden fields to hide/show it

        $textFieldHTML = "";

        if( isset( $data[ 'repeatees' ] ) && is_array( $data[ 'repeatees' ] ) ) {
            $repeateesCount = count( $data[ 'repeatees' ] );

            $textFieldHTML .= '<div class="alchemy__field alchemy__field--repeater jsAlchemyRepeaterField">';

            if( $data[ 'desc' ] ) {
                $textFieldHTML .= '<p>' . $data[ 'desc' ] . '</p>';
            }

            $textFieldHTML .= '<ul class="jsAlchemyRepeaterSortable">';
            $textFieldHTML .= '</ul>';

            if( $repeateesCount > 1 ) {
                $textFieldHTML .= '<button class="jsAlchemyRepeaterAdd button button-primary" type="button" data-repeatees=\'' . json_encode( $data[ 'repeatees' ] ) . '\'>' . __( 'Add new', 'alchemy-options' ) . '</button><button class="jsAlchemyRepeaterArrow button button-primary" type="button" data-repeatees=\'' . json_encode( $data[ 'repeatees' ] ) . '\'><span class="dashicons dashicons-arrow-down-alt2"></span></button>';
            } else {
                $textFieldHTML .= '<button class="jsAlchemyRepeaterAdd button button-primary" type="button" data-repeatees=\'' . json_encode( $data[ 'repeatees' ] ) . '\'>' . __( 'Add new', 'alchemy-options' ) . '</button><button class="jsAlchemyRepeaterArrow button button-primary" type="button" data-repeatees=\'' . json_encode( $data[ 'repeatees' ] ) . '\'>' . __( 'Choose type', 'alchemy-options' ) . ' <span class="dashicons dashicons-arrow-down-alt2"></span></button>';
            }

            $textFieldHTML .= '</div>';
        }

        return $textFieldHTML;
    }
}