<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Textarea_Field' ) ) {

    class Alchemy_Textarea_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--textarea" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"textarea"}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content">
                        <textarea {{ATTRIBUTES}}>{{VALUE}}</textarea>
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field[ 'allow-html' ] = isset( $field[ 'allow-html' ] ) ? $field[ 'allow-html' ] : false;
            $field[ 'attributes' ] = $this->concat_attributes(array(
                'id' => $field[ 'id' ],
                'name' => $field[ 'id' ],
                'cols' => 60,
                'rows' => 5,
            ));

            return $field;
        }
    }
}