<?php

namespace Alchemy\Fields\Text;

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
        add_filter( 'alch_get_text_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_text_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'alch_validate_text_value', array( $this, 'validate_value' ), 10, 2 );
        add_action( 'alch_prepare_text_value', array( $this, 'prepare_value' ), 10, 3 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_text_field',
            AlCHEMY_DIR_URL . 'fields/text/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/text/scripts.min.js' ),
            true
        );

        wp_enqueue_script( 'alch_text_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'text',
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
                'type' => 'text',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data );

        $value = ! empty( $savedValue )
            ? sprintf( ' value="%s"', esc_attr( $savedValue ) )
            : '';

        $html .= sprintf( '<div class="field__content"><input id="%1$s" type="text"%2$s /></div>',
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
        $error = apply_filters( 'alch_do_validate_text_value', '', $value );

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
        $validValue = apply_filters( 'alch_prepared_text_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }
}