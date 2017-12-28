<?php

/**
 * @package Alchemy_Options\Includes
 *
 */

namespace Alchemy_Options\Includes;

//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Fields_Loader' ) ) {
    return;
}

class Fields_Loader {
    private $valid_field_types;

    private $networkFields;

    private $options;

    private $parentName;

    public function __construct( $networkFields = false, $options = array(), $parentName = '' ) {
        $this->networkFields = $networkFields;
        $this->options = $options;
        $this->parentName = $parentName;

        //repeater should always be the last one since it can render all of the types
        $this->valid_field_types = array(
            'text', 'url', 'email', 'tel',
            'password', 'textarea', 'select',
            'checkbox', 'radio', 'datalist',
            'colorpicker', 'datepicker', 'button-group',
            'upload', 'editor', 'image-radio',
            'textblock', 'slider', 'sections',
            'post-type-select', 'taxonomy-select',
            'datalist', 'field-group',
            'repeater'
        );
    }

    public function get_fields_html( $options ) {
        $fieldsHTML = "";

        foreach ( $options as $i => $field ) {
            $repeaterCheck = explode( ':', $field['type'] );

            if( 2 === count( $repeaterCheck ) && 'repeater' === $repeaterCheck[0] ) {
                $field['type'] = 'repeater';
                $field['repeater'] = array(
                    'type' => $repeaterCheck[1]
                );
            }

            if ( $this->is_valid_field_type( $field[ 'type' ] ) ) {
                if( ! isset( $field[ 'id' ] ) && ! $this->is_ok_without_id( $field[ 'type' ] ) ) {
                    continue;
                }

                if( ! $this->is_ok_without_id( $field[ 'type' ] ) ) {
                    $options[$i][ 'id' ] = sanitize_key( $field[ 'id' ] );
                }

                if( "" !== $this->parentName ) {
                    $options[$i]['name'] = isset( $options[$i]['name'] ) ? $options[$i]['name'] : $this->parentName . '[' . $field[ 'id' ] . ']';
                }

                $fieldInstance = $this->choose_class( $field[ 'type' ] );

                if( $fieldInstance ) {
                    $field = $fieldInstance->normalize_field_keys( $field );

                    $fieldsHTML .= $fieldInstance->get_html( $field );
                }
            }
        }

        return $fieldsHTML;
    }

    public function choose_class( $type ) {

        switch ( $type ) {
            case 'email' :
            case 'url' :
            case 'tel' :
            case 'text' :
                return new Fields\Text( $this->networkFields, $this->options );
            break;
            case 'password' :
                return new Fields\Password( $this->networkFields, $this->options );
            break;
            case 'radio' :
                return new Fields\Radio( $this->networkFields, $this->options );
            break;
            case 'checkbox' :
                return new Fields\Checkbox( $this->networkFields, $this->options );
            break;
            case 'select' :
                return new Fields\Select( $this->networkFields, $this->options );
            break;
            case 'textarea' :
                return new Fields\Textarea( $this->networkFields, $this->options );
            break;
            case 'colorpicker' :
                return new Fields\Colorpicker( $this->networkFields, $this->options );
            break;
            case 'datepicker' :
                return new Fields\Datepicker( $this->networkFields, $this->options );
            break;
            case 'button-group' :
                return new Fields\Button_Group( $this->networkFields, $this->options );
            break;
            case 'upload' :
                return new Fields\Upload( $this->networkFields, $this->options );
            break;
            case 'editor' :
                return new Fields\Editor( $this->networkFields, $this->options );
            break;
            case 'image-radio' :
                return new Fields\Image_Radio( $this->networkFields, $this->options );
            break;
            case 'textblock' :
                return new Fields\Textblock( $this->networkFields, $this->options );
            break;
            case 'slider' :
                return new Fields\Slider( $this->networkFields, $this->options );
            break;
            case 'sections' :
                return new Fields\Sections( $this->networkFields, $this->options );
            break;
            case 'post-type-select' :
                return new Fields\Post_Type_Select( $this->networkFields, $this->options );
            break;
            case 'taxonomy-select' :
                return new Fields\Taxonomy_Select( $this->networkFields, $this->options );
            break;
            case 'datalist' :
                return new Fields\Datalist( $this->networkFields, $this->options );
            break;
            case 'field-group' :
                return new Fields\Field_Group( $this->networkFields, $this->options );
            break;
            case 'repeater' :
                return new Fields\Repeater( $this->networkFields, $this->options );
            break;
            default : break;
        }

        return false;
    }

    public function is_ok_without_id( $type ) {
        return in_array( $type, [ 'textblock', 'sections' ] );
    }

    public function is_valid_field_type ( $type ) {
        return in_array( $type, $this->valid_field_types );
    }
}