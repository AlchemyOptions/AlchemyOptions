<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Checkbox_Field' ) ) {

    class Alchemy_Checkbox_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field field field--checkbox" id="field--{{NAME}}" data-alchemy=\'{"id":"{{NAME}}","type":"checkbox"}\'>
                    <fieldset>
                        <legend class="field__label">{{TITLE}}</legend>
                        {{CHOICES}}
                    </fieldset>
                    {{DESCRIPTION}}
                </div>
            ';
        }

        public function get_checkbox_choices( $id, $choices ) {
            $checksHTML = "";

            if( is_array( $choices ) && count( $choices ) > 0 ) {
                foreach ( $choices as $choice ) {
                    $id = esc_attr( $id . '_' . $this->make_label( $choice[ 'value' ] ) );

                    $checksHTML .= sprintf (
                        '<label%1$s><input%2$s data-value=\'' . esc_attr( $choice[ 'value' ] ) . '\' ' . $this->is_disabled( $choice[ 'disabled' ] ) . ' ' . $this->is_checked( $choice[ 'checked' ] ) . '/> ' . $choice[ 'label' ] . '</label><br>',
                        $this->concat_attributes( array( 'for' => $id ) ),
                        $this->concat_attributes( array( 'id' => $id, 'name' => $id, 'type' => 'checkbox' ) )
                    );
                }
            }

            return $checksHTML;
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field[ 'name' ] = $field[ 'id' ];

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