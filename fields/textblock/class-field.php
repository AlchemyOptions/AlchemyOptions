<?php

namespace Alchemy\Fields\Textblock;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Field' ) ) {
    return;
}

class Field {
    function __construct() {
        add_filter( 'alch_register_field_type', array( $this, 'register_type' ) );
        add_filter( 'alch_ok_without_id_types', array( $this, 'add_as_ok_if_no_id' ) );
        add_filter( 'alch_get_textblock_option_html', array( $this, 'get_option_html' ) );
    }

    function add_as_ok_if_no_id( array $types ) : array {
        return array_merge( $types, ['textblock'] );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'textblock',
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

    function get_option_html( array $data ) : string {
        $html = sprintf( '<div class="alchemy__field field field--%1$s clearfix">',
            $data['type']
        );

        if( ! empty( $data['title'] ) ) {
            $html .= sprintf( '<div class="field__sidebar">%s</div>',
                alch_admin_get_field_label( $data, false )
            );
        }

        if( ! empty( $data['desc'] ) ) {
            $html .= sprintf( '<div class="field__content">%s</div>',
                alch_admin_get_field_description( $data['desc'] )
            );
        }

        $html .= '</div>';

        return $html;
    }
}