<?php

namespace Alchemy\Fields\Field_Group;

use Alchemy\Options;
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
        add_filter( 'alch_get_field_group_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_field_group_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_field_group_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_field_group_value', array( $this, 'validate_value' ), 10, 2 );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_field_group_field',
            AlCHEMY_DIR_URL . 'fields/field-group/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/field-group/scripts.min.js' ),
            true
        );

        wp_enqueue_script( 'alch_field_group_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'field_group',
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

        $data['fields'] = empty( $data['fields'] ) ? [] : $data['fields'];

        $html = sprintf( '<div class="alchemy__field field field--%1$s clearfix jsAlchemyField" data-alchemy="%2$s" id="%3$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'field_group',
                'id' => $data['id'],
                'fields-data' => array_map( function( $field ) {
                    return array( 'type' => $field['type'], 'id' => $field['id'] );
                }, $data['fields'] )
            ) ) ),
            $data['id']
        );

        foreach ( $data['fields'] as $i => $field ) {
            $savedField = is_array( $savedValue ) ? array_values( array_filter( $savedValue, function( $savedValueField ) use( $field ) {
                $fieldType = $field['type'];
                $savedFieldType = $savedValueField['type'];

                $repeater = Options::get_repeater_id_details( $fieldType );
                $isRepeater = Options::get_repeater_id_details( $savedFieldType );

                if( $repeater ) {
                    $fieldType = 'repeater';
                }

                if( $isRepeater ) {
                    $savedFieldType = 'repeater';
                }

                return $fieldType === $savedFieldType && substr( $savedValueField['id'], -strlen( $field['id'] ) ) === $field['id'];
            } ) ) : null;

            $data['fields'][$i]['id'] = sprintf( '%s-%s', $data['id'], $field['id'] );

            if( ! empty( $savedField ) ) {
                $data['fields'][$i]['value'] = $savedField[0]['value'];
            }
        }

        if( isset( $type ) ) {
            switch( $type ) {
                case 'metabox' :
                    $html .= Options::get_meta_html( get_the_ID(), $data['fields'] );
                break;
                case 'options' :
                    $html .= Options::get_options_html( $data['fields'] );
                break;
                case 'network-options' :
                    $html .= Options::get_network_options_html( $data['fields'] );
                break;
            }
        }

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) : array {
        $sanitisedValues = [];

        foreach ( $value as $field ) {
            $valueType = $field->type;
            $repeater = Options::get_repeater_id_details( $valueType );

            if( $repeater ) {
                $valueType = 'repeater';
            }

            $sanitisedValues[] = array(
                'type' => $field->type,
                'id' => $field->id,
                'value' => apply_filters( "alch_sanitize_{$valueType}_value", $field->value )
            );
        }

        return $sanitisedValues;
    }

    function validate_value( $id, $value ) : array {
        $error = apply_filters( 'alch_do_validate_field_group_value', '', $value );

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
        $preparedValue = [];

        foreach ( $value as $item ) {
            $preparedValue[$item['id']] = $item['value'];
        }

        $validValue = apply_filters( 'alch_prepared_field_group_value', $preparedValue );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }
}
