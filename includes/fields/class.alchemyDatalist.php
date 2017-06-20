<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Datalist_Field' ) ) {

    class Alchemy_Datalist_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

            $this->template = '
                <div class="alchemy__field field field--datalist jsAlchemyDatalistBlock" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"datalist"}\'>
                    <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                    <select class="jsAlchemyDatalistSelect"{{MULTIPLE}}>{{OPTIONS}}</select>
                    {{CLEAR}}
                    {{DESCRIPTION}}
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field['clear'] = $field['multiple'] ? '' : '<button type="button" class="button button-secondary jsAlchemyDatalistClear"><span class="dashicons dashicons-trash"></span></button>';
            $field['multiple'] = $this->is_multiple( $field['multiple'] );
            $field['options'] = $this->get_options_html( $field );

            return $field;
        }

        public function get_options_html( $field ) {
            $optionsHTML = '<option value="default"></option>';

            if( is_array( $field['value'] ) ) {
                foreach ( $field['options'] as $id => $option ) {
                    $field['options'][$id]['alchemy_is_selected'] = in_array( $option['value'], $field['value'] );
                }
            }

            $optionsHTML .= join('', array_map( function( $option ){
                return sprintf(
                    '<option value="%1$s"%3$s>%2$s</option>',
                    esc_attr( $option['value'] ),
                    $option['name'],
                    $this->is_selected( $option['alchemy_is_selected'] )
                );
            }, $field['options'] ));

            return $optionsHTML;
        }

        private function is_selected( $value ) {
            return $value ? ' selected="selected"' : '';
        }

        public function is_multiple( $value ) {
            return $value ? ' multiple="multiple"' : '';
        }
    }
}