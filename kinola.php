<?php
/**
 * Plugin Name:          Kinola
 * Plugin URI:           TODO
 * Description:          Kinola integration for WordPress
 * Version:              1.0
 * Author:               Kinola
 * Author URI:
 * License:              MIT
 * Text Domain:          kinola
 * Domain Path:          /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'KINOLA_VERSION', '1.0' );
define( 'KINOLA_PATH', WP_PLUGIN_DIR . '/kinola/' );

/**
 * Helper function for prettying up errors
 *
 * @param string $message
 * @param string $subtitle
 * @param string $title
 */
$kinola_error = function ( $message, $subtitle = '', $title = '' ) {
	$title   = $title ?: _x( 'Kinola error', 'Admin', 'kinola' );
	$message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p>";
	wp_die( $message, $title );
};

/**
 * Ensure compatible version of PHP is used
 */
if ( version_compare( phpversion(), '7.4', '<' ) ) {
	$kinola_error(
		_x( 'You must be using PHP 7.4 or greater.', 'Admin', 'kinola' ),
		_x( 'Invalid PHP version', 'Admin', 'kinola' )
	);
}

/**
 * Ensure compatible version of WordPress is used
 */
if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
	$kinola_error(
		_x( 'You must be using WordPress 6.0 or greater.', 'Admin', 'kinola' ),
		_x( 'Invalid WordPress version', 'Admin', 'kinola' )
	);
}

/**
 * Ensure Kinola URL is defined
 */
if ( ! defined( 'KINOLA_URL' ) ) {
	$kinola_error(
		_x( 'You must define KINOLA_URL in your wp-config file, e.g. <em>https://YOUR_SITE.kinola.ee</em>', 'Admin', 'kinola' ),
		_x( 'KINOLA_URL not defined.', 'Admin', 'kinola' )
	);
}

/**
 * Load dependencies
 */
if ( ! file_exists( $composer = __DIR__ . '/vendor/autoload.php' ) ) {
	$kinola_error(
		_x(
			'You appear to be running a development version of Kinola. You must run <code>composer install</code> from the plugin directory.',
			'Admin',
			'kinola'
		),
		_x(
			'Autoloader not found.',
			'Admin',
			'kinola'
		)
	);
}
require_once $composer;

/**
 * Start the plugin on plugins_loaded at priority 0.
 */
add_action( 'plugins_loaded', function () {
	load_plugin_textdomain(
		'kinola',
		false,
		trailingslashit( KINOLA_PATH ) . 'languages/'
	);

	$GLOBALS['KINOLA_BOOTSTRAP'] = new \Kinola\KinolaWp\Bootstrap();
	$GLOBALS['KINOLA_ADMIN']     = new \Kinola\KinolaWp\Admin\Admin();
}, 0 );

/**
 * Flush permalinks on deactivate
 */
function kinola_deactivate_plugin() {
	delete_option( 'kinola_rewrite_endpoint_registered' );
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'kinola_deactivate_plugin' );