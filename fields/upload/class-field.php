<?php

namespace Alchemy\Fields\Upload;

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
        add_filter( 'alch_get_upload_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_upload_value', array( $this, 'sanitize_value' ) );
        add_action( 'alch_prepare_upload_value', array( $this, 'prepare_value' ), 10, 3 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        //todo: add validity filter
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_upload_field',
            AlCHEMY_DIR_URL . 'fields/upload/scripts.min.js',
            array( 'alch_admin_scripts' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/upload/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_upload_field',
            AlCHEMY_DIR_URL . 'fields/upload/styles.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/upload/styles.min.css' )
        );

        wp_enqueue_script( 'alch_upload_field' );
        wp_enqueue_style( 'alch_upload_field' );
    }

    function register_type( array $types ) : array {
        $myTypes = array(
            array(
                'id' => 'upload',
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

        $addButtonTitle = apply_filters(
            "alch_{$data['id']}_upload_add_button_title",
            apply_filters( 'alch_default_upload_add_button_title',
                __( 'Select or Upload Media', 'alchemy' )
            )
        );

        $addButtonText = apply_filters(
            "alch_{$data['id']}_upload_add_button_text",
            apply_filters( 'alch_default_upload_add_button_text',
                __( 'Select or Upload Media', 'alchemy' )
            )
        );

        $html = sprintf( '<div class="alchemy__field field field--%1$s clearfix jsAlchemyField jsAlchemyUploader" data-alchemy="%2$s">',
            $data['type'],
            esc_attr( json_encode( array(
                'type' => 'upload',
                'id' => $data['id']
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data, false );

        $value = ! empty( $savedValue )
            ? sprintf( ' value="%s"', esc_attr( $savedValue ) )
            : '';

        $html .= '<div class="field__content">';

        $html .= '<div>';

        $html .= sprintf( '<input id="%1$s" class="jsAlchemyUploaderInput" type="hidden"%2$s />',
            $data['id'],
            $value
        );

        $html .= sprintf( '<button type="button" class="button button-primary jsAlchemyUploadTrigger" data-strings="%s"><span class="dashicons dashicons-admin-media"></span></button>',
            esc_attr( json_encode( array(
                'title' => $addButtonTitle,
                'text' => $addButtonText
            ) ) )
        );

        $html .= '<button type="button" class="button button-secondary jsAlchemyUploadRemove"><span class="dashicons dashicons-trash"></span></button>';

        $html .= '</div>';

        $html .= sprintf( '<div class="field__results jsAlchemyUploaderResults">%s</div>',
            empty( $savedValue ) ? '' : $this->get_saved_upload_html( $savedValue )
        );

        $html .= '</div>';

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    function sanitize_value( $value ) : string {
        return sanitize_text_field( $value );
    }

    function prepare_value( $value, $id ) {
        $valueToReturn = $this->get_attached_image( $value );

        $validValue = apply_filters( 'alch_prepared_upload_value', $valueToReturn );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }

    private function get_saved_upload_html( $value ) : string {
        $savedUpload = '';

        if( ! empty( $value ) ) {
            if( wp_attachment_is( 'image', $value ) ) {
                $imageData = wp_get_attachment_image_src( $value, 'thumbnail' );

                if( ! empty( $imageData ) ) {
                    $savedUpload .= sprintf( '<div class="field__image"><img src="%s" alt="" /></div>', $imageData[0] );
                }
            } else if( wp_attachment_is( 'video', $value ) ) {
                $videoMetaData = wp_get_attachment_metadata( $value );

                $savedUpload .= sprintf( '<img src="%1$s" alt="" /><div>%2$s <span class="alchemy__filesize">(%3$s)</span></div>',
                    apply_filters( 'alch_default_video_icon_url', get_home_url() . '/wp-includes/images/media/video.png' ),
                    sprintf( '%1$s.%2$s', get_the_title( $value ), $videoMetaData['fileformat'] ),
                    size_format( $videoMetaData['filesize'] )
                );
            } else if( wp_attachment_is( 'audio', $value ) ) {
                $videoMetaData = wp_get_attachment_metadata( $value );

                $savedUpload .= sprintf( '<img src="%1$s" alt="" /><div>%2$s <span class="alchemy__filesize">(%3$s)</span></div>',
                    apply_filters( 'alch_default_audio_icon_url', get_home_url() . '/wp-includes/images/media/audio.png' ),
                    esc_html( basename( get_attached_file( $value ) ) ),
                    size_format( $videoMetaData['filesize'] )
                );
            }
        }

        return $savedUpload;
    }

    private function get_attached_image( $value ) {
        $valueToReturn = $value;
        if( wp_attachment_is( 'image', $value ) ) {
            $imageMeta = wp_get_attachment_metadata( $value );

            if( empty( $imageMeta ) ) {
                return '';
            }

            if( is_array( $imageMeta['sizes'] ) ) {
                $valueToReturn = array(
                    'id' => (int) $value,
                    'type' => 'image',
                    'sizes' => array(),
                );

                foreach( $imageMeta['sizes'] as $sizeTitle => $sizeValue ) {
                    $valueToReturn['sizes'][$sizeTitle] = wp_get_attachment_image_src( $value, $sizeTitle );
                }

                $valueToReturn['sizes']['full'] = wp_get_attachment_image_src( $value, 'full' );
            }
        } elseif( wp_attachment_is( 'video', $value ) ) {
            $valueToReturn = array(
                'id' => (int) $value,
                'type' => 'video',
                'url' => wp_get_attachment_url( $value ),
            );
        } elseif( wp_attachment_is( 'audio', $value ) ) {
            $valueToReturn = array(
                'id' => (int) $value,
                'type' => 'audio',
                'url' => wp_get_attachment_url( $value ),
            );
        }

        return $valueToReturn;
    }
}