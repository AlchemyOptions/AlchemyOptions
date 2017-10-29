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

if( ! class_exists( __NAMESPACE__ . '\Text' ) ) {

    class Text extends Includes\Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--{{TYPE}}{{HIDDEN}}" id="field--{{ID}}"{{VISIBLE}} data-alchemy=\'{"id":"{{ID}}","type":"{{TYPE}}"}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content">
                        <input {{ATTRIBUTES}} />
                    </div> 
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );
            $passedAttrs = isset( $field[ 'attributes' ] ) ? $field[ 'attributes' ] : array();
            $mergedAttrs = array_merge( array(
                'type' => $field[ 'type' ],
                'id' => $field[ 'id' ],
                'value' => $field[ 'value' ]
            ), $passedAttrs );

            $field[ 'attributes' ] = $this->concat_attributes( $mergedAttrs );

            $field['visible'] = isset( $field['visible'] ) ? sprintf( ' data-condition=\'%1$s\'', esc_attr( $field['visible'] ) ) : '';
            $field['hidden'] = '' !== $field['visible'] ? ' jsAlchemyConditionallyHidden' : '' ;

            return $field;
        }
    }
}