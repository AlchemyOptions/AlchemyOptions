<?php

namespace Alchemy\Fields\Tel;

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
        add_filter( 'alch_get_tel_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_tel_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_tel_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_tel_value', array( $this, 'validate_value' ), 10, 2 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_tel_field',
            AlCHEMY_DIR_URL . 'fields/tel/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/tel/scripts.min.js' ),
            true
        );

        wp_enqueue_script( 'alch_tel_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'tel',
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
        $html = sprintf( '<div class="alchemy__field field field--%1$s clearfix jsAlchemyField" data-alchemy="%2$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'tel',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data );

        $value = ! empty( $savedValue )
            ? sprintf( ' value="%s"', esc_attr( $savedValue ) )
            : '';

        $html .= sprintf( '<div class="field__content"><input id="%1$s" type="tel"%2$s /></div>',
            $data['id'],
            $value
        );

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) : string {
        return sanitize_text_field( $value );
    }

    function validate_value( $id, $value ) : array {
        $error = '';

        if( ! preg_match( '%^[+]?[0-9()/ -]*$%', $value ) ) {
            $error = __( 'Phone number is not valid', 'alchemy' );
        }

        if( empty( $error ) ) {
            $error = apply_filters( 'alch_do_validate_tel_value', '', $value );
        }

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
        $validValue = apply_filters( 'alch_prepared_tel_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }
}

