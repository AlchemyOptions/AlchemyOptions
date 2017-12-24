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

if( ! class_exists(  __NAMESPACE__ . '\Taxonomy_Select' ) ) {

    class Taxonomy_Select extends Includes\Field {
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--taxonomy-select jsAlchemyTaxonomySelectBlock" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"taxonomy-select","taxonomy":"{{TAXONOMY}}"}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content"{{PADDED}}>
                        <select style="width: 100%;" class="jsAlchemyTaxonomySelect"{{MULTIPLE}} {{ATTRIBUTES}}>{{OPTIONS}}</select>
                        {{CLEAR}}
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field['multiple'] = isset( $field['multiple'] ) ? $field['multiple'] : false;
            $field['taxonomy'] = ( isset( $field['taxonomy'] ) && taxonomy_exists( $field['taxonomy'] ) ) ? $field['taxonomy'] : 'category';
            $field['clear'] = $field['multiple'] ? '' : '<button type="button" class="button button-secondary jsAlchemyTaxonomySelectClear"><span class="dashicons dashicons-trash"></span></button>';
            $field['multiple'] = $this->is_multiple( $field['multiple'] );
            $field['options'] = $this->get_options_html( $field );
            $field['padded'] = '' !== $field['multiple'] ? '' : 'style="padding-right: 50px;"';
            $field[ 'attributes' ] = $this->concat_attributes( array(
                'id' => $field['id'],
                'name' => $field['id'] . '[]',
            ) );

            return $field;
        }

        public function get_options_html( $field ) {
            $optionsHTML = '<option value="default"></option>';

            $gotTaxonomies = get_terms( array( 'taxonomy' => $field['taxonomy'] ) );

            if( isset( $field['value']['ids'] ) && is_array( $field['value']['ids'] ) ) {
                $field['value']['ids'] = array_map(function( $val ){
                    return (int) $val;
                }, $field['value']['ids']);

                foreach ( $gotTaxonomies as $tax ) {
                    $tax->alchemy_is_selected = in_array( $tax->term_taxonomy_id, $field['value']['ids'] );
                }
            }

            $optionsHTML .= join('', array_map( function( $taxonomy ){
                return sprintf(
                    '<option value="%1$s"%3$s>%2$s</option>',
                    $taxonomy->term_taxonomy_id,
                    $taxonomy->name,
                    $this->is_selected( $taxonomy->alchemy_is_selected )
                );
            }, $gotTaxonomies ));

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