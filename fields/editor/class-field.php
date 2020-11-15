<?php

namespace Alchemy\Fields\Editor;

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
        add_filter( 'alch_get_editor_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_editor_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_editor_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_editor_value', array( $this, 'validate_value' ), 10, 2 );
    }

    function enqueue_assets() {
        wp_register_script(
            'alch_editor_field',
            AlCHEMY_DIR_URL . 'fields/editor/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/editor/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_editor_field',
            AlCHEMY_DIR_URL . 'fields/editor/styles.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/editor/styles.min.css' )
        );

        wp_enqueue_script( 'alch_editor_field' );
        wp_enqueue_style( 'alch_editor_field' );
    }

    function register_type( $types ) {
        $myTypes = array(
            array(
                'id' => 'editor',
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

    function get_option_html( $data, $savedValue, $type ) {
        if( empty( $data['id'] ) ) {
            return '';
        }

        $html = sprintf( '<div class="alchemy__field field field--%1$s jsAlchemyField" data-alchemy="%2$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'editor',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data );

        $value = ! empty( $savedValue )
            ? esc_textarea( $savedValue )
            : '';

        $html .= sprintf( '<div class="field__content"><textarea class="jsAlchemyEditor" id="%1$s" rows="5" cols="60">%2$s</textarea><div class="field__cover"></div></div>',
            $data['id'],
            $value
        );

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) {
        global $allowedposttags;

        $allowed_html = apply_filters( 'alch_allowed_editor_html_tags', $allowedposttags );
        $allowed_protocols = apply_filters( 'alch_allowed_editor_protocols', wp_allowed_protocols() );

        return wp_kses( $value, $allowed_html, $allowed_protocols );
    }

    function validate_value( $id, $value ) {
        $error = apply_filters( 'alch_do_validate_editor_value', '', $value );

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
        $validValue = apply_filters( 'alch_prepared_editor_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }
}