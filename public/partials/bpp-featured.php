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

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 text-center mb-4">
            <h2 class="fw-bold mb-2"><?php echo esc_html($title); ?></h2>
            <p class="lead text-muted">
                <?php echo esc_html__('Discover exceptional professionals ready to make an impact in green industries.', 'black-potential-pipeline'); ?>
            </p>
        </div>
    </div>
    
    <?php if ($featured_query->have_posts() || (isset($additional_query) && $additional_query->have_posts())) : ?>
        
        <?php if ($layout === 'carousel') : ?>
            <div class="position-relative mb-5">
                <div class="row">
                    <div class="col-12">
                        <div class="bpp-carousel-outer-wrapper">
                            <!-- Navigation Buttons -->
                            <button type="button" class="bpp-carousel-prev btn rounded-circle" aria-label="Previous">
                                <span class="dashicons dashicons-arrow-left-alt2"></span>
                            </button>
                            
                            <div class="bpp-carousel-wrapper">
                                <div class="bpp-carousel-container d-flex">
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
                                            <a href="<?php echo esc_url($profile_url); ?>" class="text-decoration-none text-dark">
                                                <div class="card h-100 shadow-sm rounded-3">
                                                    <?php if (has_post_thumbnail()) : ?>
                                                        <div class="card-img-top">
                                                            <?php the_post_thumbnail('medium', array('class' => 'img-fluid w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                                        </div>
                                                    <?php else : ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                            <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: var(--bpp-secondary-color);"></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="card-body">
                                                        <h5 class="card-title fw-bold mb-2"><?php the_title(); ?></h5>
                                                        <?php if (!empty($job_title)) : ?>
                                                            <p class="text-muted mb-2"><?php echo esc_html($job_title); ?></p>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($years_experience)) : ?>
                                                            <p class="small text-secondary mb-2">
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
                                                            <div class="badge bg-primary"><?php echo esc_html($industry); ?></div>
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
                                            <a href="<?php echo esc_url($profile_url); ?>" class="text-decoration-none text-dark">
                                                <div class="card h-100 shadow-sm rounded-3">
                                                    <?php if (has_post_thumbnail()) : ?>
                                                        <div class="card-img-top">
                                                            <?php the_post_thumbnail('medium', array('class' => 'img-fluid w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                                        </div>
                                                    <?php else : ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                            <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: var(--bpp-secondary-color);"></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="card-body">
                                                        <h5 class="card-title fw-bold mb-2"><?php the_title(); ?></h5>
                                                        <?php if (!empty($job_title)) : ?>
                                                            <p class="text-muted mb-2"><?php echo esc_html($job_title); ?></p>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($years_experience)) : ?>
                                                            <p class="small text-secondary mb-2">
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
                                                            <div class="badge bg-primary"><?php echo esc_html($industry); ?></div>
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
                            
                            <button type="button" class="bpp-carousel-next btn rounded-circle" aria-label="Next">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
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
                    <div class="col">
                        <a href="<?php echo esc_url($profile_url); ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm rounded-3">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="card-img-top">
                                        <?php the_post_thumbnail('medium', array('class' => 'img-fluid w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: var(--bpp-secondary-color);"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title fw-bold mb-2"><?php the_title(); ?></h5>
                                    <?php if (!empty($job_title)) : ?>
                                        <p class="text-muted mb-2"><?php echo esc_html($job_title); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($years_experience)) : ?>
                                        <p class="small text-secondary mb-2">
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
                                        <div class="badge bg-primary"><?php echo esc_html($industry); ?></div>
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
                    <div class="col">
                        <a href="<?php echo esc_url($profile_url); ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm rounded-3">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="card-img-top">
                                        <?php the_post_thumbnail('medium', array('class' => 'img-fluid w-100', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: var(--bpp-secondary-color);"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title fw-bold mb-2"><?php the_title(); ?></h5>
                                    <?php if (!empty($job_title)) : ?>
                                        <p class="text-muted mb-2"><?php echo esc_html($job_title); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($years_experience)) : ?>
                                        <p class="small text-secondary mb-2">
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
                                        <div class="badge bg-primary"><?php echo esc_html($industry); ?></div>
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
        <?php endif; ?>
        
    <?php else : ?>
        <div class="alert alert-info text-center">
            <p class="mb-0">
                <?php echo esc_html__('No featured professionals found.', 'black-potential-pipeline'); ?>
        </p>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div>

<?php if ($layout === 'carousel') : ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log('Carousel script initialized');
    
    // Explicitly select the elements
    const $carousel = $('.bpp-carousel-container');
    const $slides = $('.bpp-carousel-slide');
    const $prevBtn = $('.bpp-carousel-prev');
    const $nextBtn = $('.bpp-carousel-next');
    
    console.log('Found slides:', $slides.length);
    console.log('Prev button found:', $prevBtn.length);
    console.log('Next button found:', $nextBtn.length);
    
    // Exit if no slides
    if ($slides.length === 0) {
        console.log('No slides found, exiting');
        return;
    }
    
    let currentIndex = 0;
    let slidesToShow = 4;
    
    // Adjust slides to show based on window width
    function adjustSlidesToShow() {
        if (window.innerWidth < 768) {
            slidesToShow = 1;
        } else if (window.innerWidth < 992) {
            slidesToShow = 2;
        } else {
            slidesToShow = 4;
        }
        
        console.log('Slides to show:', slidesToShow);
        
        // Update slide widths
        const slideWidth = 100 / slidesToShow;
        $slides.css({
            'flex': '0 0 ' + slideWidth + '%',
            'max-width': slideWidth + '%',
            'padding': '0 10px'
        });
        
        // Reset position if needed
        const maxIndex = Math.max(0, $slides.length - slidesToShow);
        if (currentIndex > maxIndex) {
            currentIndex = maxIndex;
        }
        
        updateCarousel();
    }
    
    // Update carousel display
    function updateCarousel() {
        const slideWidth = 100 / slidesToShow;
        const translateX = -(currentIndex * slideWidth);
        
        console.log('Updating carousel, currentIndex:', currentIndex, 'translateX:', translateX);
        
        $carousel.css({
            'transform': 'translateX(' + translateX + '%)',
            'transition': 'transform 0.4s ease-in-out'
        });
        
        // Update button states
        const maxIndex = Math.max(0, $slides.length - slidesToShow);
        
        if (currentIndex <= 0) {
            $prevBtn.addClass('disabled').attr('disabled', 'disabled');
            console.log('Prev button disabled');
        } else {
            $prevBtn.removeClass('disabled').removeAttr('disabled');
            console.log('Prev button enabled');
        }
        
        if (currentIndex >= maxIndex) {
            $nextBtn.addClass('disabled').attr('disabled', 'disabled');
            console.log('Next button disabled');
        } else {
            $nextBtn.removeClass('disabled').removeAttr('disabled');
            console.log('Next button enabled');
        }
    }
    
    // Ensure initial setup
    adjustSlidesToShow();
    
    // Handle button clicks with direct click handler
    $prevBtn.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Prev button clicked');
        
        if (currentIndex > 0) {
            currentIndex--;
            console.log('Moving to index:', currentIndex);
            updateCarousel();
        } else {
            console.log('Already at first slide');
        }
    });
    
    $nextBtn.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Next button clicked');
        
        const maxIndex = Math.max(0, $slides.length - slidesToShow);
        if (currentIndex < maxIndex) {
            currentIndex++;
            console.log('Moving to index:', currentIndex);
            updateCarousel();
        } else {
            console.log('Already at last slide');
        }
    });
    
    // Touch/swipe support
    let startX = 0;
    let endX = 0;
    
    $carousel.on('touchstart', function(e) {
        startX = e.originalEvent.touches[0].clientX;
        console.log('Touch start:', startX);
    });
    
    $carousel.on('touchend', function(e) {
        endX = e.originalEvent.changedTouches[0].clientX;
        console.log('Touch end:', endX);
        
        const diff = startX - endX;
        console.log('Touch diff:', diff);
        
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                console.log('Swipe left detected');
                $nextBtn.trigger('click');
            } else {
                console.log('Swipe right detected');
                $prevBtn.trigger('click');
            }
        }
    });
    
    // Handle window resize
    $(window).on('resize', function() {
        adjustSlidesToShow();
    });
    
    // Prevent card hover during navigation
    $('.bpp-carousel-slide .card').hover(
        function() {
            $(this).addClass('card-hover');
        },
        function() {
            $(this).removeClass('card-hover');
        }
    );
    
    // Ensure buttons are clickable
    console.log('Setting up button click handlers');
    $prevBtn.css('pointer-events', 'auto');
    $nextBtn.css('pointer-events', 'auto');
});
</script>

<style type="text/css">
:root {
    --bpp-primary-color: var(--wp--preset--color--primary, #61CE70);
    --bpp-secondary-color: var(--wp--preset--color--secondary, #6F3802);
}

.bpp-carousel-outer-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    margin: 0 auto;
    width: 100%;
}

.bpp-carousel-wrapper {
    overflow: hidden;
    width: 100%;
    position: relative;
    padding: 0 10px;
}

.bpp-carousel-container {
    display: flex;
    transition: transform 0.4s ease-in-out;
    will-change: transform;
}

.bpp-carousel-slide {
    flex: 0 0 25%;
    max-width: 25%;
    padding: 0 10px;
    box-sizing: border-box;
}

.bpp-carousel-prev,
.bpp-carousel-next {
    flex: 0 0 48px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background-color: var(--bpp-primary-color, #61CE70) !important;
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 100;
    margin: 0 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    pointer-events: auto;
}

.bpp-carousel-prev:hover:not(.disabled),
.bpp-carousel-next:hover:not(.disabled) {
    background-color: #50b85c !important;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.bpp-carousel-prev.disabled,
.bpp-carousel-next.disabled {
    background-color: #ccc !important;
    cursor: not-allowed;
    opacity: 0.6;
}

.bpp-carousel-prev .dashicons,
.bpp-carousel-next .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    line-height: 1;
    display: inline-block;
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    height: 100%;
}

.card-hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
}

.bg-primary {
    background-color: var(--bpp-primary-color) !important;
}

.btn-primary {
    background-color: var(--bpp-primary-color) !important;
    border-color: var(--bpp-primary-color) !important;
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .bpp-carousel-slide {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 767px) {
    .bpp-carousel-slide {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .bpp-carousel-prev,
    .bpp-carousel-next {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
    }
}

@media (max-width: 480px) {
    .bpp-carousel-outer-wrapper {
        padding: 0 5px;
    }
}
</style>
<?php endif; ?> 