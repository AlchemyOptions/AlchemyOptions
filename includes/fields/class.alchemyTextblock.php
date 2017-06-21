<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Textblock_Field' ) ) {

    class Alchemy_Textblock_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--textblock">
                    <div class="field__side">
                        <h3 class="field__label">{{TITLE}}</h3>
                    </div>
                    <div class="field__content">
                        {{DESCRIPTION}}
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            return parent::normalize_field_keys( $field );
        }
    }
}