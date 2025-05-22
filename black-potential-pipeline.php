<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Black_Potential_Pipeline
 */

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

// Define plugin directory constant if not already defined
if ( ! defined('BPP_PLUGIN_DIR') ) {
    define('BPP_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Define plugin constants
define('BPP_VERSION', '1.0.0');
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
 * Add custom rewrite rules for the plugin
 */
function bpp_add_rewrite_rules() {
    // Add custom rewrite rule for professional profiles
    add_rewrite_rule(
        'professionals/([^/]+)/?$',
        'index.php?post_type=bpp_applicant&name=$matches[1]',
        'top'
    );
    
    // Force a rewrite rules flush when needed
    if (get_option('bpp_flush_rewrite_rules', false)) {
        flush_rewrite_rules();
        delete_option('bpp_flush_rewrite_rules');
    }
}
add_action('init', 'bpp_add_rewrite_rules', 10);

/**
 * Emergency fallback to ensure bpp_applicant post type is always registered
 * This is a safety measure that will only run if the main registration fails
 */
function bpp_emergency_post_type_registration() {
    if (!post_type_exists('bpp_applicant')) {
        // Register the post type as a fallback
        register_post_type('bpp_applicant', array(
            'labels' => array(
                'name' => 'Professionals',
                'singular_name' => 'Professional',
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'professionals',
                'with_front' => true,
            ),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        ));
    }
}
add_action('init', 'bpp_emergency_post_type_registration', 999);

/**
 * Begins execution of the plugin.
 */
function run_black_potential_pipeline() {
    $plugin = new BPP();
    $plugin->run();
}
run_black_potential_pipeline(); 