<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Datepicker_Field' ) ) {

    class Alchemy_Datepicker_Field extends Alchemy_Field {
        public function __construct() {
            parent::__construct();

            $this->template = '
                <div class="alchemy__field field field--datepicker" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"datepicker"}\'>
                    <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                    <input {{ATTRIBUTES}} /><span class="dashicons dashicons-calendar-alt"></span>
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
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
                'class' => 'jsAlchemyDatepickerInput'
            ), $passedAttrs );

            $field[ 'attributes' ] = $this->concat_attributes( $mergedAttrs );

            return $field;
        }
    }
}