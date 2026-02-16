<?php
/**
 * Shortcode tests.
 */

/**
 * Test popup shortcodes.
 */
class ShortcodesTest extends WP_UnitTestCase {

	/**
	 * Ensure shortcodes are registered.
	 */
	public function test_shortcodes_are_registered() {
		$this->assertTrue( shortcode_exists( 'panasys_popup' ) );
		$this->assertTrue( shortcode_exists( 'panasys_popup_trigger' ) );
	}

	/**
	 * Ensure popup shortcode renders content for published popup.
	 */
	public function test_popup_shortcode_renders_for_valid_popup() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'panasys_popup',
				'post_status'  => 'publish',
				'post_title'   => 'Demo popup',
				'post_content' => 'Hello popup',
			)
		);

		$output = do_shortcode( sprintf( '[panasys_popup id="%d"]', $post_id ) );

		$this->assertStringContainsString( 'Hello popup', $output );
		$this->assertStringContainsString( 'panasys-popup-' . $post_id, $output );
	}
}
