<?php
/**
 * Provide a public-facing view for showcasing applicants from a specific category in a carousel
 *
 * This file is used to markup the public-facing category carousel.
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
$category = sanitize_text_field($atts['category']);
$title = !empty($atts['title']) ? sanitize_text_field($atts['title']) : sprintf(__('Black Professionals in %s', 'black-potential-pipeline'), ucwords(str_replace('-', ' ', $category)));
$items_per_slide = isset($atts['items_per_slide']) ? intval($atts['items_per_slide']) : 4; // Number of items per slide on large screens
$total_items = isset($atts['count']) ? intval($atts['count']) : 12; // Total items to display
$use_bootstrap = isset($atts['use_bootstrap']) ? ($atts['use_bootstrap'] === 'yes') : true;

// Debug log
error_log("BPP CATEGORY FEATURED: Processing category {$category}");

// Get term information
$term = get_term_by('slug', $category, 'bpp_industry');
if (!$term || is_wp_error($term)) {
    $term = get_term_by('name', $category, 'bpp_industry');
}
$term_slug = $term ? $term->slug : $category;
$term_name = $term ? $term->name : '';

// Create a comprehensive query that checks for the category in all possible places
$args = array(
    'post_type'      => 'bpp_applicant',
    'post_status'    => 'publish',
    'posts_per_page' => $total_items,
    'orderby'        => 'meta_value date',
    'meta_key'       => 'bpp_featured',
    'order'          => 'DESC',
);

// Build tax query for the category
$tax_meta_query = array(
    'relation' => 'OR',
    // Query 1: Taxonomy term by slug
    array(
        'taxonomy' => 'bpp_industry',
        'field'    => 'slug',
        'terms'    => $term_slug,
    )
);

// Add meta query options for various ways the category might be stored
if (!empty($term_name)) {
    $tax_meta_query[] = array(
        'key'     => 'bpp_industry',
        'value'   => $term_name,
        'compare' => '=',
    );
    $tax_meta_query[] = array(
        'key'     => 'industry',
        'value'   => $term_name,
        'compare' => '=',
    );
}

// Add queries for the original category value
$tax_meta_query[] = array(
    'key'     => 'bpp_industry',
    'value'   => $category,
    'compare' => '=',
);
$tax_meta_query[] = array(
    'key'     => 'industry',
    'value'   => $category,
    'compare' => '=',
);

// Set the combined query for tax/meta integration
$args['_tax_meta_query'] = $tax_meta_query;

// Use WordPress query integration for combining tax and meta queries
add_filter('posts_clauses', function($clauses, $wp_query) {
    if (!empty($wp_query->query_vars['_tax_meta_query'])) {
        global $wpdb;
        
        $tax_meta_query = $wp_query->query_vars['_tax_meta_query'];
        $tax_query_parts = array();
        $meta_query_parts = array();
        
        // Handle taxonomy conditions
        foreach ($tax_meta_query as $key => $query) {
            if (isset($query['taxonomy'])) {
                // This is a taxonomy query
                $term_id = 0;
                if (isset($query['terms'])) {
                    $term = get_term_by($query['field'] ?? 'slug', $query['terms'], $query['taxonomy']);
                    if ($term && !is_wp_error($term)) {
                        $term_id = $term->term_id;
                    }
                }
                
                if ($term_id > 0) {
                    $tax_query_parts[] = $wpdb->prepare(
                        "EXISTS (
                            SELECT 1 FROM {$wpdb->term_relationships} tr
                            JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                            WHERE tr.object_id = {$wpdb->posts}.ID
                            AND tt.taxonomy = %s
                            AND tt.term_id = %d
                        )",
                        $query['taxonomy'],
                        $term_id
                    );
                }
            } elseif (isset($query['key'])) {
                // This is a meta query
                $meta_query_parts[] = $wpdb->prepare(
                    "EXISTS (
                        SELECT 1 FROM {$wpdb->postmeta} pm
                        WHERE pm.post_id = {$wpdb->posts}.ID
                        AND pm.meta_key = %s
                        AND pm.meta_value = %s
                    )",
                    $query['key'],
                    $query['value']
                );
            } elseif ($key === 'relation') {
                // Skip relation
                continue;
            }
        }
        
        if (!empty($tax_query_parts) || !empty($meta_query_parts)) {
            $all_parts = array_merge($tax_query_parts, $meta_query_parts);
            $or_clause = implode(' OR ', $all_parts);
            
            // Add to the WHERE clause
            if (!empty($or_clause)) {
                $clauses['where'] .= " AND ({$or_clause})";
            }
        }
    }
    
    return $clauses;
}, 10, 2);

// Debug the final query
error_log('FINAL Category Featured Query: ' . print_r($args, true));

// Execute the query
$applicants_query = new WP_Query($args);
$found_posts = $applicants_query->found_posts;

// Debug the results
error_log("Found {$found_posts} posts for category {$category}");

// Bootstrap class mappings
$container_class = $use_bootstrap ? 'container py-4' : 'bpp-featured-container';
$header_class = $use_bootstrap ? 'text-center mb-4' : 'bpp-featured-header';
$title_class = $use_bootstrap ? 'h2 mb-3' : 'bpp-featured-title';
$description_class = $use_bootstrap ? 'lead text-muted mb-4' : 'bpp-featured-description';
$slider_class = $use_bootstrap ? 'carousel slide' : 'bpp-featured-slider';
$slider_id = 'bppCategorySlider_' . str_replace('-', '_', $category);
$card_class = $use_bootstrap ? 'card h-100 shadow-sm' : 'bpp-professional-card';
?>

<!-- Category Featured Applicants -->
<div class="<?php echo esc_attr($container_class); ?>">
    <div class="<?php echo esc_attr($header_class); ?>">
        <h2 class="<?php echo esc_attr($title_class); ?>"><?php echo esc_html($title); ?></h2>
        <p class="<?php echo esc_attr($description_class); ?>"><?php echo esc_html__('Discover talented Black professionals ready to make an impact in this industry.', 'black-potential-pipeline'); ?></p>
    </div>
    
    <?php if ($applicants_query->have_posts()) : ?>
        <?php if ($use_bootstrap) : ?>
        <!-- Bootstrap Carousel Implementation -->
        <div id="<?php echo esc_attr($slider_id); ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php 
                $total_posts = $applicants_query->found_posts;
                $total_slides = ceil($total_posts / $items_per_slide);
                $current_slide = 0;
                $current_item = 0;
                
                while ($applicants_query->have_posts()) : $applicants_query->the_post();
                    $post_id = get_the_ID();
                    $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                    $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
                    $profile_url = get_permalink();
                    
                    // Get industry from taxonomy
                    $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                    $industry = '';
                    if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                        $industry = $industry_terms[0];
                    } else {
                        // Fallback to meta field if taxonomy not set
                        $industry = get_post_meta($post_id, 'bpp_industry', true);
                    }
                    
                    // Start a new slide
                    if ($current_item % $items_per_slide === 0) {
                        $active_class = ($current_slide === 0) ? 'active' : '';
                        echo '<div class="carousel-item ' . $active_class . '">';
                        echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-' . $items_per_slide . ' g-4">';
                        $current_slide++;
                    }
                ?>
                    <div class="col">
                        <a href="<?php echo esc_url($profile_url); ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm" style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="card-img-top">
                                        <?php the_post_thumbnail('medium', array('class' => 'w-100 img-fluid', 'style' => 'max-height: 200px; object-fit: cover;')); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="card-img-top bg-light text-center py-4">
                                        <span class="dashicons dashicons-businessperson d-block mx-auto" style="font-size: 80px; width: 80px; height: 80px;"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php the_title(); ?></h5>
                                    <?php if (!empty($job_title)) : ?>
                                        <p class="card-text text-muted mb-1"><?php echo esc_html($job_title); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($years_experience)) : ?>
                                        <div class="mb-1">
                                            <small class="text-muted">
                                                <?php 
                                                // Display experience in a readable format
                                                if (is_numeric($years_experience)) {
                                                    echo sprintf(_n('%d year experience', '%d years experience', $years_experience, 'black-potential-pipeline'), $years_experience);
                                                } else {
                                                    echo esc_html($years_experience) . ' ' . esc_html__('experience', 'black-potential-pipeline');
                                                }
                                                ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($industry)) : ?>
                                        <span class="badge bg-primary"><?php echo esc_html($industry); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php
                    $current_item++;
                    
                    // Close the slide
                    if ($current_item % $items_per_slide === 0 || $current_item === $total_posts) {
                        echo '</div>'; // Close row
                        echo '</div>'; // Close carousel-item
                    }
                endwhile;
                ?>
            </div>
            
            <?php if ($total_slides > 1) : ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo esc_attr($slider_id); ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php esc_html_e('Previous', 'black-potential-pipeline'); ?></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#<?php echo esc_attr($slider_id); ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php esc_html_e('Next', 'black-potential-pipeline'); ?></span>
                </button>
            <?php endif; ?>
        </div>
        
        <?php else : ?>
        <!-- Custom Slider Implementation (Non-Bootstrap) -->
        <div class="bpp-featured-slider" id="<?php echo esc_attr($slider_id); ?>">
            <div class="bpp-slider-container">
                <?php 
                while ($applicants_query->have_posts()) : $applicants_query->the_post();
                    $post_id = get_the_ID();
                    $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                    $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
                    $profile_url = get_permalink();
                    
                    // Get industry from taxonomy
                    $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                    $industry = '';
                    if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                        $industry = $industry_terms[0];
                    } else {
                        // Fallback to meta field if taxonomy not set
                        $industry = get_post_meta($post_id, 'bpp_industry', true);
                    }
                ?>
                    <div class="bpp-slide">
                        <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link">
                            <div class="bpp-professional-card">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="bpp-card-image">
                                        <?php the_post_thumbnail('medium', array('class' => 'bpp-featured-image')); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="bpp-card-image bpp-no-image">
                                        <span class="dashicons dashicons-businessperson"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="bpp-card-content">
                                    <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                                    
                                    <?php if (!empty($job_title)) : ?>
                                        <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($years_experience)) : ?>
                                        <p class="bpp-professional-experience">
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
                                        <div class="bpp-professional-industry">
                                            <?php echo esc_html($industry); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="bpp-slider-controls">
                <button class="bpp-slider-prev" aria-label="<?php esc_attr_e('Previous Slide', 'black-potential-pipeline'); ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <button class="bpp-slider-next" aria-label="<?php esc_attr_e('Next Slide', 'black-potential-pipeline'); ?>">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
        </div>
        <?php endif; ?>
        
    <?php else : ?>
        <div class="<?php echo $use_bootstrap ? 'alert alert-info' : 'bpp-no-applicants'; ?>">
            <p><?php echo sprintf(esc_html__('No professionals found in the %s category.', 'black-potential-pipeline'), esc_html($term_name)); ?></p>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div>

<!-- Custom JS for Carousel/Slider -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    <?php if ($use_bootstrap) : ?>
    // Initialize Bootstrap Carousel
    var myCarousel = document.getElementById('<?php echo $slider_id; ?>');
    if (myCarousel) {
        var carousel = new bootstrap.Carousel(myCarousel, {
            interval: 5000,
            wrap: true
        });
        
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
                    'box-shadow': ''
                });
            }
        );
    }
    <?php else : ?>
    // Initialize Custom Slider
    var sliderContainer = $('#<?php echo $slider_id; ?> .bpp-slider-container');
    var slideCount = sliderContainer.children().length;
    var slideWidth = sliderContainer.children().outerWidth(true);
    var slidesVisible = <?php echo $items_per_slide; ?>;
    var currentPosition = 0;
    
    // Set initial width
    sliderContainer.css('width', slideWidth * slideCount);
    
    // Handle responsive adjustments
    function updateSliderView() {
        if ($(window).width() < 768) {
            slidesVisible = 1;
        } else if ($(window).width() < 992) {
            slidesVisible = 2;
        } else {
            slidesVisible = <?php echo $items_per_slide; ?>;
        }
        
        slideWidth = $('#<?php echo $slider_id; ?>').width() / slidesVisible;
        sliderContainer.children().css('width', slideWidth);
        sliderContainer.css('width', slideWidth * slideCount);
        
        // Reset position if needed
        if (currentPosition > slideCount - slidesVisible) {
            currentPosition = slideCount - slidesVisible;
            if (currentPosition < 0) currentPosition = 0;
            moveSlider();
        }
    }
    
    $(window).resize(updateSliderView);
    updateSliderView();
    
    // Slider controls
    function moveSlider() {
        sliderContainer.css('transform', 'translateX(' + (-currentPosition * slideWidth) + 'px)');
    }
    
    $('#<?php echo $slider_id; ?> .bpp-slider-prev').click(function() {
        if (currentPosition > 0) {
            currentPosition--;
            moveSlider();
        }
    });
    
    $('#<?php echo $slider_id; ?> .bpp-slider-next').click(function() {
        if (currentPosition < (slideCount - slidesVisible)) {
            currentPosition++;
            moveSlider();
        }
    });
    
    // Add swipe support for mobile
    var touchStartX = 0;
    var touchEndX = 0;
    
    sliderContainer.on('touchstart', function(e) {
        touchStartX = e.originalEvent.touches[0].clientX;
    });
    
    sliderContainer.on('touchend', function(e) {
        touchEndX = e.originalEvent.changedTouches[0].clientX;
        handleSwipe();
    });
    
    function handleSwipe() {
        if (touchStartX - touchEndX > 50) {
            // Swipe left
            $('#<?php echo $slider_id; ?> .bpp-slider-next').trigger('click');
        }
        
        if (touchEndX - touchStartX > 50) {
            // Swipe right
            $('#<?php echo $slider_id; ?> .bpp-slider-prev').trigger('click');
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
                'box-shadow': ''
            });
        }
    );
    <?php endif; ?>
});
</script>

<!-- Custom CSS for non-Bootstrap version -->
<?php if (!$use_bootstrap) : ?>
<style type="text/css">
.bpp-featured-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.bpp-featured-header {
    text-align: center;
    margin-bottom: 2rem;
}

.bpp-featured-title {
    margin-bottom: 1rem;
    font-size: 2rem;
}

.bpp-featured-description {
    color: #666;
    margin-bottom: 2rem;
}

.bpp-featured-slider {
    position: relative;
    overflow: hidden;
    padding: 0 1rem;
}

.bpp-slider-container {
    display: flex;
    transition: transform 0.4s ease;
}

.bpp-slide {
    flex: 0 0 auto;
    padding: 0 10px;
}

.bpp-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.bpp-professional-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    background: #fff;
    height: 100%;
}

.bpp-card-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.bpp-featured-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.bpp-no-image {
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bpp-no-image .dashicons {
    font-size: 80px;
    width: 80px;
    height: 80px;
    color: #aaa;
}

.bpp-card-content {
    padding: 1rem;
}

.bpp-professional-name {
    margin: 0 0 0.5rem;
    font-size: 1.2rem;
    font-weight: 600;
}

.bpp-professional-title {
    margin: 0 0 0.5rem;
    color: #666;
}

.bpp-professional-experience {
    margin: 0 0 0.5rem;
    font-size: 0.9rem;
    color: #777;
}

.bpp-professional-industry {
    display: inline-block;
    background: #0d6efd;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 0.8rem;
}

.bpp-slider-controls {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
}

.bpp-slider-prev,
.bpp-slider-next {
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

.bpp-slider-prev:hover,
.bpp-slider-next:hover {
    background: #0b5ed7;
}

.bpp-no-applicants {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 5px;
    text-align: center;
}

@media (max-width: 768px) {
    .bpp-professional-card {
        margin-bottom: 1rem;
    }
}
</style>
<?php endif; ?> 