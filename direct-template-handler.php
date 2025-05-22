<?php
/**
 * Emergency direct template handler for BPP Professionals
 *
 * This file can be placed in your WordPress root directory to directly 
 * handle viewing post ID 120 regardless of post type registration.
 */

// Bootstrap WordPress
define('WP_USE_THEMES', false);
require('./wp-load.php');

// Set up the global post variable
global $post;

// Get post ID from query parameter, default to 120
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 120;

// Get the post
$post = get_post($post_id);

if (!$post) {
    echo 'Post not found';
    exit;
}

// Load the header
get_header();

// Buffer output
ob_start();

// Include the template directly
$template_path = WP_PLUGIN_DIR . '/black-potential-pipeline/public/partials/bpp-single-profile.php';

if (file_exists($template_path)) {
    include($template_path);
} else {
    // Fallback display
    echo '<div class="container">';
    echo '<h1>' . get_the_title($post) . '</h1>';
    echo '<div class="content">' . get_the_content($post) . '</div>';
    
    // Metadata
    echo '<div class="meta">';
    echo '<p><strong>Job Title:</strong> ' . get_post_meta($post->ID, 'bpp_job_title', true) . '</p>';
    echo '<p><strong>Location:</strong> ' . get_post_meta($post->ID, 'bpp_location', true) . '</p>';
    echo '<p><strong>Skills:</strong> ' . get_post_meta($post->ID, 'bpp_skills', true) . '</p>';
    echo '</div>';
    echo '</div>';
}

// Output the buffer
echo ob_get_clean();

// Load the footer
get_footer(); 