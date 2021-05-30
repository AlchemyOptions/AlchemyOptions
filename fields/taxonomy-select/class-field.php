<?php

namespace Alchemy\Fields\Taxonomy_Select;

use Alchemy\Fields\Field_Interface;
use Alchemy\Includes\Options_Page;
use WP_Error;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Field' ) ) {
    return;
}

class Field implements Field_Interface {
    function __construct() {
        add_filter( 'alch_register_field_type', array( $this, 'register_type' ) );
        add_filter( 'alch_get_taxonomy_select_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_taxonomy_select_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_taxonomy_select_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_taxonomy_select_value', array( $this, 'validate_value' ), 10, 2 );
        add_action( 'rest_api_init', array( $this, 'add_rest_endpoints' ) );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_select_2',
            AlCHEMY_DIR_URL . 'fields/taxonomy-select/vendor/select2/js/select2.full.min.js',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/taxonomy-select/vendor/select2/js/select2.full.min.js' ),
            true
        );

        wp_register_script(
            'alch_taxonomy_select_field',
            AlCHEMY_DIR_URL . 'fields/taxonomy-select/scripts.min.js',
            array( 'alch_admin_scripts', 'alch_select_2' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/taxonomy-select/scripts.min.js' ),
            true
        );

        $select2LanguageFile = sprintf( 'fields/taxonomy-select/vendor/select2/js/i18n/%1$s.js',
            explode( '_', get_locale() )[0]
        );

        if( file_exists( AlCHEMY_DIR_PATH . $select2LanguageFile ) ) {
            wp_register_script(
                'alch_select_2_i18n',
                AlCHEMY_DIR_URL . $select2LanguageFile,
                array( 'alch_select_2' ),
                filemtime( AlCHEMY_DIR_PATH . $select2LanguageFile ),
                true
            );

            wp_enqueue_script( 'alch_select_2_i18n' );
        }

        wp_localize_script( 'alch_taxonomy_select_field', 'AlchemyTaxonomySelectData', array(
            'search' => array(
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => get_rest_url( null, '/alchemy/v1/taxonomy-search/' )
            ),
        ) );

        wp_register_style(
            'alch_select_2',
            AlCHEMY_DIR_URL . 'fields/taxonomy-select/vendor/select2/css/select2.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/taxonomy-select/vendor/select2/css/select2.min.css' )
        );

        wp_register_style(
            'alch_taxonomy_select_field',
            AlCHEMY_DIR_URL . 'fields/taxonomy-select/styles.min.css',
            array( 'alch_select_2' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/taxonomy-select/styles.min.css' )
        );

        wp_enqueue_script( 'alch_taxonomy_select_field' );
        wp_enqueue_style( 'alch_taxonomy_select_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'taxonomy_select',
                'available-for' => array(
                    'options' => true,
                    'repeaters' => true,
                    'metaboxes' => true,
                    'userprofile' => true,
                ),
            ),
        );

        return array_merge( $types, $myTypes );
    }

    function add_rest_endpoints() : void {
        register_rest_route( 'alchemy/v1', '/taxonomy-search/', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array( $this, 'handle_taxonomy_search' ),
            'permission_callback' => function() {
                $pageID = isset( $_GET['page-id'] ) ? $_GET['page-id'] : null;

                if( ! empty( $pageID ) ) {
                    $pageCap = Options_Page::get_page_capabilities( $pageID );

                    return current_user_can( $pageCap );
                }

                return false;
            },
        ) );
    }

    function handle_taxonomy_search( \WP_REST_Request $request ) {
        $params = $request->get_params();

        if( empty( $params['_wpnonce'] ) || ! wp_verify_nonce( $params['_wpnonce'], 'wp_rest' ) ) {
            return rest_ensure_response( new WP_Error(
                'alch-taxonomy-search-nonce-failure',
                __( 'Nonce check failed', 'alchemy' ),
                array( 'status' => 401 )
            ) );
        }

        $gotTaxonomies = get_terms( array(
            'search' => $params['searchedFor'],
            'taxonomy' => $params['taxonomy']
        ) );

        $result = array_map( function( $taxonomy ) {
            return array(
                'text' => $taxonomy->name,
                'id' => $taxonomy->term_taxonomy_id
            );
        }, $gotTaxonomies );

        return rest_ensure_response( array(
            'success' => true,
            'data' => $result
        ) );
    }

    function get_option_html( array $data, $savedValue, string $type ) : string {
        if( empty( $data['id'] ) ) {
            return '';
        }

        $html = sprintf( '<div class="alchemy__field field field--%1$s clearfix jsAlchemyField" data-alchemy="%2$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'taxonomy_select',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data );

        $multiple = isset( $data['multiple'] ) && true === $data['multiple'];
        $taxonomies = $data['taxonomy'] ?? ['category'];

        $html .= '<div class="field__content">';

        $html .= sprintf( '<select class="jsAlchemyTaxonomySelect" id="%1$s"%2$s data-alchemy="%3$s">',
            $data['id'],
            $multiple ? ' multiple="true"' : '',
            esc_attr( json_encode( array(
                'taxonomy' => $taxonomies,
                'locale' => explode( '_', get_locale() )[0]
            ) ) )
        );

        if( ! empty( $savedValue ) ) {
            $html .= $this->get_taxonomy_options_html( $savedValue, $taxonomies );
        }

        $html .= '</select>';

        $html .= '<button type="button" class="button button-secondary jsAlchemyTaxonomySelectClear"><span class="dashicons dashicons-trash"></span></button>';

        $html .= '</div>';

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) {
        if( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }

        return sanitize_text_field( $value );
    }

    function validate_value( $id, $value ) : array {
        $error = apply_filters( 'alch_do_validate_taxonomy_select_value', '', $value );

        if( empty( $error ) ) {
            $error = apply_filters( "alch_do_validate_{$id}_value", '', $value );
        }

        if( ! empty( $error ) ) {
            return array(
                'is_valid' => false,
                'message' => $error
            );
        }

        return array( 'is_valid' => true );
    }

    function prepare_value( $value, $id ) {
        $validValue = apply_filters( 'alch_prepared_taxonomy_select_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }

    private function get_taxonomy_options_html( $ids, $taxonomies ) : string {
        $optionsHTML = '';

        if( is_string( $ids ) ) {
            $ids = [$ids];
        }

        $found_taxonomies = get_terms( array(
            'taxonomy' => $taxonomies,
            'include' => $ids
        ) );

        if ( empty( $found_taxonomies ) ) {
            return '';
        }

        usort( $found_taxonomies, function( $a, $b ) use( $ids ) {
            if ( $a->term_taxonomy_id == $b->term_taxonomy_id ) { return 0; }

            $position = array_search( $a->term_taxonomy_id, $ids );
            $position2 = array_search( $b->term_taxonomy_id, $ids );

            if ( $position2 !== false && $position !== false ) {
                return ( $position < $position2 ) ? -1 : 1;
            }

            if( $position !== false ) { return -1; }
            if( $position2 !== false ) { return 1; }

            return ( $a->term_taxonomy_id < $b->term_taxonomy_id ) ? -1 : 1;
        } );

        $optionsHTML .= join('',  array_map( function( $taxonomy ) {
            return sprintf( '<option selected="selected" value="%1$s">%2$s</option>',
                $taxonomy->term_taxonomy_id,
                $taxonomy->name
            );
        }, $found_taxonomies ) );

        return $optionsHTML;
    }
}
