<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Slider_Field' ) ) {

    class Alchemy_Slider_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--{{TYPE}}" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"{{TYPE}}"}\'>
                    <div class="field__side">
                        <h3 class="field__label">{{TITLE}}</h3>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content">
                        <div class="jsAlchemySlider" data-values=\'{{VALUES}}\'></div>
                        <input {{ATTRIBUTES}} />
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $passedAttrs = isset( $field['attributes'] ) ? $field['attributes'] : array();
            $mergedAttrs = array_merge( array(
                'type' => 'number',
                'id' => $field['id'],
                'value' => $field['value'],
                'readonly' => true,
                'class' => 'jsAlchemySliderInput'
            ), $passedAttrs );

            $field['attributes'] = $this->concat_attributes( $mergedAttrs );
            $field['values'] = json_encode( $field['values'] );

            return $field;
        }
    }
}