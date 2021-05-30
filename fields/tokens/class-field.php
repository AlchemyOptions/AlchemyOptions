<?php

namespace Alchemy\Fields\Tokens;

use Alchemy\Fields\Field_Interface;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Field' ) ) {
    return;
}

class Field implements Field_Interface {
    function __construct() {
        add_filter( 'alch_register_field_type', array( $this, 'register_type' ) );
        add_filter( 'alch_get_tokens_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_tokens_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_tokens_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_tokens_value', array( $this, 'validate_value' ), 10, 2 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_select_2',
            AlCHEMY_DIR_URL . 'fields/tokens/vendor/select2/js/select2.full.min.js',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/tokens/vendor/select2/js/select2.full.min.js' ),
            true
        );

        $select2LanguageFile = sprintf( 'fields/tokens/vendor/select2/js/i18n/%1$s.js',
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
            'alch_tokens_field',
            AlCHEMY_DIR_URL . 'fields/tokens/scripts.min.js',
            array( 'alch_admin_scripts', 'alch_select_2' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/tokens/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_tokens_field',
            AlCHEMY_DIR_URL . 'fields/tokens/styles.min.css',
            array( 'alch_select_2' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/tokens/styles.min.css' )
        );

        wp_register_style(
            'alch_select_2',
            AlCHEMY_DIR_URL . 'fields/tokens/vendor/select2/css/select2.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/tokens/vendor/select2/css/select2.min.css' )
        );

        wp_enqueue_script( 'alch_tokens_field' );
        wp_enqueue_style( 'alch_tokens_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'tokens',
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

    function get_option_html( array $data, $savedValue, string $type ) : string {
        if( empty( $data['id'] ) ) {
            return '';
        }

        $html = sprintf( '<div class="alchemy__field field field--%1$s clearfix jsAlchemyField" data-alchemy="%2$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'tokens',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data );

        $savedValue = empty( $savedValue ) ? [] : $savedValue;

        $html .= '<div class="field__content">';

        $html .= sprintf( '<select class="jsAlchemyTokens" id="%1$s" multiple="true" data-alchemy="%2$s">',
            $data['id'],
            esc_attr( json_encode( array(
                'locale' => explode( '_', get_locale() )[0]
            ) ) )
        );

        $html .= $this->get_tokens_options_html( $savedValue );

        $html .= '</select>';

        $html .= '<button type="button" class="button button-secondary jsAlchemyTokensClear"><span class="dashicons dashicons-trash"></span></button>';

        $html .= '</div>';

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) : array {
        return array_map( 'sanitize_text_field', $value );
    }

    function validate_value( $id, $value ) : array {
        $error = apply_filters( 'alch_do_validate_tokens_value', '', $value );

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

    function prepare_value( $value, $id ) : array {
        $validValue = apply_filters( 'alch_prepared_tokens_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }

    private function get_tokens_options_html( array $savedValue = [] ) : string {
        $optionsHTML = '';

        $optionsHTML .= join( '', array_map( function( $option ) {
            return sprintf( '<option value="%1$s" selected="selected">%1$s</option>', esc_attr( $option ) );
        }, $savedValue ) );

        return $optionsHTML;
    }
}