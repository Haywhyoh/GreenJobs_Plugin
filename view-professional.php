<?php
/**
 * Direct Professional Profile Viewer
 * 
 * This file provides a direct way to view professional profiles
 * when the normal post type registration isn't working properly.
 * 
 * Usage: /view-professional.php?id=120
 */

// Bootstrap WordPress core
define('WP_USE_THEMES', false);
require('./wp-load.php');

// Get the post ID, default to 120
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 120;

// Get the post
$post = get_post($post_id);

if (!$post) {
    wp_die('Professional profile not found.');
}

// Add CSS for styling
echo '<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; }
    .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
    .profile-header { display: flex; margin-bottom: 30px; }
    .profile-photo { width: 200px; height: 200px; margin-right: 30px; background: #f5f5f5; }
    .profile-photo img { width: 100%; height: 100%; object-fit: cover; }
    .profile-info { flex: 1; }
    .profile-name { font-size: 24px; margin: 0 0 10px 0; }
    .profile-job { font-size: 18px; margin: 0 0 15px 0; color: #555; }
    .profile-meta { display: flex; flex-wrap: wrap; margin-bottom: 15px; }
    .meta-item { margin-right: 20px; margin-bottom: 10px; }
    .meta-label { font-weight: bold; }
    .profile-content { margin-bottom: 30px; }
    .skills-list { display: flex; flex-wrap: wrap; }
    .skill-tag { background: #f0f0f0; padding: 5px 10px; margin-right: 10px; margin-bottom: 10px; border-radius: 3px; }
</style>';

// Get the attachment ID for thumbnail
$thumb_id = get_post_thumbnail_id($post->ID);
$thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';

// Get post meta
$job_title = get_post_meta($post->ID, 'bpp_job_title', true);
$location = get_post_meta($post->ID, 'bpp_location', true);
$years_experience = get_post_meta($post->ID, 'bpp_years_experience', true);
$skills = get_post_meta($post->ID, 'bpp_skills', true);
$skills_array = !empty($skills) ? explode(',', $skills) : array();
$bio = get_post_meta($post->ID, 'bpp_bio', true) ?: $post->post_content;
$website = get_post_meta($post->ID, 'bpp_website', true);
$linkedin = get_post_meta($post->ID, 'bpp_linkedin', true);
$email = get_post_meta($post->ID, 'bpp_email', true);
$phone = get_post_meta($post->ID, 'bpp_phone', true);

// Build the page
echo '<div class="container">';

echo '<div class="profile-header">';
echo '<div class="profile-photo">';
if ($thumb_url) {
    echo '<img src="' . esc_url($thumb_url) . '" alt="' . esc_attr($post->post_title) . '">';
} else {
    echo '<div style="text-align: center; line-height: 200px;">No Photo</div>';
}
echo '</div>';

echo '<div class="profile-info">';
echo '<h1 class="profile-name">' . esc_html($post->post_title) . '</h1>';
if ($job_title) {
    echo '<h2 class="profile-job">' . esc_html($job_title) . '</h2>';
}

echo '<div class="profile-meta">';
if ($location) {
    echo '<div class="meta-item"><span class="meta-label">Location:</span> ' . esc_html($location) . '</div>';
}
if ($years_experience) {
    echo '<div class="meta-item"><span class="meta-label">Experience:</span> ' . esc_html($years_experience) . ' years</div>';
}
echo '</div>';

if ($website || $linkedin) {
    echo '<div class="profile-links">';
    if ($website) {
        echo '<a href="' . esc_url($website) . '" target="_blank" style="margin-right: 15px;">Website</a>';
    }
    if ($linkedin) {
        echo '<a href="' . esc_url($linkedin) . '" target="_blank">LinkedIn</a>';
    }
    echo '</div>';
}
echo '</div>'; // End profile-info
echo '</div>'; // End profile-header

if ($bio) {
    echo '<div class="profile-content">';
    echo '<h3>Professional Bio</h3>';
    echo '<div>' . wpautop(esc_html($bio)) . '</div>';
    echo '</div>';
}

if (!empty($skills_array)) {
    echo '<div class="profile-skills">';
    echo '<h3>Skills & Expertise</h3>';
    echo '<div class="skills-list">';
    foreach ($skills_array as $skill) {
        echo '<span class="skill-tag">' . esc_html(trim($skill)) . '</span>';
    }
    echo '</div>';
    echo '</div>';
}

if ($email || $phone) {
    echo '<div class="profile-contact">';
    echo '<h3>Contact Information</h3>';
    if ($email) {
        echo '<p><strong>Email:</strong> <a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p>';
    }
    if ($phone) {
        echo '<p><strong>Phone:</strong> ' . esc_html($phone) . '</p>';
    }
    echo '</div>';
}

echo '<div style="margin-top: 30px;">';
echo '<a href="javascript:history.back();">&laquo; Back</a>';
echo '</div>';

echo '</div>'; // End container 