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

    public function __construct( $networkFields = false ) {
        $this->networkFields = $networkFields;

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
                $field['_repeater-type'] = $repeaterCheck[1];
            }

            if ( $this->is_valid_field_type( $field[ 'type' ] ) ) {
                if( ! isset( $field[ 'id' ] ) && ! $this->is_ok_without_id( $field[ 'type' ] ) ) {
                    continue;
                }

                if( ! $this->is_ok_without_id( $field[ 'type' ] ) ) {
                    $options[$i][ 'id' ] = sanitize_key( $field[ 'id' ] );
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
                return new Fields\Text( $this->networkFields );
            break;
            case 'password' :
                return new Fields\Password( $this->networkFields );
            break;
            case 'radio' :
                return new Fields\Radio( $this->networkFields );
            break;
            case 'checkbox' :
                return new Fields\Checkbox( $this->networkFields );
            break;
            case 'select' :
                return new Fields\Select( $this->networkFields );
            break;
            case 'textarea' :
                return new Fields\Textarea( $this->networkFields );
            break;
            case 'colorpicker' :
                return new Fields\Colorpicker( $this->networkFields );
            break;
            case 'datepicker' :
                return new Fields\Datepicker( $this->networkFields );
            break;
            case 'button-group' :
                return new Fields\Button_Group( $this->networkFields );
            break;
            case 'upload' :
                return new Fields\Upload( $this->networkFields );
            break;
            case 'editor' :
                return new Fields\Editor( $this->networkFields );
            break;
            case 'image-radio' :
                return new Fields\Image_Radio( $this->networkFields );
            break;
            case 'textblock' :
                return new Fields\Textblock( $this->networkFields );
            break;
            case 'slider' :
                return new Fields\Slider( $this->networkFields );
            break;
            case 'sections' :
                return new Fields\Sections( $this->networkFields );
            break;
            case 'post-type-select' :
                return new Fields\Post_Type_Select( $this->networkFields );
            break;
            case 'taxonomy-select' :
                return new Fields\Taxonomy_Select( $this->networkFields );
            break;
            case 'datalist' :
                return new Fields\Datalist( $this->networkFields );
            break;
            case 'field-group' :
                return new Fields\Field_Group( $this->networkFields );
            break;
            case 'repeater' :
                return new Fields\Repeater( $this->networkFields );
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