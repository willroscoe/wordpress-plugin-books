<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://github.com/willroscoe
 * @since             1.0.0
 * @package           MP_Books
 *
 * @wordpress-plugin
 * Plugin Name:       Mattering Press Books plugin
 * Plugin URI:        
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Will Roscoe
 * Author URI:        http://github.com/willroscoe
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
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
