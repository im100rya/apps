<?php
/**
 * Uninstall Panasys Popups plugin.
 *
 * @package PanasysPopups
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$popup_ids = get_posts(
	array(
		'post_type'      => 'panasys_popup',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

if ( is_array( $popup_ids ) ) {
	foreach ( $popup_ids as $popup_id ) {
		delete_post_meta( $popup_id, '_panasys_popup_width' );
		delete_post_meta( $popup_id, '_panasys_popup_auto_open' );
		delete_post_meta( $popup_id, '_panasys_popup_background_color' );
		delete_post_meta( $popup_id, '_panasys_popup_text_color' );
	}
}
