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

if( ! class_exists(  __NAMESPACE__ . '\Sections' ) ) {

    class Sections extends Includes\Field {
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

            $this->template = '
                <div class="alchemy__field field field--{{TYPE}} clearfix jsAlchemySectionsFiled" data-alchemy=\'{"type":"{{TYPE}}"}\'>
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
            $sections = isset( $field['sections'] ) ? $field['sections'] : array();

            if( isset( $sections ) && alch_is_not_empty_array( $sections ) ) {
                $navHTML = '<div class="field__tabs-nav jsAlchemySectionsNav">';
                $tabsHTML = '<div class="field__tabs jsAlchemySectionsTabs">';

                foreach ( $sections as $i => $section ) {
                    $btnClass = 'field__tab-btn jsAlchemySectionsButton';
                    $tabClass = 'field__tab jsAlchemySectionsTab';

                    if( $i === 0 ) {
                        $btnClass .= ' field__tab-btn--active';
                    } else {
                        $tabClass .= ' field__tab--hidden';
                    }

                    $navHTML .= sprintf(
                        '<button type="button" class="%3$s" data-controls="%2$s">%1$s</button>',
                            $section['title'],
                            $this->make_label( $section['title'] ),
                            $btnClass
                    );

                    if( isset( $section['options'] ) && alch_is_not_empty_array( $section['options'] ) ) {
                        $tabsHTML .= sprintf(
                            '<div class="%2$s" data-controlled-by="%1$s">',
                                $this->make_label( $section['title'] ),
                                $tabClass
                        );

                        $sectionFields = new Includes\Fields_Loader( $this->networkField );

                        $tabsHTML .= $sectionFields->get_fields_html( $section['options'] );
                        $tabsHTML .= '</div>';
                    }
                }

                $navHTML .= '</div>';
                $tabsHTML .= '</div>';

                $fieldsHTML = $navHTML . $tabsHTML;
            }

            return $fieldsHTML;
        }
    }
}