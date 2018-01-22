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

    function setUp() {
        parent::setUp();

        $_POST['fields'] = array(
            $this->id => array(
                'type' => 'text',
                'value' => 'lorem ipsum'
            ),
        );
    }

    function test_class_exists() {
        $this->assertTrue( class_exists( 'Alchemy_Options\Includes\Fields\Text' ) );
    }

    function add_nonce() {
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

    function test_slashes_are_stripped_from_value() {
        $this->add_nonce();

        $_POST['fields'] = array(
            $this->id => array(
                'type' => 'text',
                'value' => 'lorem \" \' ipsum'
            ),
        );

        try {
            $this->_handleAjax( 'alchemy_options_save_options' );
        } catch ( WPAjaxDieContinueException $e ) {
            unset( $e );
        }

        $this->assertEquals( alch_get_option( $this->id ), sprintf('lorem %1$s %2$s ipsum', '"', "'") );
    }

    function test_value_is_sanitised() {
        $this->add_nonce();

        $_POST['fields'] = array(
            $this->id => array(
                'type' => 'text',
                'value' => 'lorem <p>ipsum</p> <script>alert("hi");</script>'
            ),
        );

        try {
            $this->_handleAjax( 'alchemy_options_save_options' );
        } catch ( WPAjaxDieContinueException $e ) {
            unset( $e );
        }

        $this->assertEquals( alch_get_option( $this->id ), 'lorem ipsum' );

        $_POST['fields'] = array(
            $this->id => array(
                'type' => 'email',
                'value' => 'test'
            ),
        );

        try {
            $this->_handleAjax( 'alchemy_options_save_options' );
        } catch ( WPAjaxDieContinueException $e ) {
            unset( $e );
        }

        $this->assertEquals( alch_get_option( $this->id ), '' );

        $_POST['fields'] = array(
            $this->id => array(
                'type' => 'email',
                'value' => 'a@bc.d'
            ),
        );

        try {
            $this->_handleAjax( 'alchemy_options_save_options' );
        } catch ( WPAjaxDieContinueException $e ) {
            unset( $e );
        }

        $this->assertEquals( alch_get_option( $this->id ), 'a@bc.d' );
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
