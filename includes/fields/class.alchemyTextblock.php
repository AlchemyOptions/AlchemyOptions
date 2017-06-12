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
                <div class="alchemy__field field field--textblock">
                    <h3 class="field__label">{{TITLE}}</h3>
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            return parent::normalize_field_keys( $field );
        }
    }
}