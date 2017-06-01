<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Button_Group_Field' ) ) {

    class Alchemy_Button_Group_Field extends Alchemy_Field {
        public function __construct() {
            parent::__construct();

            $this->template = '
                <div class="alchemy__field field field--button-group jsAlchemyButtonGroup" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"button-group"}\'>
                    <input class="jsAlchemyButtonGroupInput" type="hidden" id="{{ID}}" value="{{VALUE}}" />
                    <h2 class="field__label">{{TITLE}}</h2>
                    <div class="button-group">{{CHOICES}}</div>
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
                </div>
            ';
        }

        public function alch_get_btn_group_choices( $id, $choices ) {
            $choicesHTML = "";

            if( is_array( $choices ) && count( $choices ) > 0 ) {
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

            $field[ 'choices' ] = $this->alch_get_btn_group_choices( $field[ 'id' ], $field[ 'choices' ] );

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