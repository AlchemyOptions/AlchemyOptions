<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Password_Field' ) ) {

    class Alchemy_Password_Field extends Alchemy_Field {
        public function __construct() {
            parent::__construct();

            $this->template = '
                <div class="alchemy__field field field--password" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"password"}\'>
                    <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                    <input {{ATTRIBUTES}} /><button type="button" title="{{TOGGLE-TITLE}}" class="button button-primary jsAlchemyTogglePassword"><span class="dashicons dashicons-lock"></span></button>
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field[ 'toggle-title' ] = $field[ 'toggle-title-text' ];
            unset( $field[ 'toggle-title-text' ] );

            $field[ 'attributes' ] = $this->concat_attributes(array(
                'type' => $field[ 'type' ],
                'id' => $field[ 'id' ],
                'value' => $field[ 'value' ]
            ));

            return $field;
        }
    }
}