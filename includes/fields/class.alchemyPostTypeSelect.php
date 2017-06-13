<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Post_Type_Select_Field' ) ) {

    class Alchemy_Post_Type_Select_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field field field--post-type-select jsAlchemyPostTypeSelectBlock" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"post-type-select","post-type":"{{POST-TYPE}}"}\'>
                    <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                    <select class="jsAlchemyPostTypeSelect"{{MULTIPLE}} data-nonce=\'{{NONCE}}\'></select>
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field['nonce'] = json_encode( array(
                'id' => $field[ 'id' ] . '_pts_nonce',
                'value' => wp_create_nonce( $field[ 'id' ] . '_pts_nonce' )
            ) );

            $field['post-type'] = ( isset( $field['post-type'] ) && post_type_exists( $field['post-type'] ) ) ? $field['post-type'] : 'post';
            $field['multiple'] = $this->is_multiple( $field['multiple'] );


            return $field;
        }

        public function is_multiple( $value ) {
            return $value ? ' multiple="multiple"' : '';
        }
    }
}