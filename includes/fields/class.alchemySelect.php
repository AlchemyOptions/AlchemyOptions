<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Select_Field' ) ) {

    class Alchemy_Select_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field field field--select" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"select"}\'>
                    <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                    <select {{ATTRIBUTES}}>{{OPTIONS}}</select>
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
                </div>
            ';
        }

        public function concat_select_options( $options ) {
            //todo: refactor HTML formation

            $optionsHTML = "";

            if( isset( $options[ 'optgroups' ] ) && is_array( $options[ 'optgroups' ] ) && count( $options[ 'optgroups' ] ) > 0 ) {
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
            } else if( is_array( $options[ 'choices' ] ) && count( $options[ 'choices' ] ) > 0 ) {
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