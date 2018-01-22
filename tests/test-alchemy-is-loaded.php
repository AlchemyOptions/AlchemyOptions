<?php
/**
 * Class Test_Alchemy_Existence
 *
 * @package Alchemy_Options
 */

/**
 * Sample test case.
 */
class Test_Alchemy_Existence extends WP_UnitTestCase {
    /**
     * Checks that the needed constants are defined.
     */
    function test_constants_defined() {
        $this->assertTrue( defined( 'ALCHEMY_OPTIONS_VERSION' ) );
        $this->assertTrue( defined( 'ALCHEMY_OPTIONS_PLUGIN_DIR' ) );
        $this->assertTrue( defined( 'ALCHEMY_OPTIONS_PLUGIN_DIR_URL' ) );
    }

    /**
     * Checks that we have main classes.
     */
    function test_all_classes_and_interfaces_availability() {
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Database_Value' ) );
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Field' ) );
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Fields_Loader' ) );
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Meta_Box' ) );
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Network_Options' ) );
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Options' ) );
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Options_Loader' ) );
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Repeaters' ) );
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Value' ) );
    }

    /**
     * Checks that we have main functions.
     */
    function test_functions_availability() {
        $this->assertTrue( function_exists( 'alch_options_id' ) );
        $this->assertTrue( function_exists( 'alch_repeaters_id' ) );
        $this->assertTrue( function_exists( 'alch_network_options_id' ) );
        $this->assertTrue( function_exists( 'alchemy_array_flatten' ) );
        $this->assertTrue( function_exists( 'alch_is_not_empty_array' ) );
        $this->assertTrue( function_exists( 'alch_kses_stripslashes' ) );
        $this->assertTrue( function_exists( 'alch_get_network_option' ) );
        $this->assertTrue( function_exists( 'alch_delete_value' ) );
    }
}
