<?php

namespace Alchemy\Includes;

use Alchemy\Options;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Options_Page' ) ) {
    return;
}

class Options_Page {
    private array $page;
    private array $subpages = [];

    function __construct( array $data ) {
        $this->page = $data;

        if( ! $this->is_valid_page() ) {
            trigger_error( 'Page \'id\' is missing. ' . print_r( $this->page, true ), E_USER_ERROR );
        }
    }

    function add_page( string $type = '' ) : void {
        $callBack = 'create_options_page';

        if( 'network' === $type ) {
            $callBack = 'create_network_options_page';
        }

        add_menu_page(
            $this->get_page_title(),
            $this->get_page_name(),
            $this->get_page_capabilities( $this->page['id'] ),
            $this->page['id'],
            array( $this, $callBack ),
            $this->get_page_icon(),
            $this->get_page_position()
        );

        if( ! empty( $this->page['subpages'] ) ) {
            foreach ( $this->page['subpages'] as $subpage ) {
                if( in_array( $subpage['id'], $this->subpages ) ) {
                    trigger_error( 'This subpage ID ('. $subpage['id'] . ') is already registered.', E_USER_WARNING );

                    continue;
                }

                $subpageInst = new self( $subpage );

                add_submenu_page(
                    $this->page['id'],
                    $subpageInst->get_page_title(),
                    $subpageInst->get_page_name(),
                    $subpageInst->get_page_capabilities( $subpage['id'] ),
                    $subpage['id'],
                    $subpage['id'] === $this->page['id'] ? '' : array( $subpageInst, $callBack ),
                    null
                );

                $this->subpages[] = $subpage['id'];
            }
        }
    }

    function create_options_page() : void {
        add_action( 'admin_footer_text', array( $this, 'edit_admin_footer_text' ) );

        $filteredOptions = $this->filter_options( Options::get_options() );

        echo '<div class="wrap">';

        printf( '<h1>%s</h1><br>', $this->get_page_name() );

        echo '<div class="alchemy jsAlchemy">';

        do_action( 'alch_output_before_options_tabs' );
        do_action( 'alch_' . $this->page['id'] . '_output_before_options_tabs' );

        echo $this->get_tabs_html( $this->page );

        if( empty( $filteredOptions ) ) {
            $noOptionsDefaultHTML = sprintf( '<p>%s</p>',
                __( 'Looks like there are no options to show.', 'alchemy' )
            );

            $noOptionsFilteredHTML = apply_filters( 'alch_output_no_options', $noOptionsDefaultHTML );
            $noOptionsHTML = apply_filters( 'alch_' . $this->page['id'] . '_output_no_options', $noOptionsFilteredHTML );

            echo $noOptionsHTML;

            echo '</div></div>'; // need to close .wrap and .alchemy earlier

            return;
        }

        do_action( 'alch_output_before_options' );
        do_action( 'alch_' . $this->page['id'] . '_output_before_options' );

        printf( '<button type="button" class="button button-primary jsAlchemySaveOptions">%1$s</button><img src="%2$s" class="alchemy__spinner alchemy__spinner--hidden jsAlchemyLoader" width="20" height="20" />',
            __( 'Save options', 'alchemy' ),
            get_site_url() . '/wp-includes/images/spinner-2x.gif'
        );

        printf( '<div class="alchemy__items jsAlchemyOptions">%s</div>',
            Options::get_options_html( $filteredOptions )
        );

        do_action( 'alch_output_after_options' );
        do_action( 'alch_' . $this->page['id'] . '_output_after_options' );

        printf( '<button type="button" class="button button-primary jsAlchemySaveOptions">%1$s</button>%2$s',
            __( 'Save options', 'alchemy' ),
            '<span class="alchemy__spinner alchemy__spinner--hidden alchemy__spinner--alch jsAlchemyLoader"><svg xmlns="http://www.w3.org/2000/svg" style="fill:#808080" viewBox="0 0 32.88 34.34"><path d="M31.28,23.94A15.82,15.82,0,0,0,19.22,3a1.93,1.93,0,0,0,0-.24,2.76,2.76,0,0,0-5.52,0c0,.08,0,.16,0,.23A15.79,15.79,0,0,0,1.56,23.86a2.76,2.76,0,1,0,2.73,4.78,15.78,15.78,0,0,0,24.23.06,2.73,2.73,0,0,0,1.59.5,2.77,2.77,0,0,0,2.76-2.76A2.79,2.79,0,0,0,31.28,23.94ZM25.21,9.77a12.3,12.3,0,0,1,3.64,8.78,12.56,12.56,0,0,1-.78,4.36L18.48,6.3A12.27,12.27,0,0,1,25.21,9.77Zm-.94,13.3H8.6L16.43,9.5ZM7.65,9.77A12.33,12.33,0,0,1,14.39,6.3L4.8,22.91A12.56,12.56,0,0,1,4,18.55,12.34,12.34,0,0,1,7.65,9.77ZM16.43,31a12.37,12.37,0,0,1-8.78-3.63c-.28-.29-.55-.58-.8-.89H26a11.22,11.22,0,0,1-.81.89A12.34,12.34,0,0,1,16.43,31Z"/></svg></span>'
        );

        do_action( 'alch_output_after_save_button' );
        do_action( 'alch_' . $this->page['id'] . '_output_after_save_button' );

        echo '</div>';
        echo '</div>';
    }

    function create_network_options_page() : void {
        add_action( 'admin_footer_text', array( $this, 'edit_admin_footer_text' ) );

        $filteredOptions = $this->filter_options( Options::get_network_options() );

        echo '<div class="wrap">';

        printf( '<h1>%s</h1><br>', $this->get_page_name() );

        echo '<div class="alchemy jsAlchemy">';

        do_action( 'alch_output_before_options_tabs' );
        do_action( 'alch_' . $this->page['id'] . '_output_before_options_tabs' );

        echo $this->get_tabs_html( $this->page );

        if( empty( $filteredOptions ) ) {
            $noOptionsDefaultHTML = sprintf( '<p>%s</p>',
                __( 'Looks like there are no options to show.', 'alchemy' )
            );

            $noOptionsFilteredHTML = apply_filters( 'alch_output_no_options', $noOptionsDefaultHTML );
            $noOptionsHTML = apply_filters( 'alch_' . $this->page['id'] . '_output_no_options', $noOptionsFilteredHTML );

            echo $noOptionsHTML;

            echo '</div></div>'; // need to close .wrap and .alchemy earlier

            return;
        }

        do_action( 'alch_output_before_options' );
        do_action( 'alch_' . $this->page['id'] . '_output_before_options' );

        printf( '<button type="button" class="button button-primary jsAlchemySaveOptions">%1$s</button><img src="%2$s" class="alchemy__spinner alchemy__spinner--hidden jsAlchemyLoader" width="20" height="20" />',
            __( 'Save options', 'alchemy' ),
            get_site_url() . '/wp-includes/images/spinner-2x.gif'
        );

        printf( '<div class="alchemy__items jsAlchemyOptions">%s</div>',
            Options::get_network_options_html( $filteredOptions )
        );

        do_action( 'alch_output_after_options' );
        do_action( 'alch_' . $this->page['id'] . '_output_after_options' );

        printf( '<button type="button" class="button button-primary jsAlchemySaveOptions" data-type="network">%1$s</button>%2$s',
            __( 'Save options', 'alchemy' ),
            '<span class="alchemy__spinner alchemy__spinner--hidden alchemy__spinner--alch jsAlchemyLoader"><svg xmlns="http://www.w3.org/2000/svg" style="fill:#808080" viewBox="0 0 32.88 34.34"><path d="M31.28,23.94A15.82,15.82,0,0,0,19.22,3a1.93,1.93,0,0,0,0-.24,2.76,2.76,0,0,0-5.52,0c0,.08,0,.16,0,.23A15.79,15.79,0,0,0,1.56,23.86a2.76,2.76,0,1,0,2.73,4.78,15.78,15.78,0,0,0,24.23.06,2.73,2.73,0,0,0,1.59.5,2.77,2.77,0,0,0,2.76-2.76A2.79,2.79,0,0,0,31.28,23.94ZM25.21,9.77a12.3,12.3,0,0,1,3.64,8.78,12.56,12.56,0,0,1-.78,4.36L18.48,6.3A12.27,12.27,0,0,1,25.21,9.77Zm-.94,13.3H8.6L16.43,9.5ZM7.65,9.77A12.33,12.33,0,0,1,14.39,6.3L4.8,22.91A12.56,12.56,0,0,1,4,18.55,12.34,12.34,0,0,1,7.65,9.77ZM16.43,31a12.37,12.37,0,0,1-8.78-3.63c-.28-.29-.55-.58-.8-.89H26a11.22,11.22,0,0,1-.81.89A12.34,12.34,0,0,1,16.43,31Z"/></svg></span>'
        );

        do_action( 'alch_output_after_save_button' );
        do_action( 'alch_' . $this->page['id'] . '_output_after_save_button' );

        echo '</div>';
        echo '</div>';
    }

    function edit_admin_footer_text( string $text ) : string {
        return str_replace( '.</span>', ' and <a href="https://docs.alchemy-options.com">Alchemy Options</a>.</span>', $text );
    }

    static function get_page_capabilities( string $id ) : string {
        return apply_filters(
            "alch_{$id}_capabilities",
            apply_filters( 'alch_default_page_capabilities', 'manage_options' )
        );
    }

    protected function filter_options( array $options ) : array {
        $hasTabs = ! empty( $this->page['tabs'] );
        $activeTab = '';

        if( $hasTabs ) {
            $activeTab = $this->get_active_tab_id( $this->page['tabs'] );
        }

        return array_filter( $options, function( $option ) use ( $hasTabs, $activeTab ) {
            if( ! empty( $option['place']['tab'] ) && $hasTabs ) {
                return ( $option['place']['page'] === $this->page['id'] ) && ( $option['place']['tab'] === $activeTab );
            }

            return $option['place']['page'] === $this->page['id'];
        } );
    }

    protected function is_valid_page() : bool {
        return isset( $this->page['id'] );
    }

    protected function get_page_title() : string {
        return empty( $this->page['name'] )
            ? 'Alchemy Options'
            : sprintf( '%s | Alchemy Options', $this->page['name'] );
    }

    protected function get_page_position() : int {
        return apply_filters(
            "alch_{$this->page['id']}_position",
            apply_filters( 'alch_default_page_position', 60 )
        );
    }

    protected function get_page_icon() : string {
        return apply_filters(
            "alch_{$this->page['id']}_icon",
            apply_filters( 'alch_default_page_icon', 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHN0eWxlPSJmaWxsOiM4MDgwODAiIHZpZXdCb3g9IjAgMCAyMCAyMCI+PHBhdGggZD0iTTE4LjQgMTMuOGMuMy0xIC41LTIgLjUtMy4xIDAtNC40LTMuMi04LjEtNy40LTguOHYtLjFjMC0uOS0uNy0xLjYtMS42LTEuNi0uNy4xLTEuNC44LTEuNCAxLjZ2LjFDNC4zIDIuNyAxIDYuNCAxIDEwLjhjMCAxLjEuMiAyLjEuNSAzLS41LjItLjguOC0uOCAxLjQgMCAuOS43IDEuNiAxLjYgMS42LjMgMCAuNi0uMS45LS4zIDEuNiAyIDQuMSAzLjIgNi45IDMuMiAyLjggMCA1LjItMS4yIDYuOS0zLjIuMy4yLjYuMy45LjMuOSAwIDEuNi0uNyAxLjYtMS42LS4yLS42LS42LTEuMS0xLjEtMS40em0tMy40LThjMS4zIDEuMyAyLjEgMy4xIDIuMSA1IDAgLjktLjIgMS43LS40IDIuNWwtNS40LTkuNGMxLjMuMiAyLjYuOSAzLjcgMS45em0tLjYgNy41SDUuNkwxMCA1LjdsNC40IDcuNnpNNSA1LjhjMS4xLTEuMSAyLjQtMS43IDMuOC0ybC01LjQgOS40Yy0uMy0uNy0uNC0xLjYtLjQtMi40IDAtMS45LjctMy43IDItNXptNSAxMmMtMS45IDAtMy42LS43LTUtMi4xbC0uNS0uNWgxMC45Yy0uMS4yLS4zLjMtLjUuNS0xLjMgMS40LTMgMi4xLTQuOSAyLjF6Ii8+PC9zdmc+' )
        );
    }

    protected function get_page_name() : string {
        return empty( $this->page['name'] ) ? 'Alchemy Options' : $this->page['name'];
    }

    private function get_active_tab_id( array $tabs ) : string {
        $activeTab = $tabs[0]['id'];

        if( isset( $_GET[ 'tab' ] ) ) {
            $activeTab = $_GET[ 'tab' ];
        }

        return $activeTab;
    }

    protected function get_tabs_html( array $data ) : string {
        if( empty( $data['tabs'] ) ) {
            return '';
        }

        $activeTab = $this->get_active_tab_id( $data['tabs'] );

        $tabsHTML = '<div class="alchemy__tabs nav-tab-wrapper">';

        foreach ( $data['tabs'] as $tab ) {
            $tabClasses = ['nav-tab'];

            if( $activeTab === $tab['id'] ) {
                $tabClasses[] = 'nav-tab-active';
            }

            $tabsHTML .= sprintf( '<a class="%1$s" href="%2$s">%3$s</a>',
                join( ' ', $tabClasses ),
                sprintf( '?page=%1$s&tab=%2$s',
                    esc_attr( $data['id'] ),
                    esc_attr( $tab['id'] )
                ),
                $tab['name']
            );
        }

        $tabsHTML .= '</div>';

        return apply_filters( 'alch_options_tabs_html', $tabsHTML );
    }
}