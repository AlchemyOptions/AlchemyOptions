<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Sections_Field' ) ) {

    class Alchemy_Sections_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

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

            if( is_array( $sections ) && count( $sections ) > 0 ) {
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

                    if( is_array( $section['options'] ) && count( $section['options'] ) > 0 ) {
                        $tabsHTML .= sprintf(
                            '<div class="%2$s" data-controlled-by="%1$s">',
                                $this->make_label( $section['title'] ),
                                $tabClass
                        );

                        $sectionFields = new Alchemy_Fields_Loader( $this->networkField );

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