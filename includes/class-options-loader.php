<?php

/**
 * @package Alchemy_Options\Includes
 *
 */

namespace Alchemy_Options\Includes;

use WP_Query;
use Exception;

//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Options_Loader' ) ) {
    return;
}

class Options_Loader {
    private $active_tab;
    private $parentPage = 'themes.php';
    private $githubVersion;

    public function activate() {
        include_once( ALCHEMY_OPTIONS_DIR . 'includes/alchemy-functions.php' );

        /* all of the hooks go here */
        $this->hook_up();
    }

    public function check_for_updates() {
        $latestGithubVersion = wp_remote_get( 'https://raw.githubusercontent.com/AlchemyOptions/AlchemyOptions/master/dist/VERSION' );

        if( ! is_wp_error( $latestGithubVersion ) ) {
            $this->githubVersion = $latestGithubVersion['body'];

            if ( version_compare( ALCHEMY_OPTIONS_VERSION, $this->githubVersion, '<' ) ) {
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_notification_script' ) );
                add_action( 'admin_notices', array( $this, 'add_update_notification' ) );
                add_action( 'network_admin_notices', array( $this, 'add_update_notification' ) );
            }
        }
    }

    public function add_update_notification() {
        echo sprintf(
            '<div class="notice notice-info jsAlchemyOptionsNotification"><p>%1$s</p><p><button type="button" class="button-secondary jsButton" data-type="dismiss">%2$s</button> <button type="button" class="button-secondary jsButton" data-type="hide" title="%4$s">%3$s</button></p></div>',
            sprintf(
                __( 'The latest version of Alchemy Options available on GitHub is %1$s. This site uses version %2$s. Follow <a href="%3$s" target="_blank">installation instructions</a> if you want to update it, or press any of the buttons to hide this message.', 'alchemy-options' ),
                $this->githubVersion,
                ALCHEMY_OPTIONS_VERSION,
                'https://docs.alchemy-options.com/Installation.html'
            ),
            __( 'Remind me tomorrow', 'alchemy-options' ),
            __( 'Never show this message', 'alchemy-options' ),
            __( 'I mean... like a year or so :)', 'alchemy-options' )
        );
    }

    public function enqueue_notification_script() {
        wp_enqueue_script( 'alchemy-options-notification-script', ALCHEMY_OPTIONS_DIR_URL . 'assets/scripts/alchemy-notification-script.js', array('jquery'), ALCHEMY_OPTIONS_VERSION, true );
    }

    public function enqueue_assets() {
        if( ! isset( $_GET[ 'page' ] ) || 'alchemy-options' !== $_GET[ 'page' ] || ! is_admin() ) {
            return;
        }

        wp_register_script( 'select2-scripts', ALCHEMY_OPTIONS_DIR_URL . 'assets/vendor/select2/js/select2.min.js', array(), '4.0.3', true );
        wp_register_script( 'alchemy-scripts', ALCHEMY_OPTIONS_DIR_URL . 'assets/scripts/alchemy.min.js', $this->get_scripts_deps(), ALCHEMY_OPTIONS_VERSION, true );
        wp_localize_script( 'alchemy-scripts', 'alchemyData', array(
            'adminURL' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alchemy_ajax_nonce' )
        ) );

        wp_register_style( 'alchemy-jquery', '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', array(), '1.12.1' );
        wp_register_style( 'select2-style', ALCHEMY_OPTIONS_DIR_URL . 'assets/vendor/select2/css/select2.min.css', array(), '4.0.3' );
        wp_register_style( 'alchemy-styles', ALCHEMY_OPTIONS_DIR_URL . 'assets/styles/alchemy.css', array( 'alchemy-jquery', 'select2-style' ), ALCHEMY_OPTIONS_VERSION );

        wp_enqueue_media();
        wp_enqueue_script( 'alchemy-scripts' );

        wp_enqueue_style( 'alchemy-styles' );
    }

    public function register_client_assets() {
        wp_register_script( 'alchemy-options-client-scripts', ALCHEMY_OPTIONS_DIR_URL . 'assets/scripts/alchemy-client.min.js', array(), ALCHEMY_OPTIONS_VERSION, true );

        wp_localize_script( 'alchemy-options-client-scripts', 'alchemyOptionsClientData', array(
            'adminURL' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alchemy_client_ajax_nonce' )
        ) );
    }

    public function get_scripts_deps() {
        $type = is_network_admin() ? alch_network_options_id() : alch_options_id();
        $deps = array(
            'jquery'
        );

        $savedOptions = get_option( $type, array() );

        if( isset( $savedOptions['options'] ) && alch_is_not_empty_array( $savedOptions['options'] ) ) {
            $hasRepeaters = array_filter($savedOptions['options'], function( $option ) {
                return strpos( $option['type'], 'repeater:' ) === 0;
            });
            $repeaters = array();

            if( alch_is_not_empty_array( $hasRepeaters ) ) {
                $repeatersIDs = array_map(function($repeater){
                    return explode( ':', $repeater['type'] )[1];
                }, $hasRepeaters);

                $savedRepeaters = get_option( alch_repeaters_id(), array() ) ;

                $repeaters = array_filter( $savedRepeaters, function( $repeater ) use ( $repeatersIDs ) {
                    return in_array( $repeater['id'], $repeatersIDs );
                } );
            }

            $types = array_unique( alchemy_array_flatten( $this->walk_the_fields( $savedOptions['options'], $repeaters ) ) );

            if( in_array( 'colorpicker', $types ) ) {
                $deps[] = 'iris';
            }

            if( in_array( 'slider', $types ) ) {
                $deps[] = 'jquery-ui-slider';
            }

            if( in_array( 'datepicker', $types ) ) {
                $deps[] = 'jquery-ui-datepicker';
            }

            if( in_array( 'repeater', $types ) ) {
                $deps[] = 'jquery-ui-sortable';
            }

            if( in_array( 'datalist', $types ) || in_array( 'post-type-select', $types ) || in_array( 'taxonomy-select', $types ) ) {
                $deps[] = 'select2-scripts';
            }
        }

        return $deps;
    }

    public function walk_the_fields( $fields, $repeaters = array() ) {
        $types = [];

        if( count( $repeaters ) > 0 ) {
            foreach ( $repeaters as $repeater ) {
                if( isset( $repeater['fields'] ) ) {
                    if( alch_is_not_empty_array( $repeater['fields'] ) ) {
                        foreach ( $repeater['fields'] as $field ) {
                            array_push( $fields, $field );
                        }
                    }
                } else if( isset( $repeater['field-types'] ) ) {
                    if( alch_is_not_empty_array( $repeater['field-types'] ) ) {
                        foreach ( $repeater['field-types'] as $fieldType ) {
                            if( isset( $fieldType['fields'] ) && alch_is_not_empty_array( $fieldType['fields'] ) ) {
                                foreach ( $fieldType['fields'] as $field ) {
                                    array_push( $fields, $field );
                                }
                            }
                        }
                    }
                }
            }

            $types[] = 'repeater';
        }

        foreach ( $fields as $field ) {
            $types[] = $field['type'];

            if( 'sections' === $field['type'] ) {
                foreach( $field['sections'] as $section ) {
                    $types[] = $this->walk_the_fields( $section['options'] );
                }
            }

            if( 'field-group' === $field['type'] ) {
                $types[] = $this->walk_the_fields( $field['fields'] );
            }
        }

        return $types;
    }

    public function hook_up() {
        if( is_multisite() ) {
            add_action( 'network_admin_menu', array( $this, 'create_network_options_page' ) );
        }

        if( defined( 'ALCHEMY_OPTIONS_PLUGIN_BASENAME' ) ) {
            add_filter( 'plugin_action_links_' . ALCHEMY_OPTIONS_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );
        }

        add_action( 'admin_menu', array( $this, 'create_options_submenu_page' ) );
        add_action( 'admin_bar_menu', array( $this, 'update_adminbar' ), 999 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_client_assets' ) );
        add_action( 'wp_ajax_alchemy_options_save_options', array( $this, 'handle_save_options' ) );
        add_action( 'wp_ajax_alchemy_options_repeater_item_add', array( $this, 'handle_repeater_item_add' ) );
        add_action( 'wp_ajax_alchemy_options_post_type_selection', array( $this, 'handle_post_type_selection' ) );
        add_action( 'wp_ajax_alchemy_options_client_request', array( $this, 'handle_client_requests' ) );
        add_action( 'wp_ajax_nopriv_alchemy_options_client_request', array( $this, 'handle_client_requests' ) );
    }

    public function add_plugin_action_links( $links ) {
        $links[] = sprintf( '<a target="_blank" href="https://docs.alchemy-options.com">%s</a>', __( 'Documentation', 'alchemy-options' ) );

        return $links;
    }

    public function update_adminbar( $wp_adminbar ) {
        if( ! current_user_can( 'edit_theme_options' ) || ! is_admin_bar_showing() ) {
            return;
        }

        if( is_multisite() ) {
            $this->create_adminbar_link(
                $wp_adminbar,
                'network-admin',
                network_admin_url( sprintf( '%s?page=alchemy-options', $this->parentPage ) )
            );

            $blogs = get_blogs_of_user( get_current_user_id() );

            if( count( $blogs ) > 0 ) {
                foreach( $blogs as $blog ) {
                    $this->create_adminbar_link(
                        $wp_adminbar,
                        sprintf( 'blog-%s', $blog->userblog_id ),
                        get_admin_url( $blog->userblog_id, sprintf( '%s?page=alchemy-options', $this->parentPage ) )
                    );
                }
            }
        }

        $this->create_adminbar_link(
            $wp_adminbar,
            'site-name',
            admin_url( sprintf( '%s?page=alchemy-options', $this->parentPage ) )
        );
    }

    public function create_adminbar_link( $wp_adminbar, $parent, $href ) {
        $wp_adminbar->add_node( array(
            'id' => sprintf( '%s-alchemy-options', $parent ),
            'title' => __( 'Alchemy Options', 'alchemy-options' ),
            'parent' => $parent,
            'href' => $href,
        ));
    }

    public function handle_save_options() {
        if ( ! isset( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( $_POST[ 'nonce' ], 'alchemy_ajax_nonce' ) ) {
            wp_die( 'Failed to check the nonce' );
        }

        $fields = $_POST[ 'fields' ];

        if( ! $fields ) {
            return;
        }

        $networkSave = isset( $_POST[ 'network' ] );

        if( count( $fields ) > 0 ) {
            $saved = $this->save_options( $fields, $networkSave );

            if( $saved ) {
                wp_send_json_success( __( 'Options saved', 'alchemy-options' ) );
            } else {
                wp_send_json_error( __( 'Some error happened', 'alchemy-options' ) );
            }
        }
    }

    public function save_options( $fields, $networkSave ) {
        $saved = true;

        foreach ( $fields as $id => $payload ) {
            $value = new Database_Value( $payload );

            try {
                if( $networkSave ) {
                    update_site_option( $id, array(
                        'type' => $payload[ 'type' ],
                        'value' => $value->get_safe_value(),
                    ) );
                } else {
                    update_option( $id, array(
                        'type' => $payload[ 'type' ],
                        'value' => $value->get_safe_value(),
                    ) );
                }
            } catch ( Exception $e ) {
                $saved = false;
            }
        }

        do_action( $saved ? 'alchemy_options_saved' : 'alchemy_options_save_failed' );

        return $saved;
    }

    public function handle_client_requests() {
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'alchemy_client_ajax_nonce' ) ) {
	        wp_send_json_error( __( 'Nonce check failed', 'alchemy-options' ) );
        }

        if ( ! isset( $_GET['type'] ) ) {
            wp_send_json_error();
        }

        $savedVal = "";

        switch( $_GET['type'] ) {
            case 'getPostMeta' :
                $savedVal = alch_get_post_meta( $_GET['postID'], $_GET['metaID'] );
            break;
            case 'getNetworkOption' :
                $savedVal = alch_get_network_option( $_GET['id'] );
            break;
            case 'getOption' :
                $savedVal = alch_get_option( $_GET['id'] );
            break;
            default : break;
        }

        wp_send_json_success( $savedVal );
    }

    public function handle_post_type_selection() {
        if ( ! isset( $_GET[ 'nonce' ] ) || ! wp_verify_nonce( $_GET[ 'nonce' ][1], $_GET[ 'nonce' ][0] ) ) {
            die();
        }

        $the_query = new WP_Query( array(
            's' => $_GET['searchedFor'],
            'post_type' => $_GET['post-type'],
            'post_status' => 'publish'
        ) );

        $found_posts = [];

        if ( $the_query->have_posts() ) {
            $found_posts = $the_query->get_posts();
        }

        $result = array_map(function( $post ) {
            return array(
                'text' => $post->post_title,
                'id' => $post->ID
            );
        }, $found_posts);

        wp_send_json_success( $result );
    }

    public function handle_repeater_item_add() {
        if ( ! isset( $_GET[ 'nonce' ] ) || ! wp_verify_nonce( $_GET[ 'nonce' ][1], $_GET[ 'nonce' ][0] ) ) {
            die();
        }

        $rID = $_GET[ 'repeater' ]['id'];
        $repeaterData = $_GET[ 'repeater' ]['repeater'];
        $index = $_GET[ 'index' ];
        $networkSave = isset( $_GET['network'] ) && $_GET['network'] !== 'false';

        $repeateeSettings = array(
            'id' => $rID,
            'repeater' => $repeaterData,
            'index' => $index
        );

        if( isset( $_GET['value'] ) ) {
            $repeateeSettings['isVisible'] = $_GET['value']['isVisible'];
            $repeateeSettings['savedFields'] = array_map( function( $field ) {
                $value = new Database_Value( $field );

                return array(
                    'type' => $field[ 'type' ],
                    'value' => $value->get_safe_value(),
                );
            }, $_GET['value']['fields'] );

            if( isset( $_GET['value']['typeID'] ) ) {
                $repeateeSettings['repeater']['type-id'] = $_GET['value']['typeID'];
            }
        }

        $repeater = new Fields\Repeater( $networkSave );

        wp_send_json( $repeater->generate_repeatee( $repeateeSettings ) );
    }

    public function create_network_options_page() {
        add_submenu_page(
            $this->parentPage,
            __( 'Alchemy Network Options', 'alchemy-options' ),
            __( 'Alchemy Network Options', 'alchemy-options' ),
            'manage_options',
            'alchemy-options',
            array( $this, 'render_network_options_submenu' )
        );
    }

    public function create_options_submenu_page() {
        add_submenu_page(
            $this->parentPage,
            __( 'Alchemy Options', 'alchemy-options' ),
            __( 'Alchemy Options', 'alchemy-options' ),
            'manage_options',
            'alchemy-options',
            array( $this, 'render_options_submenu' )
        );
    }

    public function render_options_submenu () {
        echo $this->get_options_page( alch_options_id(), __( 'Alchemy Options', 'alchemy-options' ) );

        //hack to include editor assets. Will be removed when support of the wp_enqueue_editor() is high and there's a way to get the default editor settings for posts
        echo '<div class="hidden">';
            wp_editor( '', 'alchemy-temp-editor' );
        echo '</div>';
    }

    public function render_network_options_submenu (  ) {
        echo $this->get_options_page( alch_network_options_id(), __( 'Alchemy Network Options', 'alchemy-options' ), true );

        //hack to include editor assets. Will be removed when support of the wp_enqueue_editor() is high and there's a way to get the default editor settings for posts
        echo '<div class="hidden">';
        wp_editor( '', 'alchemy-temp-editor' );
        echo '</div>';
    }

    public function get_options_page( $type, $pageTitle, $isNetwork = false ) {
        $savedOptions = get_option( $type, array() );

        $submenuHTML = '';

        $submenuHTML .= '<div class="wrap alchemy">';
        $submenuHTML .= '<h2>' . $pageTitle . ' <small class="alchemy__version">(v. ' . ALCHEMY_OPTIONS_VERSION . ')</small></h2><br>';

        if( count( $savedOptions ) > 0 ) {
            $submenuHTML .= $this->get_options_page_html( $savedOptions, $isNetwork );
        } else {
            $submenuHTML .= '<p>' . __( 'Looks like there are no options to show', 'alchemy-options' ) . '</p>';
        }

        $submenuHTML .= '</div>';

        return $submenuHTML;
    }

    public function get_options_page_html( $options, $isNetwork ) {
        $optionsHTML = "";
        $hasTabs = false;

        if( isset( $options['tabs'] ) && alch_is_not_empty_array( $options['tabs'] ) ) {
            $hasTabs = true;

            reset( $options[ 'tabs' ] );
            $this->active_tab = key( $options[ 'tabs' ] );

            if( isset( $_GET[ 'tab' ] ) ) {
                $this->active_tab = $_GET[ 'tab' ];
            }

            $tabsSettings = $options[ 'tabs' ];

            $optionsHTML .= $this->get_tabs_html( $tabsSettings );
        }

        if( isset( $options['options'] ) && alch_is_not_empty_array( $options['options'] ) ) {
            $optionsHTML .= '<div class="alchemy__options">';

            $optionsHTML .= $this->get_options_html( $options[ 'options' ], $hasTabs, $isNetwork );

            $optionsHTML .= sprintf(
                '<div class="alchemy__modal jsAlchemyModal"><p class="alchemy__modal--success">%s <span class="dashicons dashicons-info"></span></p><p class="alchemy__modal--error">%s <span class="dashicons dashicons-warning"></span></p></div>',
                __( 'Options saved', 'alchemy-options' ),
                __( 'Error. Options not saved', 'alchemy-options' )
            );
            $optionsHTML .= '</div>';
        }

        return $optionsHTML;
    }

    public function get_tabs_html( $tabs ) {
        $tabsHTML = "";

        $tabsHTML .= '<div class="nav-tab-wrapper">';

        foreach ( $tabs as $tabID => $tabDetails ) {
            $tabsHTML .= '<a class="nav-tab' . ( $this->active_tab == $tabID ? " nav-tab-active" : "" ) . '" href="?page=alchemy-options&tab=' . esc_attr( $tabID ) . '">' . $tabDetails[ 'title' ] . '</a>';
        }

        $tabsHTML .= "</div>";

        return $tabsHTML;
    }

    public function get_options_html( $options, $hasTabs, $isNetwork ) {
        $optionsHTML = "";

        $filteredOptions = $hasTabs
            ? array_filter( $options, function( $option ) {
                return !isset( $option[ 'tab' ] ) || $option[ 'tab' ] === $this->active_tab;
            } )
            : $options;

        $optionFields = new Fields_Loader( $isNetwork );

        $optionsHTML .= '<form action="?page=alchemy-options&action=save-alchemy-options" id="jsAlchemyForm" data-is-network="' . json_encode( $isNetwork ) . '">';
        $optionsHTML .= '<button type="submit" class="alchemy__btn alchemy__btn--submit button button-primary">' . __( 'Save options', 'alchemy-options' ) . '</button><img src="' . get_site_url() . '/wp-includes/images/spinner-2x.gif' . '" class="alchemy__spinner alchemy__spinner--hidden jsAlchemyLoader" width="20" height="20" />';

        $optionsHTML .= '<div class="alchemy__fields">';
        $optionsHTML .= $optionFields->get_fields_html( $filteredOptions );
        $optionsHTML .= '</div>';

        $optionsHTML .= '<button type="submit" class="alchemy__btn alchemy__btn--submit button button-primary">' . __( 'Save options', 'alchemy-options' ) . '</button><img src="' . get_site_url() . '/wp-includes/images/spinner-2x.gif' . '" class="alchemy__spinner alchemy__spinner--hidden jsAlchemyLoader" width="20" height="20" />';
        $optionsHTML .= '</form>';

        return $optionsHTML;
    }
}