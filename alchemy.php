<?php
/**
 * Plugin Name: Alchemy options
 * Description: Just another Options plugin inspired by the wonderful Option Tree and Archetype.
 * Author: Alex Bondarev
 * Author URI: http://alexbondarev.com
 * Text Domain: alchemy-options
 *
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ALCHEMY_OPTIONS_VERSION', '0.0.1' );
define( 'ALCHEMY_OPTIONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ALCHEMY_OPTIONS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

require_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemy-options.php' );

function alch_run_plugin() {
    $alch_options = new Alchemy_Options();

    $alch_options->activate();
}

add_action( 'after_setup_theme', 'alch_run_plugin', 1 );

