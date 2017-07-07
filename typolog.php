<?php

/**
 * Typolog.
 *
 * @link              http://typolog.org/
 * @since             1.0.0
 * @package           Typolog
 *
 * @wordpress-plugin
 * Plugin Name:       typolog
 * Plugin URI:        http://typolog.org/
 * Description:       Creates a catalog of type families for a WordPress based website, with WooCommerce support.
 * Version:           1.0.0
 * Author:            Meir Sadan
 * Author URI:        http://meirsadan.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       typolog
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require __DIR__ . '/vendor/autoload.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-font.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-family.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-font-file.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-license.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-collection.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-attachments.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-package-factory.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-product-factory.php';

/*
require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-licenses.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-families.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-fonts.php';
*/

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-font-factory.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/typolog-admin-settings.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-typolog-activator.php
 */
function activate_typolog() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-typolog-activator.php';
	Typolog_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-typolog-deactivator.php
 */
function deactivate_typolog() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-typolog-deactivator.php';
	Typolog_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_typolog' );
register_deactivation_hook( __FILE__, 'deactivate_typolog' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-typolog.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_typolog() {

	$plugin = new Typolog();
	$plugin->run();

}
run_typolog();

require plugin_dir_path( __FILE__ ) . 'includes/typolog-template.php';

