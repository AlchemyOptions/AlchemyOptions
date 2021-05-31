<?php

namespace Alchemy\Fields\Spacer;

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
        add_filter( 'alch_get_spacer_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_spacer_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'alch_validate_spacer_value', array( $this, 'validate_value' ), 10, 2 );
        add_action( 'alch_prepare_spacer_value', array( $this, 'prepare_value' ), 10, 3 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_spacer_field',
            AlCHEMY_DIR_URL . 'fields/spacer/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/spacer/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_spacer_field',
            AlCHEMY_DIR_URL . 'fields/spacer/styles.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/spacer/styles.min.css' )
        );

        wp_enqueue_script( 'alch_spacer_field' );
        wp_enqueue_style( 'alch_spacer_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'spacer',
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
        $data['default'] = $data['default'] ?? array(
            'top' => 0,
            'right' => 0,
            'bottom' => 0,
            'left' => 0
        );

        $html = sprintf( '<div class="alchemy__field field field--%1$s spacer clearfix jsAlchemyField" data-alchemy="%2$s" id="%3$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'spacer',
                'id' => $data['id']
            ) ) ),
            esc_attr( $data['id'] )
        );

        $icons = array(
            'top' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><rect x=".47" y=".47" width="29.06" height="29.06" rx="2.34" fill="#fff"/><path d="M27.19 0H2.81A2.81 2.81 0 000 2.81v24.38A2.81 2.81 0 002.81 30h24.38A2.81 2.81 0 0030 27.19V2.81A2.81 2.81 0 0027.19 0zm.15 9.81l1.72-1.73v1.73zm1.24-8.24a1.87 1.87 0 01.48 1.24v.36l-6.6 6.64H20.4zm.48 3v2.09l-3.13 3.15h-2.06zM27.72 1L19 9.81h-2.07L25.75.94h1.44a1.89 1.89 0 01.53.06zM8.4.94h2.06L1.64 9.81h-.7V8.45zM.94 7V5l4-4H7zM11.87.94h2.06L5.11 9.81H3.05zm3.47 0h2.06L8.58 9.81H6.52zm3.47 0h2.06l-8.82 8.87H10zm3.47 0h2.06l-8.82 8.87h-2.06zM3.52.94L.94 3.54v-.73A1.87 1.87 0 012.81.94zm23.67 28.12H2.81a1.87 1.87 0 01-1.87-1.87V10.81h28.12v16.38a1.87 1.87 0 01-1.87 1.87z" fill="#8c8f94"/></svg>',
            'right' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><rect x=".47" y=".47" width="29.06" height="29.06" rx="2.34" fill="#fff"/><path d="M27.19 0H2.81A2.81 2.81 0 000 2.81v24.38A2.81 2.81 0 002.81 30h24.38A2.81 2.81 0 0030 27.19V2.81A2.81 2.81 0 0027.19 0zm1.33 1.5l-8.33 8.33v-2L27 .94h.15a1.84 1.84 0 011.37.56zm.54 1.31v1.6l-8.87 8.87v-2L29 2.42a1.67 1.67 0 01.06.39zm-7.39 26.25h-1.48v-.55l8.87-8.88v2zm7.39-6v2L25.12 29h-2zM20.19 4.34l3.4-3.4h2l-5.4 5.44zm0-1.41v-2h2zm0 24.16v-2l8.87-8.87v2zm0-3.45v-2l8.87-8.87v2zm0-3.45v-2l8.87-8.87v2zm0-3.45v-2l8.87-8.88v2zM.94 27.19V2.81A1.87 1.87 0 012.81.94h16.38v28.12H2.81a1.87 1.87 0 01-1.87-1.87zm25.6 1.87l2.52-2.52v.65a1.87 1.87 0 01-1.87 1.87z" fill="#8c8f94"/></svg>',
            'bottom' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><rect x=".47" y=".47" width="29.06" height="29.06" rx="2.34" fill="#fff"/><path d="M27.19 0H2.81A2.81 2.81 0 000 2.81v24.38A2.81 2.81 0 002.81 30h24.38A2.81 2.81 0 0030 27.19V2.81A2.81 2.81 0 0027.19 0zM2.81.94h24.38a1.87 1.87 0 011.87 1.87v16.38H.94V2.81A1.87 1.87 0 012.81.94zm18.81 28.12h-2l8.88-8.87h.6v1.43zm7.44-6v2l-4 4H23zm-12.37-2.87l-8.88 8.87h-2l8.88-8.87zm1.41 0h2l-8.88 8.87h-2zm3.45 0h2l-8.87 8.87h-2zm3.45 0h2l-8.87 8.87h-2zm-22.12 0L.94 22.13v-1.94zM.94 23.54l3.35-3.35h2L.94 25.58zm.53 5a1.87 1.87 0 01-.53-1.31V27l6.8-6.8h2zm.91.51l8.82-8.82h2l-8.84 8.83H2.81a2 2 0 01-.43-.06zm24.11 0l2.57-2.57v.7a1.87 1.87 0 01-1.87 1.87z" fill="#8c8f94"/></svg>',
            'left' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><rect x=".47" y=".47" width="29.06" height="29.06" rx="2.34" fill="#fff"/><path d="M27.19 0H2.81A2.81 2.81 0 000 2.81v24.38A2.81 2.81 0 002.81 30h24.38A2.81 2.81 0 0030 27.19V2.81A2.81 2.81 0 0027.19 0zM.94 27.19v-1.6l8.87-8.87v2L1 27.58a1.67 1.67 0 01-.06-.39zM9.81 5L.94 13.82v-2l8.87-8.91zm0 1.41v2L.94 17.27v-2zm0 3.45v2L.94 20.72v-2zm0 3.45v2L.94 24.18v-2zm0 12.4l-3.4 3.4h-2l5.44-5.44zm0 1.41v2h-2zm0-25.58L.94 10.37v-2L8.33.94h1.48zM.94 6.92v-2L4.88.94h2zm2.52-6L.94 3.46v-.65A1.87 1.87 0 012.81.94zm-2 27.56l8.33-8.33v2L3 29.06h-.19a1.84 1.84 0 01-1.33-.56zm27.58-1.31a1.87 1.87 0 01-1.87 1.87H10.81V.94h16.38a1.87 1.87 0 011.87 1.87z" fill="#8c8f94"/></svg>',
        );

        $html .= alch_admin_get_field_sidebar( $data, false );

        $html .= '<div class="field__content"><div class="spacer__content jsAlchemySpacer">';

        foreach ( ['top', 'right', 'bottom', 'left'] as $side ) {
            $html .= sprintf( '<div class="spacer__side spacer__side--%s">',
                $side
            );

            $value = ! empty( $savedValue )
                ? esc_attr( $savedValue[$side] )
                : esc_attr( $data['default'][$side] );

            $html .= '<div class="spacer__controls">';

            $html .= sprintf( '<button class="spacer__button jsSpacerButton button-secondary" type="button" data-side="%s" data-type="decr">-</button>',
                $side
            );

            $html .= sprintf( '<input class="spacer__input" type="number" id="%s" min="0" value="%s" />',
                $data['id'] . '_' . $side,
                $value
            );

            $html .= sprintf( '<button class="spacer__button jsSpacerButton button-secondary" type="button" data-side="%s" data-type="incr">+</button>',
                $side
            );

            $html .= '</div>';

            $html .= $icons[$side];

            $html .= '</div>';
        }

        $html .= alch_get_validation_tooltip();

        $html .= '</div></div>';

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) : array {
        $value = (array) $value;

        $sanitisedValue = array();

        foreach ( $value as $side => $sideValue ) {
            $sanitisedValue[$side] = sanitize_text_field( $sideValue );
        }

        return $sanitisedValue;
    }

    function validate_value( $id, $value ) : array {
        $error = '';

        foreach ( ['top', 'right', 'bottom', 'left'] as $side ) {
            if( ! isset( $value->$side ) ) {
                $error = __( 'Value has a missing "' . $side . '" property', 'alchemy' );
            }
        }

        if( empty( $error ) ) {
            $error = apply_filters( 'alch_do_validate_spacer_value', '', $value );
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

    function prepare_value( $value, $id ) : array {
        $validValue = apply_filters( 'alch_prepared_spacer_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }
}
