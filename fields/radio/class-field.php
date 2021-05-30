<?php

namespace Alchemy\Fields\Radio;

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
        add_filter( 'alch_get_radio_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_radio_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_radio_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_radio_value', array( $this, 'validate_value' ), 10, 2 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_radio_field',
            AlCHEMY_DIR_URL . 'fields/radio/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/radio/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_radio_field',
            AlCHEMY_DIR_URL . 'fields/radio/styles.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/radio/styles.min.css' )
        );

        wp_enqueue_script( 'alch_radio_field' );
        wp_enqueue_style( 'alch_radio_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'radio',
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
        if( empty( $data['id'] ) || empty( $data['choices'] ) ) {
            return '';
        }

        $html = sprintf( '<div class="alchemy__field field field--%1$s radio clearfix jsAlchemyField" data-alchemy="%2$s" id="%3$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'radio',
                'id' => $data['id']
            ) ) ),
            $data['id']
        );

        $html .= alch_admin_get_field_sidebar( $data, false );

        $html .= '<div class="field__content">';

        foreach ( $data['choices'] as $choice ) {
            if( is_string( $choice ) ) {
                $choice = array(
                    'value' => $choice,
                    'label' => $choice,
                );
            }

            $radioID = esc_attr( sprintf( '%1$s_%2$s',
                $data['id'],
                sanitize_title( $choice['value'] )
            ) );

            if( empty( $savedValue ) ) {
                $choice['checked'] = $choice['checked'] ?? false;
            } else {
                $choice['checked'] = $choice['value'] === $savedValue;
            }

            $choice['disabled'] = $choice['disabled'] ?? false;

            $html .= sprintf( '<label class="radio__label" for="%1$s"><input id="%1$s" name="%2$s" type="radio" data-value="%3$s"%4$s%5$s />%6$s</label><br>',
                $radioID,
                $data['id'] . '[]',
                esc_attr( $choice['value'] ),
                disabled( $choice['disabled'], true, false ),
                checked( $choice['checked'], true, false ),
                $choice['label']
            );
        }

        $html .= '</div>';

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) : string {
        return sanitize_text_field( $value );
    }

    function validate_value( $id, $value ) : array {
        $error = apply_filters( 'alch_do_validate_radio_value', '', $value );

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

    function prepare_value( $value, $id ) : string {
        $validValue = apply_filters( 'alch_prepared_radio_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }
}