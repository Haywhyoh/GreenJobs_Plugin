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
 * Debug function to check post type registration
 */
function bpp_debug_post_types() {
    // Check if the bpp_applicant post type exists
    $post_type_exists = post_type_exists('bpp_applicant');
    error_log('BPP Debug: bpp_applicant post type exists: ' . ($post_type_exists ? 'Yes' : 'No'));
    
    // Get post type object
    if ($post_type_exists) {
        $post_type_obj = get_post_type_object('bpp_applicant');
        error_log('BPP Debug: Post type rewrite slug: ' . $post_type_obj->rewrite['slug']);
        error_log('BPP Debug: Post type has archive: ' . ($post_type_obj->has_archive ? 'Yes' : 'No'));
        error_log('BPP Debug: Post type publicly queryable: ' . ($post_type_obj->publicly_queryable ? 'Yes' : 'No'));
    }
    
    // Get post with ID 120
    $post = get_post(120);
    if ($post) {
        error_log('BPP Debug: Post 120 exists - Type: ' . $post->post_type . ', Status: ' . $post->post_status);
        error_log('BPP Debug: Post 120 permalink: ' . get_permalink(120));
    } else {
        error_log('BPP Debug: Post 120 does not exist or is not accessible');
    }
}
add_action('init', 'bpp_debug_post_types', 999);

/**
 * Special fix for post ID 120
 * This adds a dedicated template redirect for post ID 120
 */
function bpp_fix_post_120_template() {
    global $wp_query, $post;
    
    // Check if we're querying post with ID 120
    if (is_singular() && isset($wp_query->query['p']) && $wp_query->query['p'] == 120) {
        error_log('BPP: Intercepted query for post ID 120');
        
        // Check if post exists and is of our type
        $post_120 = get_post(120);
        if ($post_120 && $post_120->post_type === 'bpp_applicant') {
            error_log('BPP: Post 120 is a bpp_applicant, loading custom template');
            
            // Set the global post to ensure it's available in the template
            $post = $post_120;
            setup_postdata($post);
            
            // Load and include our template
            $template = BPP_PLUGIN_DIR . 'public/partials/bpp-single-profile.php';
            if (file_exists($template)) {
                error_log('BPP: Loading custom template: ' . $template);
                include($template);
                exit;
            } else {
                error_log('BPP: Custom template not found: ' . $template);
            }
        }
    }
}
add_action('template_redirect', 'bpp_fix_post_120_template', 1);

/**
 * Fallback for viewing bpp_applicant posts even if post type registration fails
 * This ensures posts are still accessible to users
 */
function bpp_direct_view_handler() {
    global $wp_query, $post;
    
    // Check if a post is being viewed with a numeric ID parameter
    if (isset($_GET['p']) && is_numeric($_GET['p'])) {
        $post_id = intval($_GET['p']);
        $post_obj = get_post($post_id);
        
        // Check if this is one of our post types but the post type isn't registered or not publicly queryable
        if ($post_obj && $post_obj->post_type === 'bpp_applicant') {
            $post_type_obj = get_post_type_object('bpp_applicant');
            
            if (!$post_type_obj || !$post_type_obj->publicly_queryable) {
                error_log('BPP: Direct View Handler activating for post ID ' . $post_id);
                
                // Setup the global post and query vars to mimic a normal WordPress template
                $post = $post_obj;
                setup_postdata($post);
                $wp_query->is_single = true;
                $wp_query->is_singular = true;
                
                // Set the title for the page
                add_filter('wp_title', function($title) use ($post_obj) {
                    return $post_obj->post_title . ' | ' . get_bloginfo('name');
                }, 10);
                
                // Output a complete page with header and footer
                get_header();
                echo '<div class="bpp-direct-view-container">';
                include_once(BPP_PLUGIN_DIR . 'public/partials/bpp-single-profile.php');
                echo '</div>';
                get_footer();
                exit;
            }
        }
    }
}
add_action('template_redirect', 'bpp_direct_view_handler', 5);

/**
 * Register bpp_applicant post type directly to ensure it's always available
 * This is a failsafe in case the main plugin's registration fails
 */
function bpp_emergency_post_type_registration() {
    if (!post_type_exists('bpp_applicant')) {
        error_log('BPP: Emergency post type registration activating');
        
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
        
        error_log('BPP: Emergency post type registration complete');
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