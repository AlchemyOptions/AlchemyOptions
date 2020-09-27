<?php
/**
 * Class Test_Field
 *
 * @package Alchemy_Options
 */

/**
 * Sample test case.
 */
class Test_Field extends Test_Field_Class {
	protected $server;
	protected $request;
	protected $restApiRoutes;

	function setUp() {
		parent::setUp();
	}

	function test_authentication() {
		$this->set_valid_alchemy_save_options_request();

		$this->assert_response_status( 401, $this->server->dispatch( $this->request ) );
	}

	function test_authorisation() {
		wp_set_current_user( $this->subscriber );
		$this->set_valid_alchemy_save_options_request();

		$this->assert_response_status( 403, $this->server->dispatch( $this->request ) );
	}

	function test_authorisation_caps() {
		wp_set_current_user( $this->administrator );
		$this->set_valid_alchemy_save_options_request();

		$this->assert_response_status( 200, $this->server->dispatch( $this->request ) );
	}

	function tearDown() {
		parent::tearDown();
	}
}
