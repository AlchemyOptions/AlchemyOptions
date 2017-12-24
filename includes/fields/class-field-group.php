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

if( ! class_exists( __NAMESPACE__ . '\Field_Group' ) ) {

    class Field_Group extends Includes\Field {
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

            $this->template = '
                <div class="alchemy__field field field--{{TYPE}}{{HIDDEN}}" id="field--{{ID}}"{{VISIBLE}} data-alchemy=\'{"id":"{{ID}}","type":"{{TYPE}}"}\'>
                    <fieldset>
                        {{TITLE}}
                        {{DESCRIPTION}}
                        {{FIELDS}}
                    </fieldset>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field['title'] = '' !== $field['title'] ? sprintf( '<legend class="field__label">%s</legend>', $field['title'] ) : '' ;
            $field['visible'] = isset( $field['visible'] ) ? sprintf( ' data-condition=\'%1$s\'', esc_attr( $field['visible'] ) ) : '';
            $field['hidden'] = '' !== $field['visible'] ? ' jsAlchemyConditionallyHidden' : '' ;
            $field['fields'] = $this->get_group_fields( $field );

            return $field;
        }

        public function get_group_fields( $field ) {
            $fieldsHTML = '';

            if( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {
                $repeateesFieldsData = array_map( function( $fld ){
                    return array(
                        'id' => $fld[ 'id' ],
                        'type' => $fld[ 'type' ]
                    );
                }, $field['fields'] );

                $field['fields'] = array_map( function( $fld ) use( $field ) {
                    if( isset( $field['value'] ) && isset( $field['value'][$fld['id']]['value'] ) ) {
                        $fld['value'] = $field['value'][$fld['id']]['value'];
                    }

                    $fld['id'] = $field['id'] . '_field-group_' . $fld['id'];

                    return $fld;
                }, $field['fields'] );

                $fieldsHTML .= '<div class="field__wrapper jsAlchemyFiledGroupWrapper" data-fields=\'' . json_encode( $repeateesFieldsData ) . '\'>';

                $optionFields = new Includes\Fields_Loader( $this->networkField );

                //Sections field is top-level only
                $field['fields'] = array_filter($field['fields'], function( $field ) {
                    return $field['type'] !== 'sections';
                });

                $fieldsHTML .= $optionFields->get_fields_html( $field['fields'] );

                $fieldsHTML .= '</div>';
            }

            return $fieldsHTML;
        }
    }
}