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

if( ! class_exists( __NAMESPACE__ . '\Password' ) ) {

    class Password extends Includes\Field {
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

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
                'name' => isset( $field['name'] ) ? $field['name'] : $field['id'],
                'value' => $field[ 'value' ]
            ));

            return $field;
        }
    }
}