<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Field_Group_Field' ) ) {

    class Alchemy_Field_Group_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field field field--{{TYPE}}{{HIDDEN}}" id="field--{{ID}}"{{VISIBLE}} data-alchemy=\'{"id":"{{ID}}","type":"{{TYPE}}"}\'>
                    <fieldset>
                       <legend class="field__label">{{TITLE}}</legend>
                        <div class="field__description">
                            <p>{{DESCRIPTION}}</p>
                        </div>
                        {{FIELDS}}
                    </fieldset>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

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
                    $fld['value'] = $field['value'][$fld['id']]['value'];
                    $fld['id'] = $field['id'] . '_field-group_' . $fld['id'];

                    return $fld;
                }, $field['fields'] );

                $fieldsHTML .= '<div class="field__wrapper jsAlchemyFiledGroupWrapper" data-fields=\'' . json_encode( $repeateesFieldsData ) . '\'>';

                $optionFields = new Alchemy_Fields_Loader( $this->networkField );
                $fieldsHTML .= $optionFields->get_fields_html( $field['fields'] );

                $fieldsHTML .= '</div>';
            }

            return $fieldsHTML;
        }
    }
}