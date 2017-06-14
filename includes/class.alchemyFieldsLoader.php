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

    public function __construct( $networkFields = false ) {
        $this->networkFields = $networkFields;

        //repeater should always be the last one since it can render all of the types
        $this->valid_field_types = array(
            'text', 'url', 'email', 'tel',
            'password', 'textarea', 'select',
            'checkbox', 'radio', 'datalist',
            'colorpicker', 'datepicker', 'button-group',
            'upload', 'editor', 'image-radio',
            'textblock', 'slider', 'section',
            'post-type-select', 'taxonomy-select',
            'datalist',
            'repeater'
        );
    }

    public function get_fields_html( $options ) {
        $fieldsHTML = "";

        foreach ( $options as $i => $field ) {
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
                return new Alchemy_Text_Field( $this->networkFields );
            break;
            case 'password' :
                return new Alchemy_Password_Field( $this->networkFields );
            break;
            case 'radio' :
                return new Alchemy_Radio_Field( $this->networkFields );
            break;
            case 'checkbox' :
                return new Alchemy_Checkbox_Field( $this->networkFields );
            break;
            case 'select' :
                return new Alchemy_Select_Field( $this->networkFields );
            break;
            case 'textarea' :
                return new Alchemy_Textarea_Field( $this->networkFields );
            break;
            case 'colorpicker' :
                return new Alchemy_Colorpicker_Field( $this->networkFields );
            break;
            case 'datepicker' :
                return new Alchemy_Datepicker_Field( $this->networkFields );
            break;
            case 'button-group' :
                return new Alchemy_Button_Group_Field( $this->networkFields );
            break;
            case 'upload' :
                return new Alchemy_Upload_Field( $this->networkFields );
            break;
            case 'editor' :
                return new Alchemy_Editor_Field( $this->networkFields );
            break;
            case 'image-radio' :
                return new Alchemy_Image_Radio_Field( $this->networkFields );
            break;
            case 'textblock' :
                return new Alchemy_Textblock_Field( $this->networkFields );
            break;
            case 'slider' :
                return new Alchemy_Slider_Field( $this->networkFields );
            break;
            case 'section' :
                return new Alchemy_Section_Field( $this->networkFields );
            break;
            case 'post-type-select' :
                return new Alchemy_Post_Type_Select_Field( $this->networkFields );
            break;
            case 'taxonomy-select' :
                return new Alchemy_Taxonomy_Select_Field( $this->networkFields );
            break;
            case 'datalist' :
                return new Alchemy_Datalist_Field( $this->networkFields );
            break;
            case 'repeater' :
                return new Alchemy_Repeater_Field( $this->networkFields );
            break;
            default : break;
        }

        return false;
    }

    public function is_ok_without_id( $type ) {
        return in_array( $type, [ 'textblock' ] );
    }

    public function is_valid_field_type ( $type ) {
        return in_array( $type, $this->valid_field_types );
    }
}