<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Section_Field' ) ) {

    class Alchemy_Section_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field field field--{{TYPE}}" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"{{TYPE}}","title":"{{TITLE}}"}\'>
                {{FIELDS}}
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field['fields'] = $this->create_section_fields( $field );

            return $field;
        }

        public function create_section_fields( $field ) {
            $fieldsHTML = '';
            $fields = isset( $field['options'] ) ? $field['options'] : array();

            if( is_array( $fields ) && count( $fields ) > 0 ) {
                $sectionFields = new Alchemy_Fields_Loader( $this->networkField );

                $fieldsHTML .= $sectionFields->get_fields_html( $fields );
            }

            return $fieldsHTML;
        }
    }
}