<?php
/**
 * @package Alchemy_Options
 *
 * @wordpress-plugin
 * Plugin Name: Alchemy options
 * Description: Just another Options plugin inspired by the wonderful Option Tree and Archetype.
 * Version: 0.0.1
 * Author: Alex Bondarev
 * Author URI: http://alexbondarev.com
 * Text Domain: alchemy-options
 *
 */

namespace Alchemy_Options;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ALCHEMY_OPTIONS_VERSION', '0.0.1' );
define( 'ALCHEMY_OPTIONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ALCHEMY_OPTIONS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'autoload.php' );

function alch_run_plugin() {
    load_plugin_textdomain( 'alchemy-options', ALCHEMY_OPTIONS_PLUGIN_DIR . 'languages' );

    $alch_options = new Includes\Options();
    $alch_options->activate();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\alch_run_plugin' );