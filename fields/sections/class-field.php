<?php

namespace Alchemy\Fields\Sections;

use Alchemy\Options;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Field' ) ) {
    return;
}

class Field {
    function __construct() {
        add_filter( 'alch_register_field_type', array( $this, 'register_type' ) );
        add_filter( 'alch_ok_without_id_types', array( $this, 'add_as_ok_if_no_id' ) );
        add_filter( 'alch_get_sections_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_sections_field',
            AlCHEMY_DIR_URL . 'fields/sections/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/sections/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_sections_field',
            AlCHEMY_DIR_URL . 'fields/sections/styles.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/sections/styles.min.css' )
        );

        wp_enqueue_script( 'alch_sections_field' );
        wp_enqueue_style( 'alch_sections_field' );
    }

    function add_as_ok_if_no_id( array $types ) : array {
        return array_merge( $types, ['sections'] );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'sections',
                'available-for' => array(
                    'options' => true,
                    'repeaters' => false,
                    'metaboxes' => true,
                    'userprofile' => true,
                ),
            ),
        );

        return array_merge( $types, $myTypes );
    }

    function get_option_html( array $data, $savedValue, string $type ) : string {
        if( empty( $data['sections'] ) ) {
            return '';
        }

        $html = sprintf( '<div class="alchemy__field field field--%1$s jsAlchemyField jsAlchemySectionsField" data-alchemy="%2$s">',
            $data['type'],
            esc_attr( json_encode( array( 'type' => 'sections' ) ) )
        );

        $html .= sprintf( '<div class="field__content clearfix">%s</div>',
            $this->get_sections_fields_html( $data['sections'], $type )
        );

        $html .= '</div>';

        return $html;
    }

    private function get_sections_fields_html( array $sections, string $type ) : string {
        $navHTML = '<div class="field__tabs-nav jsAlchemySectionsNav">';
        $tabsHTML = '<div class="field__tabs jsAlchemySectionsTabs">';

        foreach ( $sections as $i => $section ) {
            $btnClasses = ['field__tab-btn', 'jsAlchemySectionsButton'];
            $tabClasses = ['field__tab', 'jsAlchemySectionsTab'];
            $section['options'] = empty( $section['options'] ) ? [] : $section['options'];

            if( $i === 0 ) {
                $btnClasses[] = 'field__tab-btn--active';
            } else {
                $tabClasses[] = 'field__tab--hidden';
            }

            $navHTML .= sprintf( '<button type="button" class="%1$s" data-controls="%2$s">%3$s</button>',
                join( ' ', $btnClasses ),
                sanitize_title( $section['title'] ),
                $section['title']
            );

            $filteredOptions = array_filter( $section['options'], function( $option ) {
                return 'sections' !== $option['type'];
            } );

            $fieldsHTML = '';

            if( isset( $type ) ) {
                switch( $type ) {
                    case 'metabox' :
                        $fieldsHTML = Options::get_meta_html( get_the_ID(), $filteredOptions );
                    break;
                    case 'options' :
                        $fieldsHTML = Options::get_options_html( $filteredOptions );
                    break;
                    case 'network-options' :
                        $fieldsHTML = Options::get_network_options_html( $filteredOptions );
                    break;
                }
            }

            $tabsHTML .= sprintf( '<div class="%1$s" data-controlled-by="%2$s">%3$s</div>',
                join( ' ', $tabClasses ),
                sanitize_title( $section['title'] ),
                $fieldsHTML
            );
        }

        $navHTML .= '</div>';
        $tabsHTML .= '</div>';

        return $navHTML . $tabsHTML;
    }
}