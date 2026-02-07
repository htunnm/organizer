<?php
/**
 * PHPUnit bootstrap file.
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// If we are running in a full WP environment (e.g. wp-env), load WP.
// For basic unit tests in this scaffold, we might mock WP functions if needed.
// This is a placeholder for the WP test suite loader.

if ( getenv( 'WP_TESTS_DIR' ) ) {
	require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';
	require_once getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
} else {
	// Mock WP functions for basic unit testing without WP
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', dirname( __DIR__ ) . '/' );
	}
	if ( ! function_exists( 'add_action' ) ) {
		function add_action() {}
	}
	if ( ! function_exists( 'register_activation_hook' ) ) {
		function register_activation_hook() {}
	}
	if ( ! function_exists( 'register_deactivation_hook' ) ) {
		function register_deactivation_hook() {}
	}
	if ( ! function_exists( 'plugin_dir_path' ) ) {
		function plugin_dir_path( $file ) {
			return dirname( $file ) . '/'; }
	}
	if ( ! function_exists( 'plugin_dir_url' ) ) {
		function plugin_dir_url() {
			return ''; }
	}

	require_once dirname( __DIR__ ) . '/organizer.php';
}
