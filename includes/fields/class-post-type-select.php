<?php

/**
 * @package Alchemy_Options\Includes\Fields
 *
 */

namespace Alchemy_Options\Includes\Fields;

use Alchemy_Options\Includes;
use WP_Query;

//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( __NAMESPACE__ . '\Post_Type_Select' ) ) {

    class Post_Type_Select extends Includes\Field {
        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--post-type-select jsAlchemyPostTypeSelectBlock" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"post-type-select","post-type":"{{POST-TYPE}}"}\'>
                    <div class="field__side">
                        <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                        {{DESCRIPTION}}
                    </div>
                    <div class="field__content"{{PADDED}}>
                        <select style="width: 100%;" class="jsAlchemyPostTypeSelect"{{MULTIPLE}} {{ATTRIBUTES}} data-nonce=\'{{NONCE}}\'>{{OPTIONS}}</select>
                        {{CLEAR}}
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field['nonce'] = json_encode( array(
                'id' => $field[ 'id' ] . '_pts_nonce',
                'value' => wp_create_nonce( $field[ 'id' ] . '_pts_nonce' )
            ) );

            $field['multiple'] = isset( $field['multiple'] ) ? $field['multiple'] : false;
            $field['post-type'] = ( isset( $field['post-type'] ) && post_type_exists( $field['post-type'] ) ) ? $field['post-type'] : 'post';
            $field['multiple'] = $this->is_multiple( $field['multiple'] );
            $field['clear'] = $field['multiple'] ? '' : '<button type="button" class="button button-secondary jsAlchemyPostTypeSelectClear"><span class="dashicons dashicons-trash"></span></button>';
            $field['options'] = $this->get_options_html( $field['value'] );
            $field['padded'] = '' !== $field['multiple'] ? '' : 'style="padding-right: 50px;"';
            $field['attributes'] = $this->concat_attributes( array(
                'id' => $field['id'],
                'name' => isset( $field['name'] ) ? $field['name'] . '[]' : $field['id'] . '[]',
            ) );

            return $field;
        }

        public function get_options_html( $value ) {
            if( ! isset( $value['ids'] ) || ! $value['ids'] ) {
                return '';
            }

            $optionsHTML = '';

            if( alch_is_not_empty_array( $value['ids'] ) ) {
                $the_query = new WP_Query( array(
                    'post_type' => $value['type'],
                    'post_status' => 'publish',
                    'post__in' => $value['ids']
                ) );

                $found_posts = [];

                if ( $the_query->have_posts() ) {
                    $found_posts = $the_query->get_posts();

                    usort( $found_posts, function( $a, $b ) use( $value ) {
                        if ( $a->ID == $b->ID ) { return 0; }

                        $position = array_search( $a->ID, $value['ids'] );
                        $position2 = array_search( $b->ID, $value['ids'] );

                        if ( $position2 !== false && $position !== false ) {
                            return ( $position < $position2 ) ? -1 : 1;
                        }

                        if( $position !== false ) { return -1; }
                        if( $position2 !== false ) { return 1; }

                        return ( $a->ID < $b->ID ) ? -1 : 1;
                    } );
                }

                $optionsHTML .= join('',  array_map(function( $post ) {
                    return sprintf( '<option selected="selected" value="%1$s">%2$s</option>', $post->ID, $post->post_title );
                }, $found_posts) );
            }

            return $optionsHTML;
        }

        public function is_multiple( $value ) {
            return $value ? ' multiple="multiple"' : '';
        }
    }
}