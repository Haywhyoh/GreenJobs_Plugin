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
                    <div class="col-12 position-relative">
                        <div class="overflow-hidden px-2">
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
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-center">
                        <div class="bpp-carousel-nav">
                            <button type="button" class="bpp-carousel-prev btn btn-primary rounded-circle me-3" aria-label="Previous">
                                <span class="dashicons dashicons-arrow-left-alt2"></span>
                            </button>
                            <button type="button" class="bpp-carousel-next btn btn-primary rounded-circle" aria-label="Next">
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
    const $carousel = $('.bpp-carousel-container');
    const $slides = $('.bpp-carousel-slide');
    const $prevBtn = $('.bpp-carousel-prev');
    const $nextBtn = $('.bpp-carousel-next');
    
    let currentIndex = 0;
    let slidesToShow = 4; // Change to 4 items on large screens to match category-featured
    
    // Adjust slides to show based on window width
    function adjustSlidesToShow() {
        if (window.innerWidth < 768) {
            slidesToShow = 1;
        } else if (window.innerWidth < 992) {
            slidesToShow = 2;
        } else {
            slidesToShow = 4; // 4 items on large screens to match category-featured
        }
        
        // Update slide widths and positioning
        const slideWidth = 100 / slidesToShow;
        $slides.css('width', slideWidth + '%');
        $slides.css('padding', '0 10px');
        
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
        
        // Enable/disable buttons based on current position
        if (currentIndex === 0) {
            $prevBtn.prop('disabled', true).addClass('btn-secondary').removeClass('btn-primary');
        } else {
            $prevBtn.prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
        }
        
        if (currentIndex >= $slides.length - slidesToShow) {
            $nextBtn.prop('disabled', true).addClass('btn-secondary').removeClass('btn-primary');
        } else {
            $nextBtn.prop('disabled', false).addClass('btn-primary').removeClass('btn-secondary');
        }
    }
    
    // Initialize
    adjustSlidesToShow();
    
    // Navigation buttons with explicit click handling
    $prevBtn.on('click', function(e) {
        e.preventDefault();
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    });
    
    $nextBtn.on('click', function(e) {
        e.preventDefault();
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
    $('.card').hover(
        function() {
            $(this).css({
                'transform': 'translateY(-5px)',
                'box-shadow': '0 10px 20px rgba(0,0,0,0.1)'
            });
        },
        function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': '0 4px 6px rgba(0,0,0,0.1)'
            });
        }
    );
    
    // Responsive
    $(window).on('resize', adjustSlidesToShow);
    
    // Run initial update to correctly set button states
    updateCarousel();
    
    // Ensure buttons are clickable by explicitly setting z-index
    $prevBtn.css('z-index', '1000');
    $nextBtn.css('z-index', '1000');
});
</script>

<style type="text/css">
:root {
    --bpp-primary-color: var(--wp--preset--color--primary, #61CE70);
    --bpp-secondary-color: var(--wp--preset--color--secondary, #6F3802);
}

.bpp-carousel-container {
    transition: transform 0.4s ease;
}

.bpp-carousel-slide {
    flex: 0 0 auto;
    padding: 0 10px;
}

.bpp-carousel-nav {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 1rem;
    position: relative;
    z-index: 100;
}

.bpp-carousel-prev,
.bpp-carousel-next {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    z-index: 100;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.bpp-carousel-prev .dashicons,
.bpp-carousel-next .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    line-height: 1;
}

.bpp-carousel-prev:disabled,
.bpp-carousel-next:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.bg-primary {
    background-color: var(--bpp-primary-color) !important;
}

.btn-primary {
    background-color: var(--bpp-primary-color) !important;
    border-color: var(--bpp-primary-color) !important;
}

.btn-primary:hover {
    background-color: #50b85c !important;
    border-color: #50b85c !important;
}

.btn-secondary {
    background-color: #ced4da !important;
    border-color: #ced4da !important;
}

.card {
    transition: transform 0.3s, box-shadow 0.3s;
    border: none;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

/* Position navigation buttons outside for better visibility */
@media (min-width: 768px) {
    .position-relative {
        padding: 0 60px;
    }
    
    .bpp-carousel-nav {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        transform: translateY(-100%);
        justify-content: space-between;
        padding: 0 15px;
        margin-top: 0;
    }
}

/* Bootstrap responsive adjustments */
@media (min-width: 992px) {
    .bpp-carousel-slide {
        width: 25%; /* 4 items per row on large screens */
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .bpp-carousel-slide {
        width: 50%; /* 2 items per row on medium screens */
    }
}

@media (max-width: 767px) {
    .bpp-carousel-slide {
        width: 100%; /* 1 item per row on small screens */
    }
    
    .bpp-carousel-prev,
    .bpp-carousel-next {
        width: 40px;
        height: 40px;
    }
    
    .position-relative {
        padding: 0 15px;
    }
}
</style>
<?php endif; ?> 