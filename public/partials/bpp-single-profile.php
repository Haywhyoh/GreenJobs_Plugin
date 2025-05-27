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

// Note: Styles are already enqueued in the BPP_Public class's load_applicant_template method

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

<div class="bpp-single-profile-container">
    <!-- Breadcrumbs Navigation -->
    <div class="bpp-breadcrumbs">
        <a href="<?php echo esc_url(home_url()); ?>"><?php _e('Home', 'black-potential-pipeline'); ?></a>
        <span class="separator">›</span>
        <a href="<?php echo esc_url(home_url('/professionals/')); ?>"><?php _e('Professionals', 'black-potential-pipeline'); ?></a>
        <span class="separator">›</span>
        <span class="current"><?php the_title(); ?></span>
    </div>

    <!-- Main Profile Section -->
    <div class="bpp-card">
        <div class="bpp-card-body">
            <!-- Featured Badge -->
            <?php if ($featured) : ?>
                <div class="bpp-featured-badge">
                    <i class="dashicons dashicons-star-filled"></i>
                    <?php _e('Featured Professional', 'black-potential-pipeline'); ?>
                </div>
            <?php endif; ?>
            
            <!-- Profile Header Card -->
            <div class="bpp-profile-header-card">
                <!-- Profile Photo -->
                <?php if (!empty($visibility['photo'])) : ?>
                <div class="bpp-profile-photo">
                    <div class="bpp-photo-wrapper">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium', array('class' => '')); ?>
                        <?php else : ?>
                            <div class="bpp-no-photo">
                                <i class="dashicons dashicons-admin-users"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Profile Info -->
                <div class="bpp-profile-info">
                    <h1 class="bpp-profile-name"><?php the_title(); ?></h1>
                    
                    <?php if (!empty($job_title) && !empty($visibility['job_title'])) : ?>
                        <h2 class="bpp-profile-title"><?php echo esc_html($job_title); ?></h2>
                    <?php endif; ?>
                    
                    <!-- Tags Section -->
                    <div class="bpp-tags">
                        <?php if (!empty($industry) && !empty($visibility['industry'])) : ?>
                            <div class="bpp-tag bpp-industry-tag">
                                <i class="dashicons dashicons-category"></i>
                                <?php echo esc_html($industry); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($location) && !empty($visibility['location'])) : ?>
                            <div class="bpp-tag bpp-location-tag">
                                <i class="dashicons dashicons-location"></i>
                                <?php echo esc_html($location); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($years_experience) && !empty($visibility['years_experience'])) : ?>
                            <div class="bpp-tag bpp-experience-tag">
                                <i class="dashicons dashicons-businessman"></i>
                                <?php printf(_n('%s year experience', '%s years experience', (int)$years_experience, 'black-potential-pipeline'), esc_html($years_experience)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Social Links -->
                    <?php if ((!empty($website) && !empty($visibility['website'])) || (!empty($linkedin) && !empty($visibility['linkedin']))) : ?>
                        <div class="bpp-social-links">
                            <?php if (!empty($website) && !empty($visibility['website'])) : ?>
                                <a href="<?php echo esc_url($website); ?>" class="bpp-social-link" target="_blank">
                                    <i class="dashicons dashicons-admin-site-alt3"></i>
                                    <?php _e('Website', 'black-potential-pipeline'); ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($linkedin) && !empty($visibility['linkedin'])) : ?>
                                <a href="<?php echo esc_url($linkedin); ?>" class="bpp-social-link" target="_blank">
                                    <i class="dashicons dashicons-linkedin"></i>
                                    <?php _e('LinkedIn', 'black-potential-pipeline'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Two-Column Layout -->
    <div class="bpp-profile-grid">
        <!-- Left Column: Bio and Skills -->
        <div class="bpp-profile-main">
            <?php if (!empty($bio) && !empty($visibility['bio'])) : ?>
                <div class="bpp-card">
                    <div class="bpp-card-header">
                        <h3><?php _e('Professional Bio', 'black-potential-pipeline'); ?></h3>
                    </div>
                    <div class="bpp-card-body">
                        <div class="bpp-bio-content">
                            <?php 
                            // Use wp_kses_post instead of esc_html to allow safe HTML formatting
                            echo wpautop(wp_kses_post($bio)); 
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($skills_array) && !empty($visibility['skills'])) : ?>
                <div class="bpp-card">
                    <div class="bpp-card-header">
                        <h3><?php _e('Skills & Expertise', 'black-potential-pipeline'); ?></h3>
                    </div>
                    <div class="bpp-card-body">
                        <div class="bpp-skills-container">
                            <?php foreach ($skills_array as $skill) : ?>
                                <span class="bpp-skill-tag"><?php echo esc_html(trim($skill)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Column: Contact Information -->
        <div class="bpp-profile-sidebar">
            <div class="bpp-card">
                <div class="bpp-card-header">
                    <h3><?php _e('Contact Information', 'black-potential-pipeline'); ?></h3>
                </div>
                <div class="bpp-card-body">
                    <?php if (!empty($email) && !empty($visibility['email'])) : ?>
                        <div class="bpp-contact-item">
                            <i class="dashicons dashicons-email-alt"></i>
                            <div class="bpp-contact-item-content">
                                <div class="bpp-contact-label"><?php _e('Email', 'black-potential-pipeline'); ?></div>
                                <div class="bpp-contact-value">
                                    <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($phone) && !empty($visibility['phone'])) : ?>
                        <div class="bpp-contact-item">
                            <i class="dashicons dashicons-phone"></i>
                            <div class="bpp-contact-item-content">
                                <div class="bpp-contact-label"><?php _e('Phone', 'black-potential-pipeline'); ?></div>
                                <div class="bpp-contact-value">
                                    <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($resume_url) && !empty($visibility['resume'])) : ?>
                        <a href="<?php echo esc_url($resume_url); ?>" class="bpp-resume-button" target="_blank">
                            <i class="dashicons dashicons-pdf"></i>
                            <?php _e('Download Resume', 'black-potential-pipeline'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($contact_form_shortcode)) : ?>
                <div class="bpp-card">
                    <div class="bpp-card-header">
                        <h3><?php _e('Contact This Professional', 'black-potential-pipeline'); ?></h3>
                    </div>
                    <div class="bpp-card-body">
                        <?php echo do_shortcode($contact_form_shortcode); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Related Professionals Section -->
    <?php if ($related_query->have_posts()) : ?>
        <div class="bpp-card">
            <div class="bpp-card-header">
                <h3><?php _e('Related Professionals', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <div class="bpp-related-professionals">
                    <?php while ($related_query->have_posts()) : $related_query->the_post(); 
                        $related_id = get_the_ID();
                        $related_job_title = get_post_meta($related_id, 'bpp_job_title', true);
                        $related_industry_terms = wp_get_post_terms($related_id, 'bpp_industry', array('fields' => 'names'));
                        $related_industry = '';
                        if (!is_wp_error($related_industry_terms) && !empty($related_industry_terms)) {
                            $related_industry = $related_industry_terms[0];
                        }
                    ?>
                        <div class="bpp-card bpp-related-card">
                            <a href="<?php the_permalink(); ?>" class="bpp-card-link">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="bpp-related-img">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="bpp-related-img">
                                        <i class="dashicons dashicons-businessperson bpp-related-icon"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="bpp-card-body">
                                    <h4 class="bpp-profile-name"><?php the_title(); ?></h4>
                                    <?php if (!empty($related_job_title)) : ?>
                                        <p class="bpp-profile-title"><?php echo esc_html($related_job_title); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($related_industry)) : ?>
                                        <div class="bpp-tag bpp-industry-tag"><?php echo esc_html($related_industry); ?></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
    
    <!-- Back Button -->
    <div class="bpp-back-section">
        <a href="<?php echo esc_url(home_url('/professionals/')); ?>" class="bpp-back-button">
            <i class="dashicons dashicons-arrow-left-alt"></i>
            <?php _e('Back to All Professionals', 'black-potential-pipeline'); ?>
        </a>
    </div>
</div>

<?php get_footer(); ?> 