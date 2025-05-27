<?php
/**
 * Provide a public-facing view for showcasing applicants from a specific category
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

// Get term information
$term = get_term_by('slug', $category, 'bpp_industry');
if (!$term || is_wp_error($term)) {
    $term = get_term_by('name', $category, 'bpp_industry');
}
$term_slug = $term ? $term->slug : $category;
$term_name = $term ? $term->name : '';

// If term_name is still empty, try to format the category slug into a readable name
if (empty($term_name)) {
    $term_name = ucwords(str_replace('-', ' ', $category));
}

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

// Execute the query
$applicants_query = new WP_Query($args);
$found_posts = $applicants_query->found_posts;

// Default industry names lookup array for formatting industry slugs
$default_industry_names = array(
    'nature-based-work' => __('Nature-based work', 'black-potential-pipeline'),
    'environmental-policy' => __('Environmental policy', 'black-potential-pipeline'),
    'climate-science' => __('Climate science', 'black-potential-pipeline'),
    'green-construction' => __('Green construction & infrastructure', 'black-potential-pipeline'),
);
?>

<!-- Category Featured Applicants -->
<div class="bpp-featured-container bpp-category-featured">
    <div class="bpp-featured-header">
        <h2 class="bpp-featured-title"><?php echo esc_html($title); ?></h2>
        <p class="bpp-featured-description"><?php echo esc_html__('Discover talented Black professionals ready to make an impact in this industry.', 'black-potential-pipeline'); ?></p>
    </div>
    
    <?php if ($applicants_query->have_posts()) : ?>
        <div class="bpp-featured-grid">
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
        </div>
        
        <?php if ($found_posts > $total_items) : ?>
            <div class="bpp-view-more-container">
                <a href="<?php echo esc_url(home_url('/black-professionals/' . $term_slug)); ?>" class="bpp-view-all">
                    <?php echo esc_html__('View all', 'black-potential-pipeline'); ?> <?php echo esc_html($term_name); ?> <?php echo esc_html__('professionals', 'black-potential-pipeline'); ?> →
                </a>
            </div>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <div class="bpp-no-results">
            <p><?php _e('No professionals found in this category.', 'black-potential-pipeline'); ?></p>
        </div>
    <?php endif; ?>
</div> 