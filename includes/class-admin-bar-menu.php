<?php

namespace Alchemy\Includes;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Admin_Bar_Menu' ) ) {
    return;
}

class Admin_Bar_Menu {
    private \WP_Admin_Bar $admin_bar;

    function __construct( \WP_Admin_Bar $wp_admin_bar ) {
        $this->admin_bar = $wp_admin_bar;
    }

    function create_menu( array $pages ) : void {
        foreach ( $pages as $page ) {
            $pageCapabilities = Options_Page::get_page_capabilities( $page['id'] );

            if ( ! current_user_can( $pageCapabilities ) ) {
                continue;
            }

            $pageArgs = array(
                'id' => $page['id'],
                'title' => $page['name'],
                'href' => admin_url( 'admin.php?page=' . $page['id'] ),
            );

            if( ! empty( $page['parent-id'] ) ) {
                $pageArgs['parent'] = $page['parent-id'];
            }

            if( ! empty( $page['parent-id'] ) && $page['id'] !== $page['parent-id'] ) {
                $this->admin_bar->add_node( $pageArgs );
            }
        }
    }
}
