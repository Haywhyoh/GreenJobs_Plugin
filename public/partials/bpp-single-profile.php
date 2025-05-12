<?php
/**
 * Provide a public-facing view for a single professional's profile
 *
 * This file displays the full profile for an individual professional
 *
 * @link       https://example.com
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
$resume_url = get_post_meta($post_id, 'bpp_resume_url', true);
$featured = (bool) get_post_meta($post_id, 'bpp_featured', true);

// Get industry from taxonomy
$industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
$industry = !empty($industry_terms) ? $industry_terms[0] : '';

// Get related professionals in the same industry (limit to 3)
$related_args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'post__not_in' => array($post_id),
    'orderby' => 'rand',
);

if (!empty($industry_terms)) {
    $related_args['tax_query'] = array(
        array(
            'taxonomy' => 'bpp_industry',
            'field' => 'names',
            'terms' => $industry_terms,
        ),
    );
}

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
            <div class="bpp-profile-photo-container">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="bpp-profile-photo">
                        <?php the_post_thumbnail('medium'); ?>
                    </div>
                <?php else : ?>
                    <div class="bpp-profile-photo bpp-no-photo">
                        <span class="dashicons dashicons-businessperson"></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="bpp-profile-identity">
                <h1 class="bpp-profile-name"><?php the_title(); ?></h1>
                
                <?php if (!empty($job_title)) : ?>
                    <h2 class="bpp-profile-job-title"><?php echo esc_html($job_title); ?></h2>
                <?php endif; ?>
                
                <div class="bpp-profile-meta">
                    <?php if (!empty($industry)) : ?>
                        <div class="bpp-profile-industry">
                            <span class="dashicons dashicons-category"></span>
                            <?php echo esc_html($industry); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($location)) : ?>
                        <div class="bpp-profile-location">
                            <span class="dashicons dashicons-location"></span>
                            <?php echo esc_html($location); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($years_experience)) : ?>
                        <div class="bpp-profile-experience">
                            <span class="dashicons dashicons-businessman"></span>
                            <?php printf(_n('%s year experience', '%s years experience', (int)$years_experience, 'black-potential-pipeline'), esc_html($years_experience)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($website) || !empty($linkedin)) : ?>
                    <div class="bpp-profile-social">
                        <?php if (!empty($website)) : ?>
                            <a href="<?php echo esc_url($website); ?>" class="bpp-social-link bpp-website-link" target="_blank">
                                <span class="dashicons dashicons-admin-site-alt3"></span>
                                <?php _e('Website', 'black-potential-pipeline'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($linkedin)) : ?>
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
    
    <div class="bpp-profile-body">
        <div class="bpp-profile-main-content">
            <?php if (!empty($bio)) : ?>
                <div class="bpp-profile-section bpp-profile-bio">
                    <h3><?php _e('Professional Bio', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-profile-bio-content">
                        <?php echo wpautop(esc_html($bio)); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($skills_array)) : ?>
                <div class="bpp-profile-section bpp-profile-skills">
                    <h3><?php _e('Skills & Expertise', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-skills-tags">
                        <?php foreach ($skills_array as $skill) : ?>
                            <span class="bpp-skill-tag"><?php echo esc_html(trim($skill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($resume_url)) : ?>
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
            <div class="bpp-profile-section bpp-profile-contact">
                <h3><?php _e('Contact Information', 'black-potential-pipeline'); ?></h3>
                
                <div class="bpp-contact-info">
                    <?php if (!empty($email)) : ?>
                        <div class="bpp-contact-item">
                            <span class="dashicons dashicons-email"></span>
                            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($phone)) : ?>
                        <div class="bpp-contact-item">
                            <span class="dashicons dashicons-phone"></span>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($contact_form_shortcode)) : ?>
                <div class="bpp-profile-section bpp-profile-contact-form">
                    <h3><?php _e('Contact This Professional', 'black-potential-pipeline'); ?></h3>
                    <?php echo do_shortcode($contact_form_shortcode); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($related_query->have_posts()) : ?>
        <div class="bpp-profile-related">
            <h3><?php _e('Similar Professionals', 'black-potential-pipeline'); ?></h3>
            
            <div class="bpp-related-profiles">
                <?php 
                while ($related_query->have_posts()) : $related_query->the_post();
                    $related_id = get_the_ID();
                    $related_job_title = get_post_meta($related_id, 'bpp_job_title', true);
                    
                    // Get industry from taxonomy for related professional
                    $related_industry_terms = wp_get_post_terms($related_id, 'bpp_industry', array('fields' => 'names'));
                    $related_industry = !empty($related_industry_terms) ? $related_industry_terms[0] : '';
                ?>
                    <div class="bpp-related-profile-card">
                        <div class="bpp-related-profile-header">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="bpp-related-profile-photo">
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                </div>
                            <?php else : ?>
                                <div class="bpp-related-profile-photo bpp-no-photo">
                                    <span class="dashicons dashicons-businessperson"></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="bpp-related-profile-info">
                                <h4 class="bpp-related-profile-name">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h4>
                                
                                <?php if (!empty($related_job_title)) : ?>
                                    <p class="bpp-related-profile-title"><?php echo esc_html($related_job_title); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($related_industry)) : ?>
                                    <p class="bpp-related-profile-industry"><?php echo esc_html($related_industry); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="bpp-related-profile-footer">
                            <a href="<?php the_permalink(); ?>" class="bpp-view-profile">
                                <?php _e('View Profile', 'black-potential-pipeline'); ?>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="bpp-profile-actions">
        <a href="javascript:history.back();" class="bpp-back-button">
            <span class="dashicons dashicons-arrow-left-alt"></span>
            <?php _e('Back to Directory', 'black-potential-pipeline'); ?>
        </a>
    </div>
    
    <?php wp_reset_postdata(); ?>
</div> 