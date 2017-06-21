<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Password_Field' ) ) {

    class Alchemy_Password_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--password" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"password"}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content">
                        <input {{ATTRIBUTES}} /><button type="button"{{TOGGLE-TITLE}} class="button button-primary jsAlchemyTogglePassword"><span class="dashicons dashicons-lock"></span></button>
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field[ 'toggle-title' ] = isset( $field[ 'toggle-title-text' ] ) ? sprintf( ' title="%s"', $field[ 'toggle-title-text' ] ) : '';
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