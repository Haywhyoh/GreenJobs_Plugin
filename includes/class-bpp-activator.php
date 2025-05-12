<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://example.com
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
 * @author     Your Name <email@example.com>
 */
class BPP_Activator {

    /**
     * Create the necessary database tables and set up initial data.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create custom post type for applicants
        self::create_custom_post_types();
        
        // Create industry taxonomy
        self::create_taxonomies();
        
        // Flush rewrite rules
        flush_rewrite_rules();
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