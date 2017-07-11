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
                <div class="alchemy__field field field--{{TYPE}} jsAlchemySectionsFiled" data-alchemy=\'{"type":"{{TYPE}}"}\'>
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
                $navHTML = '<div class="jsAlchemySectionsNav">';
                $tabsHTML = '<div class="jsAlchemySectionsTabs">';

                foreach ( $sections as $section ) {
                    $navHTML .= sprintf(
                        '<button type="button" data-controls="%2$s">%1$s</button>',
                            $section['title'],
                            $this->make_label( $section['title'] )
                    );

                    if( is_array( $section['options'] ) && count( $section['options'] ) > 0 ) {
                        $tabsHTML .= sprintf(
                            '<div class="jsAlchemySectionsTab" data-controlled-by="%s">',
                                $this->make_label( $section['title'] )
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