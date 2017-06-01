<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( class_exists( 'Alchemy_Fields_Loader' ) ) {
    return;
}

class Alchemy_Fields_Loader {
    private $valid_field_types;

    public function __construct() {
        //repeater should always be the last one since it can render all of the types
        $this->valid_field_types = array(
            'text', 'url', 'email', 'tel',
            'password', 'textarea', 'select',
            'checkbox', 'radio', 'datalist',
            'colorpicker', 'datepicker',
            'repeater'
        );
    }

    public function get_fields_html( $options ) {
        $fieldsHTML = "";

        foreach ( $options as $i => $field ) {

            if ( $this->is_valid_field_type( $field[ 'type' ] ) ) {
                if( ! isset( $field[ 'id' ] ) ) {
                    return '';
                }

                $options[$i][ 'id' ] = sanitize_key( $field[ 'id' ] );

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
                return new Alchemy_Text_Field();
            break;
            case 'password' :
                return new Alchemy_Password_Field();
            break;
            case 'radio' :
                return new Alchemy_Radio_Field();
            break;
            case 'checkbox' :
                return new Alchemy_Checkbox_Field();
            break;
            case 'select' :
                return new Alchemy_Select_Field();
            break;
            case 'textarea' :
                return new Alchemy_Textarea_Field();
            break;
            case 'colorpicker' :
                return new Alchemy_Colorpicker_Field();
            break;
            case 'datepicker' :
                return new Alchemy_Datepicker_Field();
            break;
            case 'repeater' :
                return new Alchemy_Repeater_Field();
            break;
            default : break;
        }

        return false;
    }

    public function is_valid_field_type ( $type ) {
        return in_array( $type, $this->valid_field_types );
    }
}