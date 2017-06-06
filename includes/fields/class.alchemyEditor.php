<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Editor_Field' ) ) {

    class Alchemy_Editor_Field extends Alchemy_Field {
        public function __construct() {
            parent::__construct();

            $this->template = '
                <div class="alchemy__field field field--editor" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"editor"}\'>
                    <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                    <textarea {{ATTRIBUTES}}>{{VALUE}}</textarea>
                    <div class="field__cover"></div>
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
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
                'class' => 'jsAlchemyEditorTextarea'
            ));
            $field[ 'add-media' ] = '';

            $field[ 'value' ] = wp_kses_stripslashes( $field[ 'value' ] );

            return $field;
        }
    }
}