<?php

/**
 * @package Alchemy_Options\Includes\Fields
 *
 */

namespace Alchemy_Options\Includes\Fields;

use Alchemy_Options\Includes;

//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( __NAMESPACE__ . '\Checkbox' ) ) {

    class Checkbox extends Includes\Field {
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--checkbox" id="field--{{NAME}}" data-alchemy=\'{"id":"{{NAME}}","type":"checkbox"}\'>
                    <fieldset>
                        <div class="field__side">
                            <legend class="field__label">{{TITLE}}</legend>
                            {{DESCRIPTION}}
                        </div>
                        <div class="field__content">
                            {{CHOICES}}
                        </div>
                    </fieldset>
                </div>
            ';
        }

        public function get_checkbox_choices( $passedID, $choices ) {
            $checksHTML = "";

            if( isset( $choices ) && alch_is_not_empty_array( $choices ) ) {
                foreach ( $choices as $choice ) {
                    $id = esc_attr( $passedID . '_' . $this->make_label( $choice[ 'value' ] ) );

                    $checksHTML .= sprintf (
                        '<label%1$s><input%2$s data-value=\'' . esc_attr( $choice[ 'value' ] ) . '\' ' . $this->is_disabled( $choice[ 'disabled' ] ) . ' ' . $this->is_checked( $choice[ 'checked' ] ) . '/> ' . $choice[ 'label' ] . '</label><br>',
                        $this->concat_attributes( array( 'for' => $id ) ),
                        $this->concat_attributes( array( 'id' => $id, 'name' => $passedID . '[' . $choice[ 'value' ] . ']', 'type' => 'checkbox' ) )
                    );

                    $id = '';
                }
            }

            return $checksHTML;
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field[ 'name' ] = $field[ 'id' ];
            $field[ 'choices' ] = isset( $field[ 'choices' ] ) ? $field[ 'choices' ] : array();

            if( ! $this->array_has_string_keys( $field[ 'choices' ] ) && 'array' !== gettype( $field[ 'choices' ][0] ) ) {
                $field[ 'choices' ] = array_map( function( $item ){
                    return array( 'value' => $item, 'label' => $item );
                }, $field[ 'choices' ] );
            }

            foreach( $field[ 'choices' ] as $i => $choice ) {
                $field[ 'choices' ][ $i ][ 'disabled' ] = isset( $choice[ 'disabled' ] ) ? $choice[ 'disabled' ] : false;
                $field[ 'choices' ][ $i ][ 'checked' ] = isset( $choice[ 'checked' ] ) ? $choice[ 'checked' ] : false;
            }

            if( isset( $field[ 'value' ] ) && is_array( $field[ 'value' ] ) ) {
                foreach ( $field[ 'choices' ] as $i => $choice ) {
                    $field[ 'choices' ][ $i ][ 'checked' ] = in_array( $choice[ 'value' ], $field[ 'value' ] );
                }
            }

            $field[ 'choices' ] = $this->get_checkbox_choices( $field[ 'id' ], $field[ 'choices' ] );

            return $field;
        }

        public function is_checked( $value ) {
            return checked( $value, true, false );
        }
    }
}