<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Text_Field' ) ) {

    class Alchemy_Textarea_Field extends Alchemy_Field {
        public function __construct() {
            parent::__construct();

            $this->template = '
                <div class="alchemy__field field field--{{TYPE}}" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"{{TYPE}}"}\'>
                    <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                    <textarea {{ATTRIBUTES}}>{{VALUE}}</textarea>
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
                </div>
            ';
        }
    }
}