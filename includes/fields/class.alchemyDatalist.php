<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Datalist_Field' ) ) {

    class Alchemy_Datalist_Field extends Alchemy_Field {
        public function __construct() {
            parent::__construct();

            $this->template = '
                <div class="alchemy__field field field--datalist" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"datalist"}\'>
                    {{CHOICES}}
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
                </div>
            ';
        }
    }
}