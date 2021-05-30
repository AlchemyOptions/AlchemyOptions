<?php

namespace Alchemy\Fields\Post_Type_Select;

use Alchemy\Fields\Field_Interface;
use WP_Error;
use WP_Query;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Field' ) ) {
    return;
}

class Field implements Field_Interface {
    function __construct() {
        add_filter( 'alch_register_field_type', array( $this, 'register_type' ) );
        add_filter( 'alch_get_post_type_select_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_post_type_select_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_post_type_select_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_post_type_select_value', array( $this, 'validate_value' ), 10, 2 );
        add_action( 'rest_api_init', array( $this, 'add_rest_endpoints' ) );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_select_2',
            AlCHEMY_DIR_URL . 'fields/post-type-select/vendor/select2/js/select2.full.min.js',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/post-type-select/vendor/select2/js/select2.full.min.js' ),
            true
        );

        $select2LanguageFile = sprintf( 'fields/post-type-select/vendor/select2/js/i18n/%1$s.js',
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

        wp_register_script(
            'alch_post_type_select_field',
            AlCHEMY_DIR_URL . 'fields/post-type-select/scripts.min.js',
            array( 'alch_admin_scripts', 'alch_select_2' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/post-type-select/scripts.min.js' ),
            true
        );

        wp_localize_script( 'alch_post_type_select_field', 'AlchemyPTSData', array(
            'search' => array(
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => get_rest_url( null, '/alchemy/v1/pts-search/' )
            ),
        ) );

        wp_register_style(
            'alch_post_type_select_field',
            AlCHEMY_DIR_URL . 'fields/post-type-select/styles.min.css',
            array( 'alch_select_2' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/post-type-select/styles.min.css' )
        );

        wp_register_style(
            'alch_select_2',
            AlCHEMY_DIR_URL . 'fields/post-type-select/vendor/select2/css/select2.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/post-type-select/vendor/select2/css/select2.min.css' )
        );

        wp_enqueue_script( 'alch_post_type_select_field' );
        wp_enqueue_style( 'alch_post_type_select_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'post_type_select',
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
        register_rest_route( 'alchemy/v1', '/pts-search/', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array( $this, 'handle_pts_search' ),
            'permission_callback' => function() {
                $pageID = isset( $_GET['page-id'] ) ? $_GET['page-id'] : null;

                if( ! empty( $pageID ) ) {
                    $pageCap = \Alchemy\Includes\Options_Page::get_page_capabilities( $pageID );

                    return current_user_can( $pageCap );
                }

                return false;
            },
        ) );
    }

    function handle_pts_search( \WP_REST_Request $request ) {
        $params = $request->get_params();

        if( empty( $params['_wpnonce'] ) || ! wp_verify_nonce( $params['_wpnonce'], 'wp_rest' ) ) {
            return rest_ensure_response( new WP_Error(
                'alch-pts-search-nonce-failure',
                __( 'Nonce check failed', 'alchemy' ),
                array( 'status' => 401 )
            ) );
        }

        $the_query = new WP_Query( array(
            's' => $params['searchedFor'],
            'post_type' => $params['post-type'],
            'post_status' => 'publish'
        ) );

        $found_posts = [];

        if ( $the_query->have_posts() ) {
            $found_posts = $the_query->get_posts();
        }

        $result = array_map( function( $post ) {
            $ancestors = get_post_ancestors( $post );
            $title = $post->post_title;

            if( ! empty( $ancestors ) ) {
                $title = '';

                foreach ( array_reverse( $ancestors ) as $ancestor ) {
                    $title .= get_the_title( $ancestor ) . ' / ';
                }

                $title .= $post->post_title;
            }

            return array(
                'text' => $title,
                'id' => $post->ID
            );
        }, $found_posts );

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
                'type' => 'post_type_select',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data );

        $multiple = isset( $data['multiple'] ) && true === $data['multiple'];
        $postTypes = $data['post-type'] ?? ['post'];

        $html .= '<div class="field__content">';

        $html .= sprintf( '<select class="jsAlchemyPostTypeSelect" id="%1$s"%2$s data-alchemy="%3$s">',
            $data['id'],
            $multiple ? ' multiple="true"' : '',
            esc_attr( json_encode( array(
                'post-type' => $postTypes,
                'locale' => explode( '_', get_locale() )[0]
            ) ) )
        );

        if( ! empty( $savedValue ) ) {
            $html .= $this->get_pts_options_html( $savedValue, $postTypes );
        }

        $html .= '</select>';

        $html .= '<button type="button" class="button button-secondary jsAlchemyPostTypeSelectClear"><span class="dashicons dashicons-trash"></span></button>';

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
        $error = apply_filters( 'alch_do_validate_post_type_select_value', '', $value );

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
        $validValue = apply_filters( 'alch_prepared_post_type_select_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }

    private function get_pts_options_html( $ids, $postTypes ) : string {
        $optionsHTML = '';

        if( is_string( $ids ) ) {
            $ids = [$ids];
        }

        $the_query = new WP_Query( array(
            'post_type' => $postTypes,
            'post_status' => 'publish',
            'post__in' => $ids
        ) );

        $found_posts = [];

        if ( $the_query->have_posts() ) {
            $found_posts = $the_query->get_posts();

            usort( $found_posts, function( $a, $b ) use( $ids ) {
                if ( $a->ID == $b->ID ) { return 0; }

                $position = array_search( $a->ID, $ids );
                $position2 = array_search( $b->ID, $ids );

                if ( $position2 !== false && $position !== false ) {
                    return ( $position < $position2 ) ? -1 : 1;
                }

                if( $position !== false ) { return -1; }
                if( $position2 !== false ) { return 1; }

                return ( $a->ID < $b->ID ) ? -1 : 1;
            } );
        }

        $optionsHTML .= join('',  array_map( function( $post ) {
            $ancestors = get_post_ancestors( $post );
            $title = $post->post_title;

            if( ! empty( $ancestors ) ) {
                $title = '';

                foreach ( array_reverse( $ancestors ) as $ancestor ) {
                    $title .= get_the_title( $ancestor ) . ' / ';
                }

                $title .= $post->post_title;
            }

            return sprintf( '<option selected="selected" value="%1$s">%2$s</option>', $post->ID, $title );
        }, $found_posts ) );

        return $optionsHTML;
    }
}
