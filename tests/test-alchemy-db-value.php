<?php
/**
 * Class SampleTest
 *
 * @package Alchemy_Options
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

    /**
     * Checks that the main class is loaded.
     */
    function test_plugin_is_active() {
        $this->assertTrue( class_exists( 'Alchemy_Options' ) );
    }
}
