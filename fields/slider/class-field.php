<?php

namespace Alchemy\Fields\Slider;

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
        add_filter( 'alch_get_slider_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_slider_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'alch_validate_slider_value', array( $this, 'validate_value' ), 10, 2 );
        add_action( 'alch_prepare_slider_value', array( $this, 'prepare_value' ), 10, 3 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_slider_field',
            AlCHEMY_DIR_URL . 'fields/slider/scripts.min.js',
            array( 'alch_admin_scripts', 'jquery-ui-slider' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/slider/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_slider_field',
            AlCHEMY_DIR_URL . 'fields/slider/styles.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/slider/styles.min.css' )
        );

        wp_enqueue_script( 'alch_slider_field' );
        wp_enqueue_style( 'alch_slider_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'slider',
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
        $html = sprintf( '<div class="alchemy__field field field--%1$s slider clearfix jsAlchemyField" data-alchemy="%2$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'slider',
                'id' => $data['id']
            ) ) )
        );

        if( empty( $data['values'] ) ) {
            $data['values'] = array();
        } else {
            $data['values']['min'] = $data['values']['min'] ?? 0;
            $data['values']['max'] = $data['values']['max'] ?? 100;
            $data['values']['step'] = $data['values']['step'] ?? 1;
        }

        $html .= alch_admin_get_field_sidebar( $data, false );

        $value = ! empty( $savedValue )
            ? sprintf( ' value="%s"', esc_attr( $savedValue ) )
            : '';

        $html .= sprintf( '<div class="field__content"><div class="slider__container"><div class="jsAlchemySlider" data-values="%3$s"></div></div><input class="jsAlchemySliderInput" readonly id="%1$s" type="number"%2$s /></div>',
            $data['id'],
            $value,
            esc_attr( json_encode( $data['values'] ) )
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

        if( ! is_numeric( $value ) && '' !== $value ) {
            $error = __( 'Value is not numeric', 'alchemy' );
        }

        if( empty( $error ) ) {
            $error = apply_filters( 'alch_do_validate_slider_value', '', $value );
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

    function prepare_value( $value, $id ) {
        $validValue = apply_filters( 'alch_prepared_slider_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }
}
