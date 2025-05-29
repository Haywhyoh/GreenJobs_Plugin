<?php
/**
 * Provide a public-facing view for featured professionals
 *
 * This file is used to markup the public-facing aspects of the plugin.
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

// Extract shortcode attributes
$title = sanitize_text_field($atts['title']);
$count = intval($atts['count']);
$layout = sanitize_text_field($atts['layout']);

// Default industry names lookup array for formatting industry slugs
$default_industry_names = array(
    'nature-based-work' => __('Nature-based work', 'black-potential-pipeline'),
    'environmental-policy' => __('Environmental policy', 'black-potential-pipeline'),
    'climate-science' => __('Climate science', 'black-potential-pipeline'),
    'green-construction' => __('Green construction & infrastructure', 'black-potential-pipeline'),
);

// Get featured applicants query
$args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => $count,
    'orderby' => 'rand', // Random order for featured candidates
    'meta_query' => array(
        array(
            'key' => 'bpp_featured',
            'value' => '1',
            'compare' => '=',
        ),
    ),
);

$featured_query = new WP_Query($args);

// If not enough featured candidates, get recently approved ones
if ($featured_query->post_count < $count) {
    $args = array(
        'post_type' => 'bpp_applicant',
        'post_status' => 'publish',
        'posts_per_page' => $count - $featured_query->post_count,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => 'bpp_featured',
                'value' => '1',
                'compare' => '!=',
                'type' => 'NUMERIC',
            ),
        ),
    );
    
    $additional_query = new WP_Query($args);
}
?>

<div class="bpp-featured-container">
    <div class="bpp-featured-header">
        <h2 class="bpp-featured-title"><?php echo esc_html($title); ?></h2>
        <p class="bpp-featured-description">
            <?php echo esc_html__('Discover exceptional professionals ready to make an impact in green industries.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <?php if ($featured_query->have_posts() || (isset($additional_query) && $additional_query->have_posts())) : ?>
        
        <?php if ($layout === 'carousel') : ?>
            <div class="bpp-carousel-container">
                <div class="bpp-carousel-wrapper">
                    <div class="bpp-carousel-track">
                        <?php 
                        // Output featured candidates first
                        while ($featured_query->have_posts()) : $featured_query->the_post();
                            $post_id = get_the_ID();
                            $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                            $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
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
                            
                            $profile_url = get_permalink();
                        ?>
                            <div class="bpp-carousel-slide">
                                <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link">
                                    <div class="bpp-professional-card">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="bpp-professional-photo">
                                                <?php the_post_thumbnail('medium'); ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="bpp-professional-photo bpp-no-photo">
                                                <span class="dashicons dashicons-businessperson"></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="bpp-professional-content">
                                            <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                            <?php if (!empty($job_title)) : ?>
                                                <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($years_experience)) : ?>
                                                <p class="bpp-experience">
                                                    <?php 
                                                    // Display experience in a readable format
                                                    if (is_numeric($years_experience)) {
                                                        echo sprintf(_n('%d year experience', '%d years experience', $years_experience, 'black-potential-pipeline'), $years_experience);
                                                    } else {
                                                        echo esc_html($years_experience) . ' ' . esc_html__('experience', 'black-potential-pipeline');
                                                    }
                                                    ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($industry)) : ?>
                                                <div class="bpp-industry-tag"><?php echo esc_html($industry); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                        
                        <?php 
                        // Output additional candidates if needed
                        if (isset($additional_query) && $additional_query->have_posts()) :
                            while ($additional_query->have_posts()) : $additional_query->the_post();
                                $post_id = get_the_ID();
                                $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                                $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
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
                                
                                $profile_url = get_permalink();
                        ?>
                            <div class="bpp-carousel-slide">
                                <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link">
                                    <div class="bpp-professional-card">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="bpp-professional-photo">
                                                <?php the_post_thumbnail('medium'); ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="bpp-professional-photo bpp-no-photo">
                                                <span class="dashicons dashicons-businessperson"></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="bpp-professional-content">
                                            <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                            <?php if (!empty($job_title)) : ?>
                                                <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($years_experience)) : ?>
                                                <p class="bpp-experience">
                                                    <?php 
                                                    // Display experience in a readable format
                                                    if (is_numeric($years_experience)) {
                                                        echo sprintf(_n('%d year experience', '%d years experience', $years_experience, 'black-potential-pipeline'), $years_experience);
                                                    } else {
                                                        echo esc_html($years_experience) . ' ' . esc_html__('experience', 'black-potential-pipeline');
                                                    }
                                                    ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($industry)) : ?>
                                                <div class="bpp-industry-tag"><?php echo esc_html($industry); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php 
                            endwhile; 
                        endif;
                        ?>
                    </div>
                </div>
                
                <div class="bpp-carousel-buttons">
                    <button type="button" class="bpp-carousel-prev" aria-label="Previous">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <button type="button" class="bpp-carousel-next" aria-label="Next">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
            </div>
        <?php else : ?>
            <div class="bpp-featured-grid">
                <?php 
                // Output featured candidates first
                while ($featured_query->have_posts()) : $featured_query->the_post();
                    $post_id = get_the_ID();
                    $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                    $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
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
                    
                    $profile_url = get_permalink();
                ?>
                    <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link">
                        <div class="bpp-professional-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="bpp-professional-photo">
                                    <?php the_post_thumbnail('medium'); ?>
                                </div>
                            <?php else : ?>
                                <div class="bpp-professional-photo bpp-no-photo">
                                    <span class="dashicons dashicons-businessperson"></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="bpp-professional-content">
                                <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                <?php if (!empty($job_title)) : ?>
                                    <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($years_experience)) : ?>
                                    <p class="bpp-experience">
                                        <?php 
                                        // Display experience in a readable format
                                        if (is_numeric($years_experience)) {
                                            echo sprintf(_n('%d year experience', '%d years experience', $years_experience, 'black-potential-pipeline'), $years_experience);
                                        } else {
                                            echo esc_html($years_experience) . ' ' . esc_html__('experience', 'black-potential-pipeline');
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($industry)) : ?>
                                    <div class="bpp-industry-tag"><?php echo esc_html($industry); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
                
                <?php 
                // Output additional candidates if needed
                if (isset($additional_query) && $additional_query->have_posts()) :
                    while ($additional_query->have_posts()) : $additional_query->the_post();
                        $post_id = get_the_ID();
                        $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                        $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
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
                        
                        $profile_url = get_permalink();
                ?>
                    <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link">
                        <div class="bpp-professional-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="bpp-professional-photo">
                                    <?php the_post_thumbnail('medium'); ?>
                                </div>
                            <?php else : ?>
                                <div class="bpp-professional-photo bpp-no-photo">
                                    <span class="dashicons dashicons-businessperson"></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="bpp-professional-content">
                                <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                <?php if (!empty($job_title)) : ?>
                                    <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($years_experience)) : ?>
                                    <p class="bpp-experience">
                                        <?php 
                                        // Display experience in a readable format
                                        if (is_numeric($years_experience)) {
                                            echo sprintf(_n('%d year experience', '%d years experience', $years_experience, 'black-potential-pipeline'), $years_experience);
                                        } else {
                                            echo esc_html($years_experience) . ' ' . esc_html__('experience', 'black-potential-pipeline');
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($industry)) : ?>
                                    <div class="bpp-industry-tag"><?php echo esc_html($industry); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php 
                    endwhile; 
                endif;
                ?>
            </div>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
        
    <?php else : ?>
        <div class="bpp-no-results">
            <p><?php _e('No featured professionals found.', 'black-potential-pipeline'); ?></p>
        </div>
    <?php endif; ?>
</div> 

<!-- Script to reinitialize carousel on page visibility change -->
<script type="text/javascript">
(function($) {
    // Handle page visibility changes (when user switches tabs and comes back)
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible' && typeof window.bppInitCarousel === 'function') {
            setTimeout(function() {
                window.bppInitCarousel();
            }, 100);
        }
    });
    
    // Additional initialization for handling history navigation
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
        // Page was loaded from history (back/forward)
        if (typeof window.bppInitCarousel === 'function') {
            setTimeout(function() {
                window.bppInitCarousel();
            }, 200);
        }
    }
})(jQuery);
</script> 