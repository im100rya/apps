<?php
/**
 * PHPUnit bootstrap for WordPress tests.
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	fwrite( STDERR, "Could not find WordPress tests bootstrap. Set WP_TESTS_DIR.\n" );
	exit( 1 );
}

require_once $_tests_dir . '/includes/functions.php';

/**
 * Load plugin for tests.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/panasys-popups.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
