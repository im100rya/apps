<?php
/**
 * Plugin Name: Ticket Booking & Reviews for WooCommerce
 * Description: Sell bookable seats for movie theaters, cricket matches, football matches, and event passes with WooCommerce checkout and post-event reviews.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Panasys Technologies
 * License: GPL-2.0-or-later
 * Text Domain: ticket-booking-reviews
 *
 * @package TicketBookingReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TBR_VERSION', '1.0.0' );
define( 'TBR_FILE', __FILE__ );
define( 'TBR_DIR', plugin_dir_path( __FILE__ ) );
define( 'TBR_URL', plugin_dir_url( __FILE__ ) );

require_once TBR_DIR . 'includes/class-ticket-booking-reviews-plugin.php';

/**
 * Bootstrap plugin.
 *
 * @return Ticket_Booking_Reviews_Plugin
 */
function ticket_booking_reviews() {
	return Ticket_Booking_Reviews_Plugin::instance();
}

ticket_booking_reviews();
