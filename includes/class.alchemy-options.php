<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( class_exists( 'Alchemy_Options' ) ) {
    return;
}

class Alchemy_Options {
    private $active_tab;

    public function activate() {
        $this->includes();

        /* all of the hooks go here */
        $this->hook_up();
    }

    public function includes() {
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/alchemy-functions.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/interface.alchemyField.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemyDBValue.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemyValue.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemyField.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyText.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyTextarea.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyPassword.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyRadio.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyCheckbox.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemySelect.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyColorpicker.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyDatepicker.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyButtonGroup.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyUpload.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyEditor.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyImageRadio.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyTextblock.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemySlider.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemySections.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyPostTypeSelect.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyTaxonomySelect.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyDatalist.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyFieldGroup.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/fields/class.alchemyRepeater.php' );
        include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemyFieldsLoader.php' );
    }

    public function enqueue_assets() {
        if( ! isset( $_GET[ 'page' ] ) || 'alchemy-options' !== $_GET[ 'page' ] || ! is_admin() ) {
            return;
        }

        wp_register_script( 'select2-scripts', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/vendor/select2/js/select2.min.js', array(), '4.0.3', true );
        wp_register_script( 'alchemy-scripts', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/scripts/alchemy.min.js', $this->get_scripts_deps(), ALCHEMY_OPTIONS_VERSION, true );
        wp_localize_script( 'alchemy-scripts', 'alchemyData', array(
            'adminURL' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'alchemy_ajax_nonce' )
        ) );

        wp_register_style( 'alchemy-jquery', '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', array(), '1.12.1' );
        wp_register_style( 'select2-style', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/vendor/select2/css/select2.min.css', array(), '4.0.3' );
        wp_register_style( 'alchemy-styles', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/styles/alchemy.css', array( 'alchemy-jquery', 'select2-style' ), ALCHEMY_OPTIONS_VERSION );

        wp_enqueue_media();
        wp_enqueue_script( 'alchemy-scripts' );

        wp_enqueue_style( 'alchemy-styles' );
    }

    public function register_client_assets() {
        wp_register_script( 'alchemy-options-client-scripts', ALCHEMY_OPTIONS_PLUGIN_DIR_URL . 'assets/scripts/alchemy-client.min.js', array(), ALCHEMY_OPTIONS_VERSION, true );

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

        if( isset( $savedOptions['options'] ) && is_array( $savedOptions['options'] ) && count( $savedOptions['options'] ) > 0 ) {
            $types = array_unique( alchemy_array_flatten( $this->walk_the_fields( $savedOptions['options'] ) ) );

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

    public function walk_the_fields( $fields ) {
        $types = [];

        foreach ( $fields as $field ) {
            $types[] = $field['type'];

            if( 'repeater' === $field['type'] ) {
                if( $field['repeatees'] ) {
                    foreach( $field['repeatees'] as $repeatee ) {
                        $types[] = $this->walk_the_fields( $repeatee['fields'] );
                    }
                }
            }

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

        add_action( 'admin_menu', array( $this, 'create_options_submenu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_client_assets' ) );
        add_action( 'wp_ajax_alchemy_options_save_options', array( $this, 'handle_save_options' ) );
        add_action( 'wp_ajax_alchemy_options_repeater_item_add', array( $this, 'handle_repeater_item_add' ) );
        add_action( 'wp_ajax_alchemy_options_post_type_selection', array( $this, 'handle_post_type_selection' ) );
        add_action( 'wp_ajax_alchemy_options_client_request', array( $this, 'handle_client_requests' ) );
        add_action( 'wp_ajax_nopriv_alchemy_options_client_request', array( $this, 'handle_client_requests' ) );
    }

    public function handle_save_options() {
        if ( ! isset( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( $_POST[ 'nonce' ], 'alchemy_ajax_nonce' ) ) {
            die();
        }

        $fields = $_POST[ 'fields' ];

        if( ! $fields ) {
            return;
        }

        $networkSave = isset( $_POST[ 'network' ] );

        if( count( $fields ) > 0 ) {
            try {
                foreach ( $fields as $id => $payload ) {
                    $value = new Alchemy_DB_Value( $payload );

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
                }

                wp_send_json_success( __( 'Options saved', 'alchemy-options' ) );
            } catch( Exception $err ) {
                wp_send_json_error( $err->getMessage() );
            }
        }
    }

    public function handle_client_requests() {
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'alchemy_client_ajax_nonce' ) ) {
	        wp_send_json_error( __( 'Nonce check failed', 'alchemy-options' ) );
        }

        $isNetworkCall = isset( $_GET['network'] ) && $_GET['network'] === 'true';

        $savedVal = $isNetworkCall
	        ? alch_get_network_option( $_GET['id'] )
	        : alch_get_option( $_GET['id'] );

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

    public function handle_datalist_search() {
        if ( ! isset( $_GET[ 'nonce' ] ) || ! is_array( $_GET[ 'nonce' ] ) || ! wp_verify_nonce( $_GET[ 'nonce' ][ 'value' ], $_GET[ 'nonce' ][ 'id' ] ) ) {
            die();
        }

        $fields = $_GET[ 'search-string' ];

        if( ! $fields ) {
            return;
        }
    }

    public function handle_repeater_item_add() {
        if ( ! isset( $_GET[ 'nonce' ] ) || ! wp_verify_nonce( $_GET[ 'nonce' ][1], $_GET[ 'nonce' ][0] ) ) {
            die();
        }

        $rID = $_GET[ 'repeater' ]['id'];
        $repeaterData = $_GET[ 'repeater' ]['repeater'];
        $index = $_GET[ 'index' ];

        $repeater = new Alchemy_Repeater_Field();

        $repeaterHTML = $repeater->generate_repeatee( array(
            'id' => $rID,
            'repeater' => $repeaterData,
            'index' => $index
        ) );

        wp_send_json( $repeaterHTML );
    }

    public function create_network_options_page() {
        add_submenu_page(
            'themes.php',
            __( 'Alchemy Network Options', 'alchemy-options' ),
            __( 'Alchemy Network Options', 'alchemy-options' ),
            'manage_options',
            'alchemy-options',
            array( $this, 'render_network_options_submenu' )
        );
    }

    public function create_options_submenu_page() {
        add_submenu_page(
            'themes.php',
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

        if( is_array( $options[ 'tabs' ] ) && count( $options[ 'tabs' ] ) > 0 ) {
            $hasTabs = true;

            reset( $options[ 'tabs' ] );
            $this->active_tab = key( $options[ 'tabs' ] );

            if( isset( $_GET[ 'tab' ] ) ) {
                $this->active_tab = $_GET[ 'tab' ];
            }

            $tabsSettings = $options[ 'tabs' ];

            $optionsHTML .= $this->get_tabs_html( $tabsSettings );
        }

        if( is_array( $options[ 'options' ] ) && count( $options[ 'options' ] ) > 0 ) {
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

        $optionFields = new Alchemy_Fields_Loader( $isNetwork );

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