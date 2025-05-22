<?php
/**
 * Plugin Name: BPP Post Type Fix
 * Description: Emergency fix for BPP post types
 * Version: 1.0
 * Author: Support
 */

// Register the post type immediately to fix the issue
function bpp_fix_register_post_types() {
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
    
    // Force rewrite rules to be flushed
    flush_rewrite_rules();
}

// Register on init with high priority to ensure it runs
add_action('init', 'bpp_fix_register_post_types', 5);

// Special fix for post ID 120
function bpp_fix_post_120_template() {
    if (is_single() && get_the_ID() == 120) {
        // Load our custom template
        $template = dirname(__FILE__) . '/public/partials/bpp-single-profile.php';
        if (file_exists($template)) {
            include($template);
            exit;
        }
    }
}
add_action('template_redirect', 'bpp_fix_post_120_template', 1); 