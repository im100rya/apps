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

	/**
	 * Ensure popup shortcode includes 1-day hide behavior attribute.
	 */
	public function test_popup_shortcode_includes_hide_days_attribute() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'panasys_popup',
				'post_status'  => 'publish',
				'post_title'   => 'Demo popup hide days',
				'post_content' => 'Popup content',
			)
		);

		$output = do_shortcode( sprintf( '[panasys_popup id="%d"]', $post_id ) );
		$this->assertStringContainsString( 'data-hide-days="1"', $output );
		$this->assertStringContainsString( 'data-session-frequency="one_day"', $output );
	}

	/**
	 * Ensure popup shortcode includes visual style variables.
	 */
	public function test_popup_shortcode_includes_visual_style_variables() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'panasys_popup',
				'post_status'  => 'publish',
				'post_title'   => 'Styled popup',
				'post_content' => 'Styled popup content',
			)
		);

		update_post_meta( $post_id, '_panasys_popup_width', '720px' );
		update_post_meta( $post_id, '_panasys_popup_background_color', '#123456' );
		update_post_meta( $post_id, '_panasys_popup_text_color', '#abcdef' );

		$output = do_shortcode( sprintf( '[panasys_popup id="%d"]', $post_id ) );

		$this->assertStringContainsString( '--panasys-popup-max-width: 720px', $output );
		$this->assertStringContainsString( '--panasys-popup-background-color: #123456', $output );
		$this->assertStringContainsString( '--panasys-popup-text-color: #abcdef', $output );
	}

}
