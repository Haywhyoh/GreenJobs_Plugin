<?php
/**
 * Provide a public-facing view for the category directory
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

// Debug: Add logging for troubleshooting
error_log("BPP CATEGORY DIRECTORY DEBUGGING:");
error_log("Shortcode Attributes: " . print_r($atts, true));

// Extract shortcode attributes
$category = sanitize_text_field($atts['category']);
$per_page = intval($atts['per_page']);
$layout = sanitize_text_field($atts['layout']);

// Debug: Log the category being queried
error_log("Searching for category: " . $category);

// Add more detailed industry debugging
error_log("Checking industry as both taxonomy term and meta field");

// Get all possible term variations
$term = get_term_by('slug', $category, 'bpp_industry');
if (!$term || is_wp_error($term)) {
    $term = get_term_by('name', $category, 'bpp_industry');
}
$term_slug = $term ? $term->slug : $category;
$term_name = $term ? $term->name : '';

// Debug: Check for approved applicants with this industry
$debug_args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => -1,
);
$debug_query = new WP_Query($debug_args);
$found_approved = $debug_query->found_posts;
error_log("Total approved applicants: " . $found_approved);

// Debug: Look for applicants with this industry as taxonomy
$tax_debug_args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'tax_query' => array(
        array(
            'taxonomy' => 'bpp_industry',
            'field' => 'slug',
            'terms' => $term_slug,
        )
    )
);
$tax_debug_query = new WP_Query($tax_debug_args);
$found_tax = $tax_debug_query->found_posts;
error_log("Applicants with slug '{$term_slug}' as taxonomy term: " . $found_tax);

// Debug: Look for applicants with this industry as meta field
$meta_debug_args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => 'bpp_industry',
            'value' => $category,
            'compare' => '=',
        )
    )
);
$meta_debug_query = new WP_Query($meta_debug_args);
$found_meta = $meta_debug_query->found_posts;
error_log("Applicants with '{$category}' as meta field: " . $found_meta);

// Now try directly with 'industry' meta key (without bpp_ prefix)
$direct_meta_args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => 'industry',
            'value' => $category,
            'compare' => '=',
        )
    )
);
$direct_meta_query = new WP_Query($direct_meta_args);
$found_direct_meta = $direct_meta_query->found_posts;
error_log("Applicants with '{$category}' as direct 'industry' meta field: " . $found_direct_meta);

// Get current page
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Get search parameters
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Create a more comprehensive query that combines both approaches
$args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'paged' => $paged,
    'orderby' => 'meta_value',
    'meta_key' => 'bpp_featured',
    'order' => 'DESC',
);

// IMPORTANT FIX: Create queries for all possible industry representation formats
// This is a critical change - we need to structure the query differently

// Create a combined query with OR relationship at the outer level
$tax_meta_query = array(
    'relation' => 'OR',
    // Query 1: Taxonomy term by slug
    array(
        'taxonomy' => 'bpp_industry',
        'field' => 'slug',
        'terms' => $term_slug,
    )
);

// If we found a term by slug, also try to query by its name as meta value
if (!empty($term_name)) {
    $tax_meta_query[] = array(
        'key' => 'bpp_industry',
        'value' => $term_name,
        'compare' => '=',
    );
    $tax_meta_query[] = array(
        'key' => 'industry',
        'value' => $term_name,
        'compare' => '=',
    );
}

// Add queries for the original category value (which might be a slug)
$tax_meta_query[] = array(
    'key' => 'bpp_industry',
    'value' => $category,
    'compare' => '=',
);
$tax_meta_query[] = array(
    'key' => 'industry',
    'value' => $category,
    'compare' => '=',
);

// Set the combined query
$args['_tax_meta_query'] = $tax_meta_query;

// Use the WordPress query integration for combining tax and meta queries
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

// Add search if provided
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Debug the final query
error_log('FINAL Category Query: ' . print_r($args, true));

$applicants_query = new WP_Query($args);
?>

<div class="bpp-directory-container bpp-category-directory">
    <div class="bpp-directory-header">
        <h2 class="bpp-directory-title"><?php echo esc_html($atts['title']); ?></h2>
        <p class="bpp-directory-description">
            <?php echo esc_html__('Browse our curated database of talented Black professionals in this industry.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <div class="bpp-directory-filters">
        <form method="get" class="bpp-filter-form">
            <div class="bpp-filter-row">
                <div class="bpp-filter-field">
                    <label for="bpp-search"><?php echo esc_html__('Search', 'black-potential-pipeline'); ?></label>
                    <input type="text" id="bpp-search" name="search" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php echo esc_attr__('Search by name, skills, or location', 'black-potential-pipeline'); ?>">
                </div>
                
                <div class="bpp-filter-actions">
                    <button type="submit" class="bpp-filter-button"><?php echo esc_html__('Search', 'black-potential-pipeline'); ?></button>
                    <a href="<?php echo esc_url(remove_query_arg('search')); ?>" class="bpp-reset-button"><?php echo esc_html__('Reset', 'black-potential-pipeline'); ?></a>
                </div>
            </div>
            
            <!-- Preserve the category in URL when searching -->
            <input type="hidden" name="category" value="<?php echo esc_attr($category); ?>">
        </form>
    </div>
    
    <div class="bpp-directory-layout-toggle">
        <a href="<?php echo esc_url(add_query_arg('layout', 'grid')); ?>" class="bpp-layout-button <?php echo $layout === 'grid' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-grid-view"></span>
            <?php echo esc_html__('Grid', 'black-potential-pipeline'); ?>
        </a>
        <a href="<?php echo esc_url(add_query_arg('layout', 'list')); ?>" class="bpp-layout-button <?php echo $layout === 'list' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-list-view"></span>
            <?php echo esc_html__('List', 'black-potential-pipeline'); ?>
        </a>
    </div>
    
    <?php if ($applicants_query->have_posts()) : ?>
        <div class="bpp-directory-results">
            <div class="bpp-results-count">
                <?php
                echo sprintf(
                    esc_html(_n('Showing %1$d of %2$d professional', 'Showing %1$d of %2$d professionals', $applicants_query->found_posts, 'black-potential-pipeline')),
                    $applicants_query->post_count,
                    $applicants_query->found_posts
                );
                ?>
            </div>
        </div>
        
        <div class="bpp-directory-content bpp-layout-<?php echo esc_attr($layout); ?>">
            <?php while ($applicants_query->have_posts()) : $applicants_query->the_post(); 
                $post_id = get_the_ID();
                $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                $location = get_post_meta($post_id, 'bpp_location', true);
                $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
                $skills = get_post_meta($post_id, 'bpp_skills', true);
            ?>
                <div class="bpp-professional-card">
                    <div class="bpp-professional-header">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="bpp-professional-photo">
                                <?php the_post_thumbnail('thumbnail'); ?>
                            </div>
                        <?php else : ?>
                            <div class="bpp-professional-photo bpp-no-photo">
                                <span class="dashicons dashicons-businessperson"></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="bpp-professional-info">
                            <h3 class="bpp-professional-name"><?php the_title(); ?></h3>
                            <?php if (!empty($job_title)) : ?>
                                <p class="bpp-professional-title"><?php echo esc_html($job_title); ?></p>
                            <?php endif; ?>
                            <div class="bpp-professional-meta">
                                <?php if (!empty($location)) : ?>
                                    <span class="bpp-professional-location">
                                        <span class="dashicons dashicons-location"></span>
                                        <?php echo esc_html($location); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($years_experience)) : ?>
                                    <span class="bpp-professional-experience">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php echo sprintf(esc_html(_n('%d year experience', '%d years experience', $years_experience, 'black-potential-pipeline')), $years_experience); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($layout === 'list') : ?>
                        <div class="bpp-professional-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($skills)) : ?>
                        <div class="bpp-professional-skills">
                            <h4><?php echo esc_html__('Skills', 'black-potential-pipeline'); ?></h4>
                            <p><?php echo esc_html($skills); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="bpp-professional-footer">
                        <a href="<?php the_permalink(); ?>" class="bpp-view-profile">
                            <?php echo esc_html__('View Full Profile', 'black-potential-pipeline'); ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="bpp-directory-pagination">
            <?php
            echo paginate_links(array(
                'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'format' => '?paged=%#%',
                'current' => max(1, $paged),
                'total' => $applicants_query->max_num_pages,
                'prev_text' => '&laquo; ' . esc_html__('Previous', 'black-potential-pipeline'),
                'next_text' => esc_html__('Next', 'black-potential-pipeline') . ' &raquo;',
            ));
            ?>
        </div>
    <?php else : ?>
        <div class="bpp-no-results">
            <p><?php echo esc_html__('No professionals found in this category matching your criteria.', 'black-potential-pipeline'); ?></p>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div> 