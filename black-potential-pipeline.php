<?php
/**
 * Plugin Name: Black Potential Pipeline
 * Plugin URI: https://beinitiative/black-potential-pipeline
 * Description: A curated database of Black professionals seeking green jobs, featuring a submission form, admin screening interface, and public directory.
 * Version: 1.0.0
 * Author: Adedayo Samuel
 * Author URI: https://codemygig.com/cruisedev
 * Text Domain: black-potential-pipeline
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('BPP_VERSION', '1.0.0');
define('BPP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BPP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BPP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('BPP_PLUGIN_FILE', __FILE__);

/**
 * The code that runs during plugin activation.
 */
function activate_black_potential_pipeline() {
    require_once BPP_PLUGIN_DIR . 'includes/class-bpp-activator.php';
    BPP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_black_potential_pipeline() {
    require_once BPP_PLUGIN_DIR . 'includes/class-bpp-deactivator.php';
    BPP_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_black_potential_pipeline');
register_deactivation_hook(__FILE__, 'deactivate_black_potential_pipeline');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require BPP_PLUGIN_DIR . 'includes/class-bpp.php';

/**
 * Begins execution of the plugin.
 */
function run_black_potential_pipeline() {
    $plugin = new BPP();
    $plugin->run();
}
run_black_potential_pipeline(); 