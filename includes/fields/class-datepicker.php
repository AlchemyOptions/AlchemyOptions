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

if( ! class_exists( __NAMESPACE__ . '\Datepicker' ) ) {

    class Datepicker extends Includes\Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--datepicker" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"datepicker"}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content">
                        <input {{ATTRIBUTES}} /><span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $passedAttrs = isset( $field[ 'attributes' ] ) ? $field[ 'attributes' ] : array();
            $mergedAttrs = array_merge( array(
                'type' => 'text',
                'id' => $field[ 'id' ],
                'value' => $field[ 'value' ],
                'class' => 'jsAlchemyDatepickerInput'
            ), $passedAttrs );

            $field[ 'attributes' ] = $this->concat_attributes( $mergedAttrs );

            return $field;
        }
    }
}