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

if( ! class_exists( __NAMESPACE__ . '\Select' ) ) {

    class Select extends Includes\Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--select" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"select"}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content">
                        <select {{ATTRIBUTES}}>{{OPTIONS}}</select>
                    </div>
                </div>
            ';
        }

        public function concat_select_options( $options ) {
            //todo: refactor HTML formation

            $optionsHTML = "";

            if( isset( $options['optgroups'] ) && alch_is_not_empty_array( $options['optgroups'] ) ) {
                foreach ( $options[ 'optgroups' ] as $group ) {
                    $optionsHTML .= '<optgroup label="' . esc_attr( $group[ 'label' ] ) . '" ' . ( isset( $group[ 'disabled' ] ) ? $this->is_disabled( $group[ 'disabled' ] ) : "" ) . '>';

                    if( ! $this->array_has_string_keys( $group[ 'choices' ] ) && 'array' !== gettype( $group[ 'choices' ][0] ) ) {
                        foreach ( $group[ 'choices' ] as $choice ) {
                            $optionsHTML .= '<option value="' . esc_attr( $choice ) . '" ' . $this->is_selected( $choice, $options[ 'value' ] ) . '>' . $choice . '</option>';
                        }
                    } else {
                        foreach ( $group[ 'choices' ] as $choice ) {
                            $optionsHTML .= '<option value="' . esc_attr( $choice[ 'value' ] ) . '" ' . $this->is_selected( $choice[ 'value' ], $options[ 'value' ] ) . ( isset( $choice[ 'disabled' ] ) ? $this->is_disabled( $choice[ 'disabled' ] ) : "" ) . '>' . $choice[ 'label' ] . '</option>';
                        }
                    }

                    $optionsHTML .= '</optgroup>';
                }
            } else if( isset( $options['choices'] ) && alch_is_not_empty_array( $options['choices'] ) ) {
                if( ! $this->array_has_string_keys( $options[ 'choices' ] ) && 'array' !== gettype( $options[ 'choices' ][0] ) ) {
                    foreach ( $options[ 'choices' ] as $choice ) {
                        $optionsHTML .= '<option value="' . esc_attr( $choice ) . '" ' . $this->is_selected( $choice, $options[ 'value' ] ) . '>' . $choice . '</option>';
                    }
                } else {
                    foreach ( $options[ 'choices' ] as $choice ) {
                        $optionsHTML .= '<option value="' . esc_attr( $choice[ 'value' ] ) . '" ' . $this->is_selected( $choice[ 'value' ], $options[ 'value' ] ) . ( isset( $choice[ 'disabled' ] ) ? $this->is_disabled( $choice[ 'disabled' ] ) : "" ) . '>' . $choice[ 'label' ] . '</option>';
                    }
                }
            }

            return $optionsHTML;
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field[ 'attributes' ] = $this->concat_attributes( array(
                'id' => $field[ 'id' ],
                'name' => $field[ 'id' ],
                'class' => 'alchemy__input alchemy__input--select',
            ) );

            $field[ 'options' ] = $this->concat_select_options( $field );

            return $field;
        }

        public function is_selected( $value, $selectedValue ) {
            return selected( $value, $selectedValue, false );
        }
    }
}