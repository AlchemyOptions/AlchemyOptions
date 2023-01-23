<?php
/**
 * @package Alchemy_Options
 *
 * @wordpress-plugin
 * Plugin Name: Alchemy Options
 * Plugin URI: https://docs.alchemy-options.com/
 * Description: Just another Options plugin inspired by the wonderful Option Tree and Archetype.
 * Version: 1.0.0
 * Author: Alex Bondarev
 * Author URI: http://alexbondarev.com
 * Text Domain: alchemy-options
 *
 */

namespace Alchemy;

use Alchemy\Fields\{ Upload, Textblock, Url, Textarea, Text, Tel, Taxonomy_Select, Slider, Select, Sections, Radio, Post_Type_Select, Password, Email, Editor, Datepicker, Tokens, Datalist, Colorpicker, Checkbox_Image, Checkbox, Button_Group, Field_Group, Spacer, Repeater };
use Alchemy\Includes;
use WP_Admin_Bar;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;
use WP_User;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AlCHEMY_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'AlCHEMY_DIR_PATH', plugin_dir_path( __FILE__ ) );

include_once( AlCHEMY_DIR_PATH . 'autoload.php' );
include_once( AlCHEMY_DIR_PATH . 'includes/functions.php' );

new Upload\Field();
new Textblock\Field();
new Url\Field();
new Textarea\Field();
new Text\Field();
new Tel\Field();
new Taxonomy_Select\Field();
new Slider\Field();
new Select\Field();
new Sections\Field();
new Radio\Field();
new Post_Type_Select\Field();
new Password\Field();
new Email\Field();
new Editor\Field();
new Datepicker\Field();
new Datalist\Field();
new Tokens\Field();
new Colorpicker\Field();
new Checkbox_Image\Field();
new Checkbox\Field();
new Button_Group\Field();
new Field_Group\Field();
new Spacer\Field();
new Repeater\Field();

class Options {
    private static $registeredTypes;
    private static $registeredRepeaters;
    private static $options;
    private static $networkOptions;
    private static $metaBoxes;
    private static $userMetaFields;
    private static $optionPages = [];
    private static $processedPages = [];
    private static $networkOptionPages = [];
    private static $processedNetworkPages = [];
    private static $okWithoutID;

    function __construct() {
        if( is_multisite() ) {
            add_action( 'network_admin_menu', array( $this, 'create_network_options_pages' ) );
        }

        add_action( 'admin_bar_menu', array( $this, 'create_admin_bar_menu' ), 500 );
        add_action( 'admin_menu', array( $this, 'create_options_pages' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'rest_api_init', array( $this, 'create_rest_endpoints' ) );

        add_action( 'block_editor_meta_box_hidden_fields', array( $this, 'append_temp_editor' ) ); // Gutenberg
        add_action( 'edit_form_advanced', array( $this, 'append_temp_editor' ) ); // pre-Gutenberg metabox for everything but pages
        add_action( 'edit_page_form', array( $this, 'append_temp_editor' ) ); // pre-Gutenberg metabox for pages
        add_action( 'alch_output_after_options', array( $this, 'append_temp_editor' ) );

        add_action( 'show_user_profile', array( $this, 'add_user_meta_fields' ) );
        add_action( 'show_user_profile', array( $this, 'append_temp_editor' ) );
        add_action( 'edit_user_profile', array( $this, 'add_user_meta_fields' ) );
        add_action( 'edit_user_profile', array( $this, 'append_temp_editor' ) );

        $this::$okWithoutID = apply_filters( 'alch_ok_without_id_types', [] );

        $this->add_metaboxes();
    }

    function create_admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) : void {
        if( empty( self::$optionPages ) ) {
            return;
        }

        $adminBarMenu = new Includes\Admin_Bar_Menu( $wp_admin_bar );
        $adminBarMenu->create_menu( self::$optionPages );
    }

    function permission_callback() : bool {
        $pageID = $_POST['page-id'] ?? null;

        if( ! empty( $pageID ) ) {
            $pageCap = Includes\Options_Page::get_page_capabilities( $pageID );

            return current_user_can( $pageCap );
        }

        return false;
    }

    function handle_save_options( WP_REST_Request $request ) {
        $bodyParams = $request->get_body_params();

        if( empty( $bodyParams['_wpnonce'] ) || ! wp_verify_nonce( $bodyParams['_wpnonce'], 'wp_rest' ) || ! is_user_logged_in() ) {
			return rest_ensure_response( new WP_Error(
				'alch-save-unauthenticated',
				__( 'Nonce check failed', 'alchemy' ),
				array( 'status' => 401 )
			) );
        }

        if( ! empty( $bodyParams['values'] ) ) {
            $decodedValues = json_decode( $bodyParams['values'] );

            $checks = $this->check_values( $decodedValues );

            if( ! empty( $checks ) ) {
				return rest_ensure_response( new WP_Error(
					'alch-save-validation-errors',
					__( 'Options not saved', 'alchemy' ),
					array(
						'success' => false,
						'status' => 422,
						'invalid-fields' => $checks
					)
				) );
            }

            if( $this->save_values( $decodedValues ) ) {
				return rest_ensure_response( array( 'success' => true ) );
            }
        }

		return rest_ensure_response( new WP_Error(
			'alch-save-options-error',
			__( 'Options not saved', 'alchemy' ),
			array( 'status' => 400 )
		) );
    }

    function handle_save_network_options( WP_REST_Request $request ) {
        $bodyParams = $request->get_body_params();

        if( empty( $bodyParams['_wpnonce'] ) || ! wp_verify_nonce( $bodyParams['_wpnonce'], 'wp_rest' ) ) {
			return rest_ensure_response( new WP_Error(
				'alch-save-unauthenticated',
				__( 'Nonce check failed', 'alchemy' ),
				array( 'status' => 401 )
			) );
        }

        if( ! empty( $bodyParams['values'] ) ) {
            $decodedValues = json_decode( $bodyParams['values'] );

            $checks = $this->check_values( $decodedValues );

            if( ! empty( $checks ) ) {
				return rest_ensure_response( new WP_Error(
					'alch-save-validation-errors',
					__( 'Options not saved', 'alchemy' ),
					array(
						'success' => false,
						'status' => 422,
						'invalid-fields' => $checks
					)
				) );
            }

            if( $this->save_network_values( $decodedValues ) ) {
				return rest_ensure_response( array( 'success' => true ) );
            }
        }

		return rest_ensure_response( new WP_Error(
			'alch-save-options-error',
			__( 'Options not saved', 'alchemy' ),
			array( 'status' => 400 )
		) );
    }

    function handle_save_meta( WP_REST_Request $request ) {
        $bodyParams = $request->get_body_params();

        if( empty( $bodyParams['_wpnonce'] ) || ! wp_verify_nonce( $bodyParams['_wpnonce'], 'wp_rest' ) ) {
			return rest_ensure_response( new WP_Error(
				'alch-save-unauthenticated',
				__( 'Nonce check failed', 'alchemy' ),
				array( 'status' => 401 )
			) );
        }

        if( ! empty( $bodyParams['values'] ) ) {
            $decodedValues = json_decode( $bodyParams['values'] );

            $checks = $this->check_values( $decodedValues );

            if( ! empty( $checks ) ) {
				return rest_ensure_response( new WP_Error(
					'alch-save-validation-errors',
					__( 'Metabox values not saved', 'alchemy' ),
					array(
						'success' => false,
						'status' => 422,
						'invalid-fields' => $checks
					)
				) );
            }

            if( $this->save_meta_values( $bodyParams['post-id'], $decodedValues ) ) {
				return rest_ensure_response( array( 'success' => true ) );
            }
        }

		return rest_ensure_response( new WP_Error(
			'alch-save-options-error',
			__( 'Metabox values not saved', 'alchemy' ),
			array( 'status' => 400 )
		) );
    }

    function handle_save_user_profile( WP_REST_Request $request ) {
        $bodyParams = $request->get_body_params();

        if( empty( $bodyParams['_wpnonce'] ) || ! wp_verify_nonce( $bodyParams['_wpnonce'], 'wp_rest' ) ) {
			return rest_ensure_response( new WP_Error(
				'alch-save-unauthenticated',
				__( 'Nonce check failed', 'alchemy' ),
				array( 'status' => 401 )
			) );
        }

        if( ! empty( $bodyParams['values'] ) ) {
            $decodedValues = json_decode( $bodyParams['values'] );

            $checks = $this->check_values( $decodedValues );

            if( ! empty( $checks ) ) {
				return rest_ensure_response( new WP_Error(
					'alch-save-validation-errors',
					__( 'User profile values not saved', 'alchemy' ),
					array(
						'success' => false,
						'status' => 422,
						'invalid-fields' => $checks
					)
				) );
            }

            if( $this->save_user_profile_values( $bodyParams['user-id'], $decodedValues ) ) {
				return rest_ensure_response( array( 'success' => true ) );
            }
        }

		return rest_ensure_response( new WP_Error(
			'alch-save-userprofile-error',
			__( 'User profile values not saved', 'alchemy' ),
			array( 'status' => 400 )
		) );
    }

    function append_temp_editor() : void {
        //hack to include editor assets. Will be removed when there's a way to get the full tinyMCE settings and assets

        echo '<div class="hidden jsAlchemyTempEditor">';
        wp_editor( '', 'alchemy-temp-editor' );
        echo '</div>';
    }

    function create_rest_endpoints() : void {
        register_rest_route( 'alchemy/v1', '/save-options/', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'handle_save_options' ),
            'permission_callback' => array( $this, 'permission_callback' ),
        ) );
        register_rest_route( 'alchemy/v1', '/save-network-options/', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'handle_save_network_options' ),
            'permission_callback' => array( $this, 'permission_callback' ),
        ) );

        register_rest_route( 'alchemy/v1', '/save-metaboxes/', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'handle_save_meta' ),
            'permission_callback' => function() {
                return current_user_can( apply_filters( 'alch_save_metaboxes_capabilities', 'edit_posts' ) );
            }
        ) );

        register_rest_route( 'alchemy/v1', '/save-user-profile/', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'handle_save_user_profile' ),
            'permission_callback' => function() {
                $capability = current_user_can( 'edit_users' ) ? 'edit_users' : 'read';

                return current_user_can( $capability );
            }
        ) );
    }

    function enqueue_admin_assets() : void {
        if( ! is_admin() ) {
            return;
        }

        wp_register_script( 'alch_popper', AlCHEMY_DIR_URL . 'scripts/vendor/popper.min.js', [], '2.4.0' );
        wp_register_script( 'alch_bluebird', AlCHEMY_DIR_URL . 'scripts/vendor/bluebird.min.js', [], '3.5.0' );

        wp_register_script(
            'alch_admin_scripts',
            AlCHEMY_DIR_URL . 'scripts/alchemy.min.js',
            array( 'jquery', 'alch_bluebird', 'alch_popper' ),
            filemtime( AlCHEMY_DIR_PATH . 'scripts/alchemy.min.js' ),
            true
        );

        wp_register_style(
            'alch_admin_styles',
            AlCHEMY_DIR_URL . 'styles/alchemy.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'styles/alchemy.min.css' )
        );

        wp_localize_script( 'alch_admin_scripts', 'AlchemyData', array(
            'save-options' => array(
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => get_rest_url( null, '/alchemy/v1/save-options/' ),
            ),
            'save-network-options' => array(
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => get_rest_url( null, '/alchemy/v1/save-network-options/' ),
            ),
            'save-metaboxes' => array(
                'postID' => get_the_ID(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => get_rest_url( null, '/alchemy/v1/save-metaboxes/' ),
            ),
            'save-user-profile' => array(
                'userID' => $this->get_user_ID(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => get_rest_url( null, '/alchemy/v1/save-user-profile/' ),
            ),
        ) );

        wp_enqueue_script( 'alch_admin_scripts' );
        wp_enqueue_style( 'alch_admin_styles' );
    }

    function add_user_meta_fields( WP_User $user ) : void {
        $userMetaFields = self::get_user_meta_fields();

        if( empty( $userMetaFields ) || wp_doing_ajax() ) {
            return;
        }

        printf( '<div class="alchemy alchemy--profile jsAlchemy jsAlchemyUserProfile">%s</div>', $this->get_user_meta_html( $user->ID, $userMetaFields ) );
    }

    static function get_registered_field_types() : array {
        if( empty( self::$registeredTypes ) ) {
            self::register_types( apply_filters( 'alch_register_field_type', [] ) );
        }

        return self::$registeredTypes;
    }

    static function get_registered_repeaters() : array {
        if( empty( self::$registeredRepeaters ) ) {
            self::register_repeater_types( apply_filters( 'alch_repeaters', [] ) );
        }

        return self::$registeredRepeaters;
    }

    static function get_options() : array {
        if( empty( self::$options ) ) {
            self::$options = apply_filters( 'alch_options', [] );
        }

        return self::$options;
    }

    static function get_network_options() : array {
        if( empty( self::$networkOptions ) ) {
            self::$networkOptions = apply_filters( 'alch_network_options', [] );
        }

        return self::$networkOptions;
    }

    static function get_metaboxes() : array {
        if( empty( self::$metaBoxes ) ) {
            self::$metaBoxes = apply_filters( 'alch_meta_boxes', [] );
        }

        return self::$metaBoxes;
    }

    static function get_user_meta_fields() : array {
        if( empty( self::$userMetaFields ) ) {
            self::$userMetaFields = apply_filters( 'alch_user_meta_fields', [] );
        }

        return self::$userMetaFields;
    }

    static function get_field_type_settings( string $type ) : array {
        $fieldTypes = self::get_registered_field_types();
        $fieldTypeSettings = $fieldTypes[$type]['available-for'];

        if( empty( $fieldTypeSettings ) ) {
            $fieldTypeSettings = array(
                'options' => true,
                'repeaters' => true,
                'metaboxes' => true,
                'userprofile' => true,
            );
        }

        return $fieldTypeSettings;
    }

    static function get_repeater_id_details( string $type ) : array {
        $typeParts = explode( ':', $type );

        if( count( $typeParts ) > 1 && 'repeater' === $typeParts[0] ) {
            return array( 'type' => 'repeater', 'id' => $typeParts[1] );
        }

        return [];
    }

    static function get_options_html( array $options ) : string {
        $html = '';
        $parsedOptions = [];
        $registeredTypes = self::get_registered_field_types();
        $registeredRepeaters = self::get_registered_repeaters();

        foreach ( $options as $option ) {
            $optionType = $option['type'];

            $repeater = self::get_repeater_id_details( $optionType );

            if( empty( $optionType ) ) {
                trigger_error( 'Option \'type\' is missing. ' . print_r( $option, true ), E_USER_ERROR );
            }

            if( empty( $option['id'] ) && ! in_array( $optionType, self::$okWithoutID ) ) {
                trigger_error( 'Option \'id\' is missing. ' . print_r( $option, true ), E_USER_ERROR );
            }

            //todo: check for valid ID with underscores

            if( isset( $option['id'] ) && ! empty( $parsedOptions[$option['id']] ) ) {
                //todo: if it's a section - need to check its children

                trigger_error( sprintf( 'The \'%s\' option ID is already present. Please use a different one.',
                    $option['id']
                ), E_USER_ERROR );
            }

            if( ! empty( $repeater ) ) {
                $optionType = $repeater['type'];

                if( empty( $registeredRepeaters[$repeater['id']] ) ) {
                    trigger_error( 'Repeater type \''. $repeater['id'] .'\' is not registered. Please use the \'alch_repeaters\' filter to do that.', E_USER_WARNING );
                }
            }

            if( empty( $registeredTypes[$optionType] ) ) {
                trigger_error( 'Option type \''. $optionType .'\' is not registered. Please use the \'alch_register_field_type\' filter to do that.', E_USER_WARNING );

                continue;
            }

            $typeSettings = self::get_field_type_settings( $optionType );

            if( ! isset( $typeSettings['options'] ) || ! $typeSettings['options'] ) {
                continue;
            }

            global $wp_filter;

            $htmlFilter = $wp_filter["alch_get_{$optionType}_option_html"];
            $optionDefault = isset( $option['default'] ) ? $option['default'] : '';

            $savedValue = isset( $option['id'] )
                ? alch_admin_get_saved_option( alch_modify_alchemy_id( $option['id'] ), $optionDefault )
                : '';

            if( isset( $option['value'] ) ) {
                $savedValue = $option['value'];
            }

            if( isset( $htmlFilter ) && ! empty( $htmlFilter->callbacks ) ) {
                $optionHtml = apply_filters( "alch_get_{$optionType}_option_html", $option, $savedValue, 'options' );

                if( ! empty( $optionHtml ) ) {
                    $html .= $optionHtml;
                }
            } else {
                trigger_error( 'No alch_get_'. $optionType . '_option_html callback registered.', E_USER_WARNING );
            }

            if( ! empty( $option['id'] ) ) {
                $parsedOptions[$option['id']] = $option;
            }
        }

        return $html;
    }

    static function get_network_options_html( array $options ) : string {
        $html = '';
        $parsedOptions = [];
        $registeredTypes = self::get_registered_field_types();
        $registeredRepeaters = self::get_registered_repeaters();

        foreach ( $options as $option ) {
            $optionType = $option['type'];

            $repeater = self::get_repeater_id_details( $optionType );

            if( empty( $optionType ) ) {
                trigger_error( 'Option \'type\' is missing. ' . print_r( $option, true ), E_USER_ERROR );
            }

            if( empty( $option['id'] ) && ! in_array( $optionType, self::$okWithoutID ) ) {
                trigger_error( 'Option \'id\' is missing. ' . print_r( $option, true ), E_USER_ERROR );
            }

            //todo: check for valid ID with underscores

            if( isset( $option['id'] ) && ! empty( $parsedOptions[$option['id']] ) ) {
                //todo: if it's a section - need to check its children

                trigger_error( sprintf( 'The \'%s\' option ID is already present. Please use a different one.',
                    $option['id']
                ), E_USER_ERROR );
            }

            if( ! empty( $repeater ) ) {
                $optionType = $repeater['type'];

                if( empty( $registeredRepeaters[$repeater['id']] ) ) {
                    trigger_error( 'Repeater type \''. $repeater['id'] .'\' is not registered. Please use the \'alch_repeaters\' filter to do that.', E_USER_WARNING );
                }
            }

            if( empty( $registeredTypes[$optionType] ) ) {
                trigger_error( 'Option type \''. $optionType .'\' is not registered. Please use the \'alch_register_field_type\' filter to do that.', E_USER_WARNING );

                continue;
            }

            $typeSettings = self::get_field_type_settings( $optionType );

            if( ! isset( $typeSettings['options'] ) || ! $typeSettings['options'] ) {
                continue;
            }

            global $wp_filter;

            $htmlFilter = $wp_filter["alch_get_{$optionType}_option_html"];
			$optionDefault = isset( $option['default'] ) ? $option['default'] : '';

            $savedValue = isset( $option['id'] )
                ? alch_admin_get_saved_network_option( alch_modify_alchemy_id( $option['id'] ), $optionDefault )
                : '';

            if( isset( $option['value'] ) ) {
                $savedValue = $option['value'];
            }

            if( isset( $htmlFilter ) && ! empty( $htmlFilter->callbacks ) ) {
                $optionHtml = apply_filters( "alch_get_{$optionType}_option_html", $option, $savedValue, 'network-options' );

                if( ! empty( $optionHtml ) ) {
                    $html .= $optionHtml;
                }
            } else {
                trigger_error( 'No alch_get_'. $optionType . '_option_html callback registered.', E_USER_WARNING );
            }

            if( ! empty( $option['id'] ) ) {
                $parsedOptions[$option['id']] = $option;
            }
        }

        return $html;
    }

    static function get_meta_html( int $postID, array $fields ) : string {
        $html = '';
        $parsedOptions = [];
        $registeredTypes = self::get_registered_field_types();
        $registeredRepeaters = self::get_registered_repeaters();

        foreach ( $fields as $option ) {
            $optionType = $option['type'];

            $repeater = self::get_repeater_id_details( $optionType );

            if( empty( $optionType ) ) {
                trigger_error( 'Option \'type\' is missing. ' . print_r( $option, true ), E_USER_ERROR );
            }

            if( empty( $option['id'] ) && ! in_array( $optionType, self::$okWithoutID ) ) {
                trigger_error( 'Option \'id\' is missing. ' . print_r( $option, true ), E_USER_ERROR );
            }

            if( isset( $option['id'] ) && ! empty( $parsedOptions[$option['id']] ) ) {
                //if it's a section - need to check its children

                trigger_error( sprintf( 'The \'%s\' option ID is already present in this metabox. Please use a different ID.',
                    $option['id']
                ), E_USER_ERROR );
            }

            if( ! empty( $repeater ) ) {
                $optionType = $repeater['type'];

                if( empty( $registeredRepeaters[$repeater['id']] ) ) {
                    trigger_error( 'Repeater type \''. $repeater['id'] .'\' is not registered. Please use the \'alch_repeaters\' filter to do that.', E_USER_WARNING );
                }
            }

            if( empty( $registeredTypes[$optionType] ) ) {
                trigger_error( 'Option type \''. $optionType .'\' is not registered. Please use the \'alch_register_field_type\' filter to do that.', E_USER_WARNING );

                continue;
            }

            $typeSettings = self::get_field_type_settings( $optionType );

            if( ! isset( $typeSettings['metaboxes'] ) || ! $typeSettings['metaboxes'] ) {
                trigger_error( 'Option type \''. $optionType .'\' is not available for meta boxes.', E_USER_WARNING );

                continue;
            }

            global $wp_filter;

            $htmlFilter = $wp_filter["alch_get_{$optionType}_option_html"];
			$optionDefault = isset( $option['default'] ) ? $option['default'] : '';

            $savedValue = isset( $option['id'] )
                ? alch_admin_get_saved_meta( $postID, alch_modify_alchemy_id( $option['id'] ), $optionDefault )
                : '';

            if( isset( $option['value'] ) ) {
                $savedValue = $option['value'];
            }

            if( isset( $htmlFilter ) && ! empty( $htmlFilter->callbacks ) ) {
                $optionHtml = apply_filters( "alch_get_{$optionType}_option_html", $option, $savedValue, 'metabox' );

                if( ! empty( $optionHtml ) ) {
                    $html .= $optionHtml;
                }
            } else {
                trigger_error( 'No alch_get_'. $optionType . '_option_html callback registered.', E_USER_WARNING );
            }

            if( isset( $option['id'] ) ) {
                $parsedOptions[$option['id']] = $option;
            }
        }

        return $html;
    }

    static function get_user_meta_html( int $userID, array $fields ) : string {
        $html = '';
        $parsedOptions = [];
        $registeredTypes = self::get_registered_field_types();
        $registeredRepeaters = self::get_registered_repeaters();

        foreach ( $fields as $option ) {
            $optionType = $option['type'];

            $repeater = self::get_repeater_id_details( $optionType );

            if( empty( $optionType ) ) {
                trigger_error( 'Option \'type\' is missing. ' . print_r( $option, true ), E_USER_ERROR );
            }

            if( empty( $option['id'] ) && ! in_array( $optionType, self::$okWithoutID ) ) {
                trigger_error( 'Option \'id\' is missing. ' . print_r( $option, true ), E_USER_ERROR );
            }

            if( isset( $option['id'] ) && ! empty( $parsedOptions[$option['id']] ) ) {
                //if it's a section - need to check its children

                trigger_error( sprintf( 'The \'%s\' option ID is already present in user fields. Please use a different ID.',
                    $option['id']
                ), E_USER_ERROR );
            }

            if( ! empty( $repeater ) ) {
                $optionType = $repeater['type'];

                if( empty( $registeredRepeaters[$repeater['id']] ) ) {
                    trigger_error( 'Repeater type \''. $repeater['id'] .'\' is not registered. Please use the \'alch_repeaters\' filter to do that.', E_USER_WARNING );
                }
            }

            if( empty( $registeredTypes[$optionType] ) ) {
                trigger_error( 'Option type \''. $optionType .'\' is not registered. Please use the \'alch_register_field_type\' filter to do that.', E_USER_WARNING );

                continue;
            }

            $typeSettings = self::get_field_type_settings( $optionType );

            if( ! isset( $typeSettings['userprofile'] ) || ! $typeSettings['userprofile'] ) {
                trigger_error( 'Option type \''. $optionType .'\' is not available for user fields.', E_USER_WARNING );

                continue;
            }

            global $wp_filter;

            $htmlFilter = $wp_filter["alch_get_{$optionType}_option_html"];
			$optionDefault = isset( $option['default'] ) ? $option['default'] : '';

            $savedValue = isset( $option['id'] )
                ? alch_admin_get_saved_user_meta( alch_modify_alchemy_id( $option['id'] ), $userID, $optionDefault )
                : '';

            if( isset( $option['value'] ) ) {
                $savedValue = $option['value'];
            }

            if( isset( $htmlFilter ) && ! empty( $htmlFilter->callbacks ) ) {
                $optionHtml = apply_filters( "alch_get_{$optionType}_option_html", $option, $savedValue, 'metabox' );

                if( ! empty( $optionHtml ) ) {
                    $html .= $optionHtml;
                }
            } else {
                trigger_error( 'No alch_get_'. $optionType . '_option_html callback registered.', E_USER_WARNING );
            }

            if( isset( $option['id'] ) ) {
                $parsedOptions[$option['id']] = $option;
            }
        }

        return $html;
    }

    static function create_options_pages() : void {
        $pages = apply_filters( 'alch_options_pages', [] );

        if( empty( $pages ) ) {
            return;
        }

        foreach ( $pages as $page ) {
            if( in_array( $page['id'], self::$processedPages ) ) {
                continue;
            }

            $optionsPage = new Includes\Options_Page( $page );
            $optionsPage->add_page();

            self::$optionPages[] = $page;
            self::$processedPages[] = $page['id'];
        }
    }

    static function create_network_options_pages() : void {
        $pages = apply_filters( 'alch_network_options_pages', [] );

        if( empty( $pages ) ) {
            return;
        }

        foreach ( $pages as $page ) {
            if( in_array( $page['id'], self::$processedNetworkPages ) ) {
                continue;
            }

            $optionsPage = new Includes\Options_Page( $page );
            $optionsPage->add_page( 'network' );

            self::$networkOptionPages[] = $page;
            self::$processedNetworkPages[] = $page['id'];
        }
    }

    private function get_user_ID() : int {
        if ( defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
            return get_current_user_id();
        } else if ( ! empty( $_GET['user_id'] ) && is_numeric( $_GET['user_id'] ) ) {
            return $_GET['user_id'];
        }

        return 0;
    }

    private function check_values( array $values ) : array {
        $checks = [];

        foreach ( $values as $passed ) {
            $repeater = Options::get_repeater_id_details( $passed->type );
            $valueType = $repeater ? 'repeater' : $passed->type;

            $check = isset( $passed->value )
                ? apply_filters( "alch_validate_{$valueType}_value", $passed->id, $passed->value )
                : null;

            if( ! empty( $check ) && isset( $check['is_valid'] ) && ! $check['is_valid'] ) {
                $checks[$passed->id] = $check['message'];
            }
        }

        return $checks;
    }

    private function add_metaboxes() : void {
        $metaBoxes = self::get_metaboxes();

        if( empty( $metaBoxes ) ) {
            return;
        }

        foreach( $metaBoxes as $metaBox ) {
            $metaBoxInst = new Includes\Metabox( $metaBox );
            $metaBoxInst->add_metabox();
        }
    }

    private function save_values( array $values ) : bool {
        do_action( 'alchemy_options_before_save', $values );

        $saved = true;

        foreach ( $values as $value ) {
            $valueType = $value->type;

            if( Options::get_repeater_id_details( $valueType ) ) {
                $valueType = 'repeater';
            }

            $sanitisedValue = isset( $value->value )
                ? apply_filters( "alch_sanitize_{$valueType}_value", $value->value )
                : null;

            try {
                update_option( alch_modify_alchemy_id( $value->id ), array(
                    'type' => $value->type,
                    'value' => $sanitisedValue,
                ) );
            } catch ( \Exception $e ) {
                $saved = false;
            }
        }

        do_action( $saved ? 'alchemy_options_saved' : 'alchemy_options_save_failed' );

        return $saved;
    }

    private function save_network_values( array $values ) : bool {
        do_action( 'alchemy_network_options_before_save', $values );

        $saved = true;

        foreach ( $values as $value ) {
            $valueType = $value->type;

            if( Options::get_repeater_id_details( $valueType ) ) {
                $valueType = 'repeater';
            }

            $sanitisedValue = isset( $value->value )
                ? apply_filters( "alch_sanitize_{$valueType}_value", $value->value )
                : null;

            try {
                update_site_option( alch_modify_alchemy_id( $value->id ), array(
                    'type' => $value->type,
                    'value' => $sanitisedValue,
                ) );
            } catch ( \Exception $e ) {
                $saved = false;
            }
        }

        do_action( $saved ? 'alchemy_network_options_saved' : 'alchemy_network_options_save_failed' );

        return $saved;
    }

    private function save_meta_values( int $postID, array $values ) : bool {
        do_action( 'alchemy_metaboxes_before_save', $values );

        $saved = true;

        foreach ( $values as $value ) {
            $valueType = $value->type;

            if( Options::get_repeater_id_details( $valueType ) ) {
                $valueType = 'repeater';
            }

            $sanitisedValue = isset( $value->value )
                ? apply_filters( "alch_sanitize_{$valueType}_value", $value->value )
                : null;

            try {
                update_post_meta( $postID, alch_modify_alchemy_id( $value->id ), array(
                    'type' => $value->type,
                    'value' => wp_slash( $sanitisedValue ) // wp does wp_unslash before saving
                ) );
            } catch ( \Exception $e ) {
                $saved = false;
            }
        }

        do_action( $saved ? 'alchemy_metaboxes_saved' : 'alchemy_metaboxes_save_failed' );

        return $saved;
    }

    private function save_user_profile_values( int $userID, array $values ) : bool {
        do_action( 'alchemy_userprofile_before_save', $values );

        $saved = true;

        foreach ( $values as $value ) {
            $valueType = $value->type;

            if( Options::get_repeater_id_details( $valueType ) ) {
                $valueType = 'repeater';
            }

            $sanitisedValue = isset( $value->value )
                ? apply_filters( "alch_sanitize_{$valueType}_value", $value->value )
                : null;

            try {
                update_user_meta( $userID, alch_modify_alchemy_id( $value->id ), array(
                    'type' => $value->type,
                    'value' => wp_slash( $sanitisedValue ) // wp does wp_unslash before saving
                ) );
            } catch ( \Exception $e ) {
                $saved = false;
            }
        }

        do_action( $saved ? 'alchemy_userprofile_saved' : 'alchemy_userprofile_save_failed' );

        return $saved;
    }

    private static function register_types( array $types ) : void {
        if( empty( $types ) ) {
            self::$registeredTypes = self::$registeredTypes ?? [];

            return;
        }

        foreach ( $types as $type ) {
            if( ! empty( self::$registeredTypes[$type['id']] ) ) {
                trigger_error( sprintf( 'The \'%s\' field type is already registered. Please use a different ID.',
                    $type['id']
                ), E_USER_ERROR );
            } else {
                self::$registeredTypes[$type['id']] = $type;
            }
        }
    }

    private static function register_repeater_types( array $types ) : void {
        if( empty( $types ) ) {
            self::$registeredRepeaters = self::$registeredRepeaters ?? [];

            return;
        }

        foreach ( $types as $type ) {
            if( ! empty( self::$registeredRepeaters[$type['id']] ) ) {
                trigger_error( sprintf( 'The \'%s\' repeater is already registered. Please use a different ID.',
                    $type['id']
                ), E_USER_ERROR );
            } else {
                self::$registeredRepeaters[$type['id']] = $type;
            }
        }
    }
}

add_action( 'init', function() {
    if( wp_doing_ajax() ) {
        return;
    }

    new Options();
}, apply_filters( 'alch_init_priority', 11 ) );
