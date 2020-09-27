<?php
/**
 * Class Test_Field
 *
 * @package Alchemy_Options
 */

/**
 * Sample test case.
 */
class Test_Field_Class extends WP_UnitTestCase {
	protected $server;
	protected $request;
	protected $restApiRoutes;

	function setUp() {
		parent::setUp();

		global $wp_rest_server;

		$this->subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$this->administrator = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$this->server = $wp_rest_server = new \WP_REST_Server;
		$this->restApiRoutes = array(
			'save' => array(
				'options' => '/alchemy/v1/save-options',
				'network' => '/alchemy/v1/save-network-options',
				'metaboxes' => '/alchemy/v1/save-metaboxes',
			)
		);

		do_action( 'rest_api_init' );
	}

	function tearDown() {
		parent::tearDown();

		global $wp_rest_server;

		$wp_rest_server = null;
	}

	protected function set_valid_alchemy_save_options_request($value = [] ) {
		$value = ! empty( $value ) ? $value : array(
			'type'=>'text',
			'id'=>'id',
			'value'=>'Some value'
		);

		$this->request = new WP_REST_Request( WP_REST_Server::CREATABLE, $this->restApiRoutes['save']['options'] );

		$this->request->set_param( '_wpnonce', wp_create_nonce( 'wp_rest' ) );
		$this->request->set_param( 'page-id', 'my-options-page' );
		$this->request->set_param( 'values', json_encode( [$value] ) );
	}

	protected function assert_response_status($status, $response ) {
		$this->assertEquals( $status, $response->get_status() );
	}

	protected function assertResponseData( $data, $response ) {
		$response_data = $response->get_data();
		$tested_data = array();

		foreach( $data as $key => $value ) {
			if ( isset( $response_data[$key] ) ) {
				$tested_data[$key] = $response_data[$key];
			} else {
				$tested_data[$key] = null;
			}
		}

		$this->assertEquals( $data, $tested_data );
	}
}
