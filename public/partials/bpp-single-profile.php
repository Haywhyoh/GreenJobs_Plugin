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

// Get the current post ID
$post_id = get_the_ID();

// Get applicant metadata
$job_title = get_post_meta($post_id, 'bpp_job_title', true);
$location = get_post_meta($post_id, 'bpp_location', true);
$years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
$skills = get_post_meta($post_id, 'bpp_skills', true);
$skills_array = !empty($skills) ? explode(',', $skills) : array();
$bio = get_post_meta($post_id, 'bpp_bio', true);
$website = get_post_meta($post_id, 'bpp_website', true);
$linkedin = get_post_meta($post_id, 'bpp_linkedin', true);
$email = get_post_meta($post_id, 'bpp_email', true);
$phone = get_post_meta($post_id, 'bpp_phone', true);
$resume_id = get_post_meta($post_id, 'bpp_resume', true);
$resume_url = !empty($resume_id) ? wp_get_attachment_url($resume_id) : '';
$featured = (bool) get_post_meta($post_id, 'bpp_featured', true);

// Get industry from taxonomy
$industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
$industry = '';
if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
    $industry = $industry_terms[0];
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
    'tax_query' => array(
        array(
            'taxonomy' => 'bpp_industry',
            'field' => 'name',
            'terms' => $industry,
        ),
    ),
    'meta_query' => array(
        array(
            'key' => 'bpp_approved',
            'value' => '1',
            'compare' => '=',
        ),
    ),
);

$related_query = new WP_Query($related_args);

// Get contact form shortcode if available
$contact_form_shortcode = get_option('bpp_profile_contact_form', '');
?>

<div class="bpp-single-profile-container">
    <div class="bpp-profile-header">
        <?php if ($featured) : ?>
            <div class="bpp-featured-badge">
                <span class="dashicons dashicons-star-filled"></span>
                <?php _e('Featured Professional', 'black-potential-pipeline'); ?>
            </div>
        <?php endif; ?>
        
        <div class="bpp-profile-main-info">
            <?php if (!empty($visibility['photo'])) : ?>
            <div class="bpp-profile-photo-container">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="bpp-profile-photo">
                        <?php the_post_thumbnail('medium'); ?>
                    </div>
                <?php else : ?>
                    <div class="bpp-profile-photo bpp-no-photo">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="bpp-profile-identity">
                <h1 class="bpp-profile-name"><?php the_title(); ?></h1>
                
                <?php if (!empty($job_title) && !empty($visibility['job_title'])) : ?>
                    <h2 class="bpp-profile-job-title"><?php echo esc_html($job_title); ?></h2>
                <?php endif; ?>
                
                <div class="bpp-profile-meta">
                    <?php if (!empty($industry) && !empty($visibility['industry'])) : ?>
                        <div class="bpp-profile-industry">
                            <span class="dashicons dashicons-category"></span>
                            <?php echo esc_html($industry); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($location) && !empty($visibility['location'])) : ?>
                        <div class="bpp-profile-location">
                            <span class="dashicons dashicons-location"></span>
                            <?php echo esc_html($location); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($years_experience) && !empty($visibility['years_experience'])) : ?>
                        <div class="bpp-profile-experience">
                            <span class="dashicons dashicons-businessman"></span>
                            <?php printf(_n('%s year experience', '%s years experience', (int)$years_experience, 'black-potential-pipeline'), esc_html($years_experience)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ((!empty($website) && !empty($visibility['website'])) || (!empty($linkedin) && !empty($visibility['linkedin']))) : ?>
                    <div class="bpp-profile-social">
                        <?php if (!empty($website) && !empty($visibility['website'])) : ?>
                            <a href="<?php echo esc_url($website); ?>" class="bpp-social-link bpp-website-link" target="_blank">
                                <span class="dashicons dashicons-admin-site-alt3"></span>
                                <?php _e('Website', 'black-potential-pipeline'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($linkedin) && !empty($visibility['linkedin'])) : ?>
                            <a href="<?php echo esc_url($linkedin); ?>" class="bpp-social-link bpp-linkedin-link" target="_blank">
                                <span class="dashicons dashicons-linkedin"></span>
                                <?php _e('LinkedIn', 'black-potential-pipeline'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="bpp-profile-content">
        <div class="bpp-profile-main">
            <?php if (!empty($bio) && !empty($visibility['bio'])) : ?>
                <div class="bpp-profile-section bpp-profile-bio">
                    <h3><?php _e('Professional Bio', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-profile-bio-content">
                        <?php echo wpautop(esc_html($bio)); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($skills_array) && !empty($visibility['skills'])) : ?>
                <div class="bpp-profile-section bpp-profile-skills">
                    <h3><?php _e('Skills & Expertise', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-skills-tags">
                        <?php foreach ($skills_array as $skill) : ?>
                            <span class="bpp-skill-tag"><?php echo esc_html(trim($skill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($resume_url) && !empty($visibility['resume'])) : ?>
                <div class="bpp-profile-section bpp-profile-resume">
                    <h3><?php _e('Resume/CV', 'black-potential-pipeline'); ?></h3>
                    <a href="<?php echo esc_url($resume_url); ?>" class="bpp-resume-download bpp-button" target="_blank">
                        <span class="dashicons dashicons-pdf"></span>
                        <?php _e('Download Resume (PDF)', 'black-potential-pipeline'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bpp-profile-sidebar">
            <?php if ((!empty($email) && !empty($visibility['email'])) || (!empty($phone) && !empty($visibility['phone']))) : ?>
                <div class="bpp-profile-section bpp-profile-contact">
                    <h3><?php _e('Contact Information', 'black-potential-pipeline'); ?></h3>
                    
                    <?php if (!empty($email) && !empty($visibility['email'])) : ?>
                        <div class="bpp-contact-item bpp-contact-email">
                            <span class="dashicons dashicons-email-alt"></span>
                            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($phone) && !empty($visibility['phone'])) : ?>
                        <div class="bpp-contact-item bpp-contact-phone">
                            <span class="dashicons dashicons-phone"></span>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($contact_form_shortcode)) : ?>
                <div class="bpp-profile-section bpp-profile-contact-form">
                    <h3><?php _e('Contact This Professional', 'black-potential-pipeline'); ?></h3>
                    <?php echo do_shortcode($contact_form_shortcode); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($related_query->have_posts()) : ?>
        <div class="bpp-related-profiles">
            <h3><?php _e('Related Professionals', 'black-potential-pipeline'); ?></h3>
            <div class="bpp-related-profiles-grid">
                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                    <div class="bpp-related-profile">
                        <a href="<?php the_permalink(); ?>" class="bpp-related-profile-link">
                            <div class="bpp-related-profile-photo">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                <?php else : ?>
                                    <span class="dashicons dashicons-admin-users"></span>
                                <?php endif; ?>
                            </div>
                            <h4 class="bpp-related-profile-name"><?php the_title(); ?></h4>
                            <?php 
                            $related_job_title = get_post_meta(get_the_ID(), 'bpp_job_title', true);
                            if (!empty($related_job_title)) : 
                            ?>
                                <div class="bpp-related-profile-job"><?php echo esc_html($related_job_title); ?></div>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</div> 