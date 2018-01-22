<?php
/**
 * Class Test_Alchemy_Text_Field
 *
 * @package Alchemy_Options
 */

/**
 * Sample test case.
 */
class Test_Alchemy_Text_Field extends WP_Ajax_UnitTestCase {
    private $id = 'my-text-option';

    public function setUp() {
        parent::setUp();

        $_POST['fields'] = array(
            $this->id => array(
                'type' => 'text',
                'value' => 'lorem ipsum'
            ),
        );
    }

    public function add_nonce() {
        $_POST['nonce'] = wp_create_nonce( 'alchemy_ajax_nonce' );
    }

    function test_nonce_is_required() {
        try {
            $this->_handleAjax( 'alchemy_options_save_options' );
        } catch ( WPAjaxDieStopException $e ) {
            $this->assertEquals( $e->getMessage(), 'Failed to check the nonce' );

            unset( $e );
        }
    }

    function test_value_is_saved() {
        $this->add_nonce();

        try {
            $this->_handleAjax( 'alchemy_options_save_options' );
        } catch ( WPAjaxDieContinueException $e ) {
            unset( $e );
        }

        $response = json_decode( $this->_last_response, true );

        $this->assertNotNull( $response );
        $this->assertTrue( $response['success'] );
        $this->assertEquals( $response['data'], 'Options saved' );
    }

    function test_value_is_retrieved() {
        $this->add_nonce();

        try {
            $this->_handleAjax( 'alchemy_options_save_options' );
        } catch ( WPAjaxDieContinueException $e ) {
            unset( $e );
        }

        $this->assertEquals( alch_get_option( $this->id ), 'lorem ipsum' );
    }

    function test_default_value_is_returned() {
        $this->assertEquals( alch_get_option( $this->id, 'default value' ), 'default value' );
    }

    function tearDown() {
        alch_delete_value( $this->id );

        parent::tearDown();
    }
}
