<?php
/**
 * Dynamically loads the class attempting to be instantiated elsewhere in the
 * plugin. Based on the tutorial by Tom McFarlin - https://code.tutsplus.com/tutorials/using-namespaces-and-autoloading-in-wordpress-plugins-4--cms-27342
 *
 * @package Alchemy_Options\Loader
 */

spl_autoload_register( 'alchemy_options_autoload' );

function alchemy_options_autoload( $class_name ) {
    if ( false === strpos( $class_name, 'Alchemy' ) ) {
        return;
    }

    $file_parts = explode( '\\', $class_name );
    $namespace = '';
    $file_name = '';

    for ( $i = count( $file_parts ) - 1; $i > 0; $i-- ) {
        $current = str_ireplace( '_', '-', strtolower( $file_parts[ $i ] ) );

        if ( count( $file_parts ) - 1 === $i ) {
            if ( strpos( strtolower( $file_parts[ count( $file_parts ) - 1 ] ), 'interface' ) ) {
                $interface_name = strtolower( explode( '_', $file_parts[ count( $file_parts ) - 1 ] )[0] );

                $file_name = "interface-$interface_name.php";
            } else {
                $file_name = "class-$current.php";
            }
        } else {
            $namespace = '/' . $current . $namespace;
        }
    }

    $filepath = trailingslashit( untrailingslashit( AlCHEMY_DIR_PATH ) . $namespace ) . $file_name;

    if ( file_exists( $filepath ) ) {
        include_once( $filepath );
    } else {
        wp_die(
            esc_html__( sprintf( "The file attempting to be loaded at %s does not exist.", $filepath ), 'alchemy' )
        );
    }
}