<?php
/**
 *
 * Test case for Drop It
 *
 */
class Blimply_UnitTestCase extends WP_UnitTestCase {
	public $blimply;

	/**
	 * Init
	 * @return [type] [description]
	 */
	function setup() {
		parent::setup();
		global $blimply;
		$this->blimply = $blimply;
		$this->blimply->action_admin_init();
	}

	function teardown() {
	}

	// Check if settings get set up on activation
	function test_default_settings() {
		$this->assertNotEmpty( $this->blimply->options );
		$this->assertInternalType( 'array', $this->blimply->options );
	}

	// Check if errors are handled properly
	function test_error_handling() {

	}

	function test_save_drop() {
		//$this->assertNotEmpty( $this->di->save_drop() );
	}
}

