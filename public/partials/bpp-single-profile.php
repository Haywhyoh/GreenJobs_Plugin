<?php
/**
 * Template for displaying a single professional profile
 *
 * This template displays detailed information about an individual professional
 * in the Black Potential Pipeline database.
 *
 * @link       https://codemygig.com,
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/public/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Enqueue Bootstrap styles if not already enqueued
wp_enqueue_style('bpp-bootstrap-style');
wp_enqueue_style('bpp-bootstrap-custom-style');
wp_enqueue_script('bpp-bootstrap-script');

// Get the current post ID
$post_id = get_the_ID();

// Get applicant metadata
$job_title = get_post_meta($post_id, 'bpp_job_title', true);
$location = get_post_meta($post_id, 'bpp_location', true);
$years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
$skills = get_post_meta($post_id, 'bpp_skills', true);
$skills_array = !empty($skills) ? explode(',', $skills) : array();

// Check multiple possible bio field names
$bio = get_post_meta($post_id, 'bpp_bio', true);
if (empty($bio)) {
    $bio = get_post_meta($post_id, 'bio', true);
}
if (empty($bio)) {
    $bio = get_post_meta($post_id, 'bpp_cover_letter', true);
}
if (empty($bio)) {
    $bio = get_post_meta($post_id, 'cover_letter', true);
}
// Use post content as fallback if all meta fields are empty
if (empty($bio) && function_exists('get_post_field')) {
    $post_content = get_post_field('post_content', $post_id);
    if (!empty($post_content)) {
        $bio = $post_content;
    }
}

$website = get_post_meta($post_id, 'bpp_website', true);
$linkedin = get_post_meta($post_id, 'bpp_linkedin', true);
$email = get_post_meta($post_id, 'bpp_email', true);
$phone = get_post_meta($post_id, 'bpp_phone', true);
$resume_id = get_post_meta($post_id, 'bpp_resume', true);
$resume_url = !empty($resume_id) ? wp_get_attachment_url($resume_id) : '';
$featured = (bool) get_post_meta($post_id, 'bpp_featured', true);

// Default industry names lookup array for formatting industry slugs
$default_industry_names = array(
    'nature-based-work' => __('Nature-based work', 'black-potential-pipeline'),
    'environmental-policy' => __('Environmental policy', 'black-potential-pipeline'),
    'climate-science' => __('Climate science', 'black-potential-pipeline'),
    'green-construction' => __('Green construction & infrastructure', 'black-potential-pipeline'),
);

// Get industry from taxonomy
$industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
$industry = '';
if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
    $industry = $industry_terms[0];
}

// If no industry is set from terms, check if we can find a match in the default industries
if (empty($industry)) {
    $industry_meta = get_post_meta($post_id, 'bpp_industry', true);
    if (!empty($industry_meta)) {
        // Check if this is a known slug and convert to readable name
        if (isset($default_industry_names[$industry_meta])) {
            $industry = $default_industry_names[$industry_meta];
        } else {
            // Format as readable text
            $industry = ucwords(str_replace('-', ' ', $industry_meta));
        }
    }
}

// Get profile visibility settings
$directory_settings = get_option('bpp_directory_settings', array());
$visibility_settings = isset($directory_settings['profile_visibility']) ? $directory_settings['profile_visibility'] : array();
$default_visibility = array(
    'photo' => true,
    'job_title' => true,
    'industry' => true,
    'location' => true,
    'years_experience' => true,
    'skills' => true,
    'bio' => true,
    'website' => true,
    'linkedin' => true,
    'email' => true,
    'phone' => false,
    'resume' => true
);
// Merge with defaults
$visibility = array_merge($default_visibility, $visibility_settings);

// Get related professionals in the same industry (limit to 3)
$related_args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'post__not_in' => array($post_id),
    'orderby' => 'rand',
);

// Add industry to the query if we have it
if (!empty($industry)) {
    $related_args['tax_query'] = array(
        array(
            'taxonomy' => 'bpp_industry',
            'field' => 'name',
            'terms' => $industry,
        ),
    );
}

$related_query = new WP_Query($related_args);

// Get contact form shortcode if available
$contact_form_shortcode = get_option('bpp_profile_contact_form', '');

// Track profile view
$profile_views = (int) get_post_meta($post_id, 'bpp_profile_views', true);
update_post_meta($post_id, 'bpp_profile_views', $profile_views + 1);

get_header();
?>

<div class="container py-5 bpp-single-profile-container">
    <div class="row">
        <div class="col-md-12 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo esc_url(home_url()); ?>"><?php _e('Home', 'black-potential-pipeline'); ?></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo esc_url(home_url('/professionals/')); ?>"><?php _e('Professionals', 'black-potential-pipeline'); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4 shadow">
                <div class="card-body">
                    <div class="row">
                        <?php if ($featured) : ?>
                            <div class="col-12 mb-3">
                                <div class="badge bg-warning text-dark p-2 d-flex align-items-center" style="width: fit-content;">
                                    <i class="dashicons dashicons-star-filled me-1"></i>
                                    <?php _e('Featured Professional', 'black-potential-pipeline'); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($visibility['photo'])) : ?>
                        <div class="col-md-3 mb-4 mb-md-0 text-center">
                            <div class="profile-photo-wrapper rounded-circle overflow-hidden mx-auto" style="width: 180px; height: 180px; border: 5px solid #f8f9fa;">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('medium', array('class' => 'img-fluid w-100 h-100', 'style' => 'object-fit: cover;')); ?>
                                <?php else : ?>
                                    <div class="d-flex align-items-center justify-content-center bg-light h-100">
                                        <i class="dashicons dashicons-admin-users" style="font-size: 60px; width: 60px; height: 60px; color: var(--bpp-secondary-color);"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="<?php echo (!empty($visibility['photo'])) ? 'col-md-9' : 'col-md-12'; ?>">
                            <h1 class="h2 mb-1"><?php the_title(); ?></h1>
                            
                            <?php if (!empty($job_title) && !empty($visibility['job_title'])) : ?>
                                <h2 class="h4 text-muted mb-3"><?php echo esc_html($job_title); ?></h2>
                            <?php endif; ?>
                            
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <?php if (!empty($industry) && !empty($visibility['industry'])) : ?>
                                    <div class="badge bg-primary p-2">
                                        <i class="dashicons dashicons-category me-1" style="line-height: 1.2;"></i>
                                        <?php echo esc_html($industry); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($location) && !empty($visibility['location'])) : ?>
                                    <div class="badge bg-secondary p-2">
                                        <i class="dashicons dashicons-location me-1" style="line-height: 1.2;"></i>
                                        <?php echo esc_html($location); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($years_experience) && !empty($visibility['years_experience'])) : ?>
                                    <div class="badge bg-info text-dark p-2">
                                        <i class="dashicons dashicons-businessman me-1" style="line-height: 1.2;"></i>
                                        <?php printf(_n('%s year experience', '%s years experience', (int)$years_experience, 'black-potential-pipeline'), esc_html($years_experience)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ((!empty($website) && !empty($visibility['website'])) || (!empty($linkedin) && !empty($visibility['linkedin']))) : ?>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if (!empty($website) && !empty($visibility['website'])) : ?>
                                        <a href="<?php echo esc_url($website); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                            <i class="dashicons dashicons-admin-site-alt3 me-1" style="line-height: 1.2;"></i>
                                            <?php _e('Website', 'black-potential-pipeline'); ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($linkedin) && !empty($visibility['linkedin'])) : ?>
                                        <a href="<?php echo esc_url($linkedin); ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                            <i class="dashicons dashicons-linkedin me-1" style="line-height: 1.2;"></i>
                                            <?php _e('LinkedIn', 'black-potential-pipeline'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <?php if (!empty($bio) && !empty($visibility['bio'])) : ?>
                        <div class="card mb-4 shadow">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><?php _e('Professional Bio', 'black-potential-pipeline'); ?></h3>
                            </div>
                            <div class="card-body bio-content">
                                <?php 
                                // Use wp_kses_post instead of esc_html to allow safe HTML formatting
                                echo wpautop(wp_kses_post($bio)); 
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($skills_array) && !empty($visibility['skills'])) : ?>
                        <div class="card mb-4 shadow">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><?php _e('Skills & Expertise', 'black-potential-pipeline'); ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($skills_array as $skill) : ?>
                                        <span class="badge bg-light text-dark p-2 border"><?php echo esc_html(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-4">
                    <div class="card mb-4 shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="h5 mb-0"><?php _e('Contact Information', 'black-potential-pipeline'); ?></h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($email) && !empty($visibility['email'])) : ?>
                                <div class="mb-3 bpp-contact-email">
                                    <div class="d-flex align-items-center">
                                        <i class="dashicons dashicons-email-alt me-2" style="color: #0d6efd;"></i>
                                        <div>
                                            <div class="small text-muted"><?php _e('Email', 'black-potential-pipeline'); ?></div>
                                            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($phone) && !empty($visibility['phone'])) : ?>
                                <div class="mb-3 bpp-contact-phone">
                                    <div class="d-flex align-items-center">
                                        <i class="dashicons dashicons-phone me-2" style="color: #0d6efd;"></i>
                                        <div>
                                            <div class="small text-muted"><?php _e('Phone', 'black-potential-pipeline'); ?></div>
                                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($resume_url) && !empty($visibility['resume'])) : ?>
                                <div class="mt-4">
                                    <a href="<?php echo esc_url($resume_url); ?>" class="btn btn-primary w-100" target="_blank">
                                        <i class="dashicons dashicons-pdf me-1" style="line-height: 1.2;"></i>
                                        <?php _e('Download Resume', 'black-potential-pipeline'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($contact_form_shortcode)) : ?>
                        <div class="card mb-4 shadow">
                            <div class="card-header bg-primary text-white">
                                <h3 class="h5 mb-0"><?php _e('Contact This Professional', 'black-potential-pipeline'); ?></h3>
                            </div>
                            <div class="card-body">
                                <?php echo do_shortcode($contact_form_shortcode); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($related_query->have_posts()) : ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 mb-0"><?php _e('Related Professionals', 'black-potential-pipeline'); ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            <?php while ($related_query->have_posts()) : $related_query->the_post(); 
                                $related_id = get_the_ID();
                                $related_job_title = get_post_meta($related_id, 'bpp_job_title', true);
                                $related_industry_terms = wp_get_post_terms($related_id, 'bpp_industry', array('fields' => 'names'));
                                $related_industry = '';
                                if (!is_wp_error($related_industry_terms) && !empty($related_industry_terms)) {
                                    $related_industry = $related_industry_terms[0];
                                }
                            ?>
                                <div class="col">
                                    <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.2s, box-shadow 0.2s;">
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="card-img-top">
                                                    <?php the_post_thumbnail('medium', array('class' => 'img-fluid w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                                </div>
                                            <?php else : ?>
                                                <div class="card-img-top bg-light text-center py-4" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: var(--bpp-secondary-color);"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php the_title(); ?></h5>
                                                <?php if (!empty($related_job_title)) : ?>
                                                    <p class="card-text text-muted"><?php echo esc_html($related_job_title); ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($related_industry)) : ?>
                                                    <div class="badge bg-primary"><?php echo esc_html($related_industry); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
    
    <div class="row mt-4">
        <div class="col-12">
            <a href="<?php echo esc_url(home_url('/professionals/')); ?>" class="btn btn-outline-primary">
                <i class="dashicons dashicons-arrow-left-alt me-1" style="line-height: 1.2;"></i>
                <?php _e('Back to All Professionals', 'black-potential-pipeline'); ?>
            </a>
        </div>
    </div>
</div>

<style>
:root {
    /* These variables will try to use WordPress theme colors first, then fall back to our new green/brown scheme */
    --bpp-primary-color: var(--wp--preset--color--primary, #61CE70);
    --bpp-secondary-color: var(--wp--preset--color--secondary, #6F3802);
    --bpp-info-color: var(--wp--preset--color--tertiary, #0dcaf0);
    --bpp-warning-color: var(--wp--preset--color--warning, #ffc107);
    --bpp-light-color: var(--wp--preset--color--light, #f8f9fa);
    --bpp-dark-color: var(--wp--preset--color--dark, #212529);
}

.dashicons {
    vertical-align: middle;
    line-height: 1.5;
}

.card {
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.profile-photo-wrapper {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Override Bootstrap color classes with theme colors */
.bg-primary {
    background-color: var(--bpp-primary-color) !important;
}

.bg-secondary {
    background-color: var(--bpp-secondary-color) !important;
}

.bg-info {
    background-color: var(--bpp-info-color) !important;
}

.bg-warning {
    background-color: var(--bpp-warning-color) !important;
}

.bg-light {
    background-color: var(--bpp-light-color) !important;
}

.text-dark {
    color: var(--bpp-dark-color) !important;
}

/* Apply theme color to links */
.card-body a:not(.btn) {
    color: var(--bpp-primary-color);
}

.btn-primary {
    background-color: var(--bpp-primary-color) !important;
    border-color: var(--bpp-primary-color) !important;
}

.btn-outline-primary {
    color: var(--bpp-primary-color) !important;
    border-color: var(--bpp-primary-color) !important;
}

.btn-outline-primary:hover {
    background-color: var(--bpp-primary-color) !important;
    color: white !important;
}

.bpp-contact-email i, .bpp-contact-phone i {
    color: var(--bpp-primary-color) !important;
}

.bpp-contact-email, .bpp-contact-phone {
    padding: 10px;
    border-radius: 6px;
    background-color: var(--bpp-light-color);
}

.bio-content {
    line-height: 1.8;
    font-size: 1.05rem;
}

.bio-content p {
    margin-bottom: 1.2rem;
}

.bio-content p:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .profile-photo-wrapper {
        width: 150px !important;
        height: 150px !important;
        margin-bottom: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Add hover effect to related professional cards
    $('.col .card').hover(
        function() {
            $(this).css({
                'transform': 'translateY(-5px)',
                'box-shadow': '0 10px 20px rgba(0,0,0,0.1)'
            });
        },
        function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': '0 2px 5px rgba(0,0,0,0.1)'
            });
        }
    );
});
</script>

<?php get_footer(); ?> 