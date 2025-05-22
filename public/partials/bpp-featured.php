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
            <?php echo esc_html__('Discover exceptional Black professionals ready to make an impact in green industries.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <?php if ($featured_query->have_posts() || (isset($additional_query) && $additional_query->have_posts())) : ?>
        
        <?php if ($layout === 'carousel') : ?>
            <div class="bpp-featured-carousel">
                <div class="bpp-carousel-wrapper">
                    <div class="bpp-carousel-container">
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
                                <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                                    <div class="bpp-professional-card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="bpp-card-image">
                                                <?php the_post_thumbnail('medium', array('class' => 'w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="bpp-card-image" style="height: 200px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                                <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: #aaa;"></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="bpp-professional-content" style="padding: 1rem;">
                                            <h3 class="bpp-professional-name" style="margin: 0 0 0.5rem; font-size: 1.2rem; font-weight: 600;"><?php the_title(); ?></h3>
                                            <?php if (!empty($job_title)) : ?>
                                                <p class="bpp-professional-title" style="margin: 0 0 0.5rem; color: #666;"><?php echo esc_html($job_title); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($years_experience)) : ?>
                                                <p class="bpp-professional-experience" style="margin: 0 0 0.5rem; font-size: 0.9rem; color: #777;">
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
                                                <div class="bpp-professional-industry" style="display: inline-block; background: #0d6efd; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                                                    <?php echo esc_html($industry); ?>
                                                </div>
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
                                <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                                    <div class="bpp-professional-card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="bpp-card-image">
                                                <?php the_post_thumbnail('medium', array('class' => 'w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="bpp-card-image" style="height: 200px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                                <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: #aaa;"></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="bpp-professional-content" style="padding: 1rem;">
                                            <h3 class="bpp-professional-name" style="margin: 0 0 0.5rem; font-size: 1.2rem; font-weight: 600;"><?php the_title(); ?></h3>
                                            <?php if (!empty($job_title)) : ?>
                                                <p class="bpp-professional-title" style="margin: 0 0 0.5rem; color: #666;"><?php echo esc_html($job_title); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($years_experience)) : ?>
                                                <p class="bpp-professional-experience" style="margin: 0 0 0.5rem; font-size: 0.9rem; color: #777;">
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
                                                <div class="bpp-professional-industry" style="display: inline-block; background: #0d6efd; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                                                    <?php echo esc_html($industry); ?>
                                                </div>
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
                
                <div class="bpp-carousel-nav">
                    <button class="bpp-carousel-prev">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <button class="bpp-carousel-next">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
            </div>
        <?php else : ?>
            <div class="bpp-featured-grid bpp-layout-<?php echo esc_attr($layout); ?>">
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
                    <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                        <div class="bpp-professional-card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="bpp-card-image">
                                    <?php the_post_thumbnail('medium', array('class' => 'w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                </div>
                            <?php else : ?>
                                <div class="bpp-card-image" style="height: 200px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                    <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: #aaa;"></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="bpp-professional-content" style="padding: 1rem;">
                                <h3 class="bpp-professional-name" style="margin: 0 0 0.5rem; font-size: 1.2rem; font-weight: 600;"><?php the_title(); ?></h3>
                                <?php if (!empty($job_title)) : ?>
                                    <p class="bpp-professional-title" style="margin: 0 0 0.5rem; color: #666;"><?php echo esc_html($job_title); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($years_experience)) : ?>
                                    <p class="bpp-professional-experience" style="margin: 0 0 0.5rem; font-size: 0.9rem; color: #777;">
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
                                    <div class="bpp-professional-industry" style="display: inline-block; background: #0d6efd; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                                        <?php echo esc_html($industry); ?>
                                    </div>
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
                    <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                        <div class="bpp-professional-card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="bpp-card-image">
                                    <?php the_post_thumbnail('medium', array('class' => 'w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                </div>
                            <?php else : ?>
                                <div class="bpp-card-image" style="height: 200px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                    <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: #aaa;"></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="bpp-professional-content" style="padding: 1rem;">
                                <h3 class="bpp-professional-name" style="margin: 0 0 0.5rem; font-size: 1.2rem; font-weight: 600;"><?php the_title(); ?></h3>
                                <?php if (!empty($job_title)) : ?>
                                    <p class="bpp-professional-title" style="margin: 0 0 0.5rem; color: #666;"><?php echo esc_html($job_title); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($years_experience)) : ?>
                                    <p class="bpp-professional-experience" style="margin: 0 0 0.5rem; font-size: 0.9rem; color: #777;">
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
                                    <div class="bpp-professional-industry" style="display: inline-block; background: #0d6efd; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                                        <?php echo esc_html($industry); ?>
                                    </div>
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
        
    <?php else : ?>
        <div class="bpp-no-results">
            <p><?php echo esc_html__('No featured professionals found.', 'black-potential-pipeline'); ?></p>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div>

<?php if ($layout === 'carousel') : ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    const $carousel = $('.bpp-carousel-container');
    const $slides = $('.bpp-carousel-slide');
    const $prevBtn = $('.bpp-carousel-prev');
    const $nextBtn = $('.bpp-carousel-next');
    
    let currentIndex = 0;
    let slidesToShow = 3;
    
    // Adjust slides to show based on window width
    function adjustSlidesToShow() {
        if (window.innerWidth < 768) {
            slidesToShow = 1;
        } else if (window.innerWidth < 992) {
            slidesToShow = 2;
        } else {
            slidesToShow = 3;
        }
        
        // Update slide widths and positioning
        const slideWidth = 100 / slidesToShow;
        $slides.css('width', slideWidth + '%');
        
        // Reset position if needed
        if (currentIndex > $slides.length - slidesToShow) {
            currentIndex = Math.max(0, $slides.length - slidesToShow);
        }
        
        updateCarousel();
    }
    
    // Update carousel display
    function updateCarousel() {
        const slideWidth = 100 / slidesToShow;
        $carousel.css('transform', `translateX(-${currentIndex * slideWidth}%)`);
        $carousel.css('transition', 'transform 0.4s ease');
        
        // Enable/disable buttons
        $prevBtn.prop('disabled', currentIndex === 0);
        $nextBtn.prop('disabled', currentIndex >= $slides.length - slidesToShow);
    }
    
    // Initialize
    adjustSlidesToShow();
    
    // Navigation buttons
    $prevBtn.on('click', function() {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    });
    
    $nextBtn.on('click', function() {
        if (currentIndex < $slides.length - slidesToShow) {
            currentIndex++;
            updateCarousel();
        }
    });
    
    // Add swipe support for mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    $carousel.on('touchstart', function(e) {
        touchStartX = e.originalEvent.touches[0].clientX;
    });
    
    $carousel.on('touchend', function(e) {
        touchEndX = e.originalEvent.changedTouches[0].clientX;
        handleSwipe();
    });
    
    function handleSwipe() {
        if (touchStartX - touchEndX > 50) {
            // Swipe left - go to next slide
            $nextBtn.trigger('click');
        }
        
        if (touchEndX - touchStartX > 50) {
            // Swipe right - go to previous slide
            $prevBtn.trigger('click');
        }
    }
    
    // Add hover effect to cards
    $('.bpp-professional-card').hover(
        function() {
            $(this).css({
                'transform': 'translateY(-5px)',
                'box-shadow': '0 10px 20px rgba(0,0,0,0.1)'
            });
        },
        function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': '0 2px 10px rgba(0,0,0,0.1)'
            });
        }
    );
    
    // Responsive
    $(window).on('resize', adjustSlidesToShow);
});
</script>

<style type="text/css">
.bpp-featured-carousel {
    position: relative;
    padding: 0 1rem;
    margin-bottom: 2rem;
}

.bpp-carousel-wrapper {
    overflow: hidden;
}

.bpp-carousel-container {
    display: flex;
    transition: transform 0.4s ease;
}

.bpp-carousel-slide {
    flex: 0 0 auto;
    padding: 0 10px;
}

.bpp-carousel-nav {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

.bpp-carousel-prev,
.bpp-carousel-next {
    background: #0d6efd;
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 0.5rem;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.bpp-carousel-prev:hover,
.bpp-carousel-next:hover {
    background: #0b5ed7;
}

.bpp-carousel-prev:disabled,
.bpp-carousel-next:disabled {
    background: #ccc;
    cursor: not-allowed;
}
</style>
<?php endif; ?> 