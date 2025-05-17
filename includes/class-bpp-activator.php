<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://codemygig.com,
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 * @author     Adedayo Ayomide Samue ayomide@codemygig.com
 */
class BPP_Activator {

    /**
     * Create the necessary database tables and set up initial data.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Require the post types file to ensure proper registration
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-bpp-post-types.php';
        
        // Initialize post types
        $post_types = new BPP_Post_Types('black-potential-pipeline', '1.0.0');
        
        // Register post types and taxonomies
        $post_types->register_post_types();
        $post_types->register_taxonomies();
        
        // Flush rewrite rules to ensure permalinks work properly
        $post_types->flush_rewrite_rules();
    }

    /**
     * Register the custom post type for applicant profiles.
     *
     * @since    1.0.0
     */
    private static function create_custom_post_types() {
        // This will be called during activation, but full CPT registration 
        // will be handled in a separate class during plugin initialization
    }

    /**
     * Register the taxonomy for industry categories.
     *
     * @since    1.0.0
     */
    private static function create_taxonomies() {
        // This will be called during activation, but full taxonomy registration 
        // will be handled in a separate class during plugin initialization
    }
} 