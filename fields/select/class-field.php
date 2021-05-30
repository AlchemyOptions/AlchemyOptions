<?php

namespace Alchemy\Fields\Select;

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
        add_filter( 'alch_get_select_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_select_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'alch_validate_select_value', array( $this, 'validate_value' ), 10, 2 );
        add_action( 'alch_prepare_select_value', array( $this, 'prepare_value' ), 10, 3 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_select_field',
            AlCHEMY_DIR_URL . 'fields/select/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/select/scripts.min.js' ),
            true
        );

        wp_enqueue_script( 'alch_select_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'select',
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
                'type' => 'select',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data );

        $multiple = isset( $data['multiple'] ) && true === $data['multiple'];

        $html .= sprintf( '<div class="field__content"><select id="%1$s"%2$s>',
            $data['id'],
            $multiple ? ' multiple="true"' : ''
        );

        if( ! empty( $data['optgroups'] ) ) {
            $html .= $this->get_select_optgroups_html( $data['optgroups'], $savedValue );
        } else if( ! empty( $data['choices'] ) ) {
            $html .= $this->get_select_choices_html( $data['choices'], $savedValue );
        }

        $html .= '</select></div>';

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
        $error = apply_filters( 'alch_do_validate_select_value', '', $value );

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
        if( '__empty' === $value ) {
            $value = '';
        }

        $validValue = apply_filters( 'alch_prepared_select_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }

    private function get_select_optgroups_html( array $optgroups, $savedValue ) : string {
        return join( '', array_map( function( $optgroup ) use ( $savedValue ) {
            $optgroup['choices'] = empty( $optgroup['choices'] ) ? [] : $optgroup['choices'];

            return sprintf( '<optgroup%1$s%2$s>%3$s</optgroup>',
                isset( $optgroup['label'] ) ? sprintf( ' label="%s"', $optgroup['label'] ) : '',
                isset( $optgroup['disabled'] ) && true === $optgroup['disabled'] ? ' disabled="disabled"' : '',
                $this->get_select_choices_html( $optgroup['choices'], $savedValue )
            );
        }, $optgroups ) );
    }

    private function get_select_choices_html( array $choices, $savedValue ) : string {
        return join( '', array_map( function( $choice ) use ( $savedValue ) {
            return $this->get_select_option_html( $choice, $savedValue );
        }, $choices ) );
    }

    private function get_select_option_html( $choice, $savedValue ) : string {
        $html = '';

        if( is_string( $choice ) ) {
            $choice = array(
                'value' => $choice,
                'label' => $choice,
            );
        }

        if( empty( $savedValue ) ) {
            $choice['selected'] = $choice['selected'] ?? false;
        } else {
            $choice['selected'] = is_array( $savedValue )
                ? in_array( $choice['value'], $savedValue )
                : $choice['value'] === $savedValue;
        }

        $choice['disabled'] = $choice['disabled'] ?? false;

        $html .= sprintf( '<option value="%1$s"%2$s%3$s>%4$s</option>',
            esc_attr( $choice['value'] ),
            disabled( $choice['disabled'], true, false ),
            selected( $choice['selected'], true, false ),
            $choice['label']
        );

        return $html;
    }
}