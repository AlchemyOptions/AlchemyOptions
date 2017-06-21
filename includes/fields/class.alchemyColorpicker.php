<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Colorpicker_Field' ) ) {

    class Alchemy_Colorpicker_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--colorpicker alchemy__colorpicker jsAlchemyColorpicker" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"colorpicker"}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content">
                        <div class="alchemy__colorpicker-toolbar"><span class="alchemy__colorpicker-sample jsAlchemyColorpickerSample" style="background-color: {{VALUE}}"></span><input {{ATTRIBUTES}} /><button type="button" class="button button-secondary jsAlchemyColorpickerClear"><span class="dashicons dashicons-trash"></span></button></div>
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $passedAttrs = isset( $field[ 'attributes' ] ) ? $field[ 'attributes' ] : array();
            $mergedAttrs = array_merge( array(
                'type' => 'text',
                'id' => $field[ 'id' ],
                'value' => $field[ 'value' ],
                'class' => 'jsAlchemyColorpickerInput'
            ), $passedAttrs );
            $field[ 'attributes' ] = $this->concat_attributes( $mergedAttrs );

            return $field;
        }
    }
}