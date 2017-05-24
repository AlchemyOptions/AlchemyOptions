<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( class_exists( 'Alchemy_Option_Fields' ) ) {
    return;
}

class Alchemy_Option_Fields {
    private $passed_opts;
    private $valid_field_types;

    public function __construct( $options ) {
        include_once ( ALCHEMY_OPTIONS_PLUGIN_DIR . "includes/fields/text.php" );
        include_once ( ALCHEMY_OPTIONS_PLUGIN_DIR . "includes/fields/url.php" );
        include_once ( ALCHEMY_OPTIONS_PLUGIN_DIR . "includes/fields/password.php" );
        include_once ( ALCHEMY_OPTIONS_PLUGIN_DIR . "includes/fields/email.php" );
        include_once ( ALCHEMY_OPTIONS_PLUGIN_DIR . "includes/fields/textarea.php" );
        include_once ( ALCHEMY_OPTIONS_PLUGIN_DIR . "includes/fields/select.php" );
        include_once ( ALCHEMY_OPTIONS_PLUGIN_DIR . "includes/fields/repeater.php" ); //should always be the last include since it can render all of the types

        $this->passed_opts = $options;
        $this->valid_field_types = array(
            'text', 'url', 'email', 'password', 'textarea', 'select', 'repeater'
        );
    }

    public function get_fields_html() {
        $fieldType = null;
        $fieldsHTML = "";

        foreach ( $this->passed_opts as $field ) {
            if ( $this->is_valid_field_type( $field[ 'type' ] ) ) {
                $fieldsHTML .= call_user_func( 'alch_' . $field[ 'type' ] . '_field', $field );
            }
        }

        return $fieldsHTML;
    }

    public function is_valid_field_type ( $type ) {
        return in_array( $type, $this->valid_field_types );
    }
}