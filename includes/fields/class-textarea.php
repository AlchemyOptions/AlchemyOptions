<?php

/**
 * @package Alchemy_Options\Includes\Fields
 *
 */

namespace Alchemy_Options\Includes\Fields;

use Alchemy_Options\Includes;

//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( __NAMESPACE__ . '\Textarea' ) ) {

    class Textarea extends Includes\Field {
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

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
                'name' => isset( $field['name'] ) ? $field['name'] : $field['id'],
                'cols' => 60,
                'rows' => 5,
            ));

            return $field;
        }
    }
}