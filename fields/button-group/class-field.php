<?php

namespace Alchemy\Fields\Button_Group;

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
        add_filter( 'alch_get_button_group_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_button_group_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_button_group_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_button_group_value', array( $this, 'validate_value' ), 10, 2 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_button_group_field',
            AlCHEMY_DIR_URL . 'fields/button-group/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/button-group/scripts.min.js' ),
            true
        );

        wp_enqueue_script( 'alch_button_group_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'button_group',
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

        $html = sprintf( '<div class="alchemy__field field field--%1$s clearfix jsAlchemyField jsAlchemyButtonGroup" data-alchemy="%2$s" id="%3$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'button_group',
                'id' => $data['id'],
                'multiple' => isset( $data['multiple'] )
            ) ) ),
            $data['id']
        );

        $html .= alch_admin_get_field_sidebar( $data, false );

        $html .= sprintf( '<div class="field__content"><div class="button-group">%s</div></div>',
            $this->get_btn_group_choices( $data['choices'], $savedValue )
        );

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
        $error = apply_filters( 'alch_do_validate_button_group_value', '', $value );

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
        $validValue = apply_filters( 'alch_prepared_button_group_value', $value );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }

    private function get_btn_group_choices( array $choices, $savedValue ) : string {
        $choicesHTML = "";

        foreach ( $choices as $choice ) {
            if ( is_string( $choice ) ) {
                $choice = array(
                    'value' => $choice,
                    'label' => $choice,
                );
            }

            if( empty( $savedValue ) ) {
                $choice['checked'] = $choice['checked'] ?? false;
            } else {
                $choice['checked'] = is_array( $savedValue )
                    ? in_array( $choice['value'], $savedValue )
                    : $choice['value'] === $savedValue;
            }

            $choice['disabled'] = $choice['disabled'] ?? false;

            $buttonClasses = ['button', 'button-secondary', 'jsAlchemyButtonGroupChoice'];

            if( $choice['checked'] ) {
                $buttonClasses[] = 'button-primary';
            }

            $choicesHTML .= sprintf (
                '<button type="button" class="%4$s" data-value="%1$s"%3$s>%2$s</button>',
                esc_attr( $choice['value'] ),
                $choice['label'],
                disabled( $choice['disabled'], true, false ),
                join( ' ', $buttonClasses )
            );
        }

        return $choicesHTML;
    }
}