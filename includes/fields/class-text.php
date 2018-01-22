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
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--{{TYPE}}{{HIDDEN}}" id="field--{{ID}}"{{VISIBLE}} data-alchemy=\'{"id":"{{ID}}","type":"{{TYPE}}"{{VARIATIONS-DATA}}}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                        {{VARIATIONS-SELECT}}
                    </div>
                    <div class="field__content">
                        {{CONTENT}}
                    </div> 
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );
            $passedAttrs = isset( $field['attributes'] ) ? $field['attributes'] : array();
            $mergedAttrs = array_merge( array(
                'type' => $field['type'],
                'id' => $field['id'],
                'name' => isset( $field['name'] ) ? $field['name'] : $field['id'],
            ), $passedAttrs );

            $field['content'] = $this->get_field_content( $field, $mergedAttrs );

            $field['visible'] = isset( $field['visible'] ) ? sprintf( ' data-condition=\'%1$s\'', esc_attr( $field['visible'] ) ) : '';
            $field['hidden'] = '' !== $field['visible'] ? ' jsAlchemyConditionallyHidden' : '' ;

            return $field;
        }

        public function get_field_content( $field, $attrs ) {
            $fieldHTML = '';

            if( alch_is_not_empty_array( $field['variations'] ) ) {
                $fieldHTML .= '<div class="variations-content jsAlchemyVariationsContent">';

                foreach ( $field['variations'] as $i => $contentVariation ) {
                    $inputAttrs = $attrs;
                    $classNames = array(
                        'variations__item',
                        'jsAlchemyVariation'
                    );

                    if( $i === 0 ) {
                        array_push( $classNames, 'variations__item--visible' );
                    }

                    $inputAttrs['id'] = $inputAttrs['id'] . '_' . $contentVariation['id'];
                    $inputAttrs['name'] = $inputAttrs['name'] . '[' . $contentVariation['id'] . ']';

                    if( "" !== $field['value'] ) {
                        $inputAttrs['value'] = $field['value']['alchemy-variations'][$contentVariation['id']];
                    }

                    $fieldHTML .= sprintf(
                        '<div class="%3$s" data-variation-id="%1$s">%2$s</div>',
                            $contentVariation['id'],
                            $this->get_input_html( $inputAttrs ),
                            join( " ", $classNames )
                    );
                }

                $fieldHTML .= '</div>';
            } else {
                $attrs['value'] = $field['value'];

                $fieldHTML .= $this->get_input_html( $attrs );
            }

            return $fieldHTML;
        }

        public function get_input_html( $attrs ) {
            return sprintf(
                '<input %s />',
                $this->concat_attributes( $attrs )
            );
        }
    }
}