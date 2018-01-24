<?php
/**
 * @package Alchemy_Options
 *
 * @wordpress-plugin
 * Plugin Name: Alchemy options
 * Plugin URI: https://docs.alchemy-options.com/
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
define( 'ALCHEMY_OPTIONS_DIR', plugin_dir_path( __FILE__ ) );
define( 'ALCHEMY_OPTIONS_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'ALCHEMY_OPTIONS_BASENAME', plugin_basename( __FILE__ ) );

include_once( ALCHEMY_OPTIONS_DIR . 'autoload.php' );

function alch_run_plugin() {
    load_plugin_textdomain( 'alchemy-options', false, ALCHEMY_OPTIONS_DIR . 'languages' );

    $alch_options = new Includes\Options_Loader();
    $alch_options->activate();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\alch_run_plugin' );