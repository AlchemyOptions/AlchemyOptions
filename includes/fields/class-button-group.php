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

if( ! class_exists( __NAMESPACE__ . '\Button_Group' ) ) {

    class Button_Group extends Includes\Field {
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--button-group jsAlchemyButtonGroup" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"button-group"}\'>
                    <div class="field__side">
                        <input class="jsAlchemyButtonGroupInput" type="hidden" id="{{ID}}" name="{{ID}}" value="{{VALUE}}" />
                        <h2 class="field__label">{{TITLE}}</h2>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content">
                        <div class="button-group">{{CHOICES}}</div>
                    </div>
                </div>
            ';
        }

        public function alch_get_btn_group_choices( $choices ) {
            $choicesHTML = "";

            if( isset( $choices ) && alch_is_not_empty_array( $choices ) ) {
                foreach ( $choices as $choice ) {
                    $choicesHTML .= sprintf (
                        '<button%1$s data-value=\'' . esc_attr( $choice[ 'value' ] ) . '\' ' . $this->is_disabled( $choice[ 'disabled' ] ) . ' >' . $choice[ 'label' ] . '</button>',
                        $this->concat_attributes( array(
                            'class' => $this->is_active( $choice[ 'checked' ] ),
                            'type' => 'button'
                        ) )
                    );
                }
            }

            return $choicesHTML;
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

            if( isset( $field[ 'value' ] ) && "" !== $field[ 'value' ] ) {
                foreach ( $field[ 'choices' ] as $i => $choice ) {
                    $field[ 'choices' ][ $i ][ 'checked' ] = $choice[ 'value' ] === $field[ 'value' ];
                }
            }

            $field[ 'choices' ] = $this->alch_get_btn_group_choices( $field[ 'choices' ] );

            return $field;
        }

        public function is_active( $value ) {
            if ( (string) $value === (string) true )
                $result = "button button-secondary button-primary jsAlchemyButtonGroupChoice";
            else
                $result = 'button button-secondary jsAlchemyButtonGroupChoice';

            return $result;
        }
    }
}