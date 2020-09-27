<?php
/**
 * Class Test_Alchemy_Text_Field
 *
 * @package Alchemy_Options
 */

/**
 * Sample test case.
 */
class Test_Alchemy_Text_Field extends Test_Field_Class {
    private $field = array(
		'type'=>'text',
		'id'=>'id',
		'value'=>'Some value'
	);

    function setUp() {
        parent::setUp();

		wp_set_current_user( $this->administrator );
		$this->set_valid_alchemy_save_options_request( $this->field );
    }

    function test_value_is_retrieved() {
		$this->server->dispatch( $this->request );

		$this->assertEquals( $this->field['value'], alch_get_option( $this->field['id'] ) );
	}

    function test_value_is_sanitised() {
		$this->field['value'] = 'Some value<script>alert(1);</script>';

		$this->server->dispatch( $this->request );

		$this->assertEquals( 'Some value', alch_get_option( $this->field['id'] ) );
	}

    function test_value_is_validated() {
		add_filter( 'alch_do_validate_text_value', function( $error, $value ) {
			if( 'Some value' === $value ) {
				$error = 'Error message for text';
			}

			return $error;
		}, 10, 2 );

		$response = $this->server->dispatch( $this->request );

		$this->assertNotEmpty( $response->data['data']['invalid-fields'] );
		$this->assertEquals( 'Error message for text', $response->data['data']['invalid-fields'][$this->field['id']] );
	}


    function test_class_exists() {
        $this->assertTrue( class_exists( 'Alchemy\Fields\Text\Field' ) );
    }

    function tearDown() {
		alch_admin_delete_option( $this->id );

        parent::tearDown();
    }
}
