<?php

/**
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/willroscoe/wordpress-plugin-books
 * @since             1.0.0
 * @package           MP_Books
 *
 * @wordpress-plugin
 * Plugin Name:       Mattering Press Books plugin
 * Plugin URI:        https://github.com/willroscoe/wordpress-plugin-books
 * Description:       Adds a 'book' custom post type and always displaying/rendering of epub books in a page. The epub processing/rendering part of this plugin is significantly based on an earlier version by Edward Akerboom (opensource@infostreams.net), who has kindly permitted his code to be adapted for this wordpress plugin.
 * Version:           1.0.0
 * Authors:           Will Roscoe, Edward Akerboom (opensource@infostreams.net)
 * Author URI:        https://github.com/willroscoe
 * License:           The Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       mp-books
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MP_BOOKS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mp-books-activator.php
 */
function activate_mp_books() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mp-books-activator.php';
	MP_Books_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mp-books-deactivator.php
 */
function deactivate_mp_books() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mp-books-deactivator.php';
	MP_Books_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mp_books' );
register_deactivation_hook( __FILE__, 'deactivate_mp_books' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mp-books.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mp_books() {

	$plugin = new MP_Books();
	$plugin->run();

}
run_mp_books();
