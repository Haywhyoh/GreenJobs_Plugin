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
                            $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                            $industry = '';
                            if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                                $industry = $industry_terms[0];
                            }
                            $profile_url = get_permalink();
                        ?>
                            <div class="bpp-carousel-slide">
                                <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                                    <div class="bpp-professional-card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;">
                                        <div class="bpp-professional-content text-center">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="bpp-professional-photo mb-3 text-center">
                                                    <?php the_post_thumbnail('thumbnail', array('class' => 'mx-auto d-block', 'style' => 'width: 120px; height: 120px; object-fit: cover;')); ?>
                                                </div>
                                            <?php else : ?>
                                                <div class="bpp-professional-photo bpp-no-photo mb-3 text-center">
                                                    <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px;"></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="bpp-professional-info">
                                                <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                                <?php if (!empty($job_title)) : ?>
                                                    <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($industry)) : ?>
                                                    <p class="bpp-professional-industry"><?php echo esc_html($industry); ?></p>
                                                <?php endif; ?>
                                            </div>
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
                                $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                                $industry = '';
                                if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                                    $industry = $industry_terms[0];
                                }
                                $profile_url = get_permalink();
                        ?>
                            <div class="bpp-carousel-slide">
                                <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                                    <div class="bpp-professional-card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;">
                                        <div class="bpp-professional-content text-center">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="bpp-professional-photo mb-3 text-center">
                                                    <?php the_post_thumbnail('thumbnail', array('class' => 'mx-auto d-block', 'style' => 'width: 120px; height: 120px; object-fit: cover;')); ?>
                                                </div>
                                            <?php else : ?>
                                                <div class="bpp-professional-photo bpp-no-photo mb-3 text-center">
                                                    <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px;"></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="bpp-professional-info">
                                                <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                                <?php if (!empty($job_title)) : ?>
                                                    <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                                <?php endif; ?>
                                                <?php if (!empty($industry)) : ?>
                                                    <p class="bpp-professional-industry"><?php echo esc_html($industry); ?></p>
                                                <?php endif; ?>
                                            </div>
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
                    $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                    $industry = '';
                    if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                        $industry = $industry_terms[0];
                    }
                    $profile_url = get_permalink();
                ?>
                    <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                        <div class="bpp-professional-card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;">
                            <div class="bpp-professional-content text-center">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="bpp-professional-photo mb-3 text-center">
                                        <?php the_post_thumbnail('thumbnail', array('class' => 'mx-auto d-block', 'style' => 'width: 120px; height: 120px; object-fit: cover;')); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="bpp-professional-photo bpp-no-photo mb-3 text-center">
                                        <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px;"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="bpp-professional-info">
                                    <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                    <?php if (!empty($job_title)) : ?>
                                        <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($industry)) : ?>
                                        <p class="bpp-professional-industry"><?php echo esc_html($industry); ?></p>
                                    <?php endif; ?>
                                </div>
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
                        $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                        $industry = '';
                        if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                            $industry = $industry_terms[0];
                        }
                        $profile_url = get_permalink();
                ?>
                    <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                        <div class="bpp-professional-card" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;">
                            <div class="bpp-professional-content text-center">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="bpp-professional-photo mb-3 text-center">
                                        <?php the_post_thumbnail('thumbnail', array('class' => 'mx-auto d-block', 'style' => 'width: 120px; height: 120px; object-fit: cover;')); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="bpp-professional-photo bpp-no-photo mb-3 text-center">
                                        <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px;"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="bpp-professional-info">
                                    <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                    <?php if (!empty($job_title)) : ?>
                                        <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($industry)) : ?>
                                        <p class="bpp-professional-industry"><?php echo esc_html($industry); ?></p>
                                    <?php endif; ?>
                                </div>
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
        
        updateCarousel();
    }
    
    // Update carousel display
    function updateCarousel() {
        const slideWidth = 100 / slidesToShow;
        $slides.css('width', slideWidth + '%');
        $carousel.css('transform', `translateX(-${currentIndex * slideWidth}%)`);
        
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
    
    // Responsive
    $(window).on('resize', adjustSlidesToShow);
});
</script>
<?php endif; ?> 