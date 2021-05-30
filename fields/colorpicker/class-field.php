<?php

namespace Alchemy\Fields\Colorpicker;

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
        add_filter( 'alch_get_colorpicker_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_colorpicker_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_colorpicker_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_colorpicker_value', array( $this, 'validate_value' ), 10, 2 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_colorpicker_field',
            AlCHEMY_DIR_URL . 'fields/colorpicker/scripts.min.js',
            array( 'alch_admin_scripts', 'iris' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/colorpicker/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_colorpicker_field',
            AlCHEMY_DIR_URL . 'fields/colorpicker/styles.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/colorpicker/styles.min.css' )
        );

        wp_enqueue_script( 'alch_colorpicker_field' );
        wp_enqueue_style( 'alch_colorpicker_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'colorpicker',
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

        $html = sprintf( '<div class="alchemy__field field field--%1$s colorpicker clearfix jsAlchemyField jsAlchemyColorpicker" data-alchemy="%2$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'colorpicker',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data );

        $value = ! empty( $savedValue )
            ? sprintf( ' value="%s"', esc_attr( $savedValue ) )
            : '';

        $html .= '<div class="field__content"><div class="colorpicker__toolbar">';

        $html .= sprintf( '<span class="colorpicker__sample jsAlchemyColorpickerSample"%s></span>',
            empty( $savedValue ) ? '' : sprintf( ' style="background-color: %s"', esc_attr( $savedValue ) )
        );

        $html .= sprintf( '<input type="text" class="jsAlchemyColorpickerInput" id="%1$s"%2$s />',
            $data['id'],
            $value
        );

        $html .= '<button type="button" class="button button-secondary jsAlchemyColorpickerClear"><span class="dashicons dashicons-trash"></span></button>';

        $html .= '</div></div>';

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) : string {
        return sanitize_text_field( $value );
    }

    function validate_value( $id, $value ) : array {
        $error = apply_filters( 'alch_do_validate_colorpicker_value', '', $value );

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
        $validValue = apply_filters( 'alch_prepared_colorpicker_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }
}