<?php
/**
 * Provide a public-facing view for the full directory of approved professionals
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
$title = isset($atts['title']) ? sanitize_text_field($atts['title']) : __('Black Professionals Directory', 'black-potential-pipeline');
$description = isset($atts['description']) ? sanitize_text_field($atts['description']) : __('Discover talented Black professionals ready to make an impact in green industries.', 'black-potential-pipeline');
$per_page = isset($atts['per_page']) ? intval($atts['per_page']) : 12;
$layout = isset($atts['layout']) ? sanitize_text_field($atts['layout']) : 'grid';
$use_bootstrap = isset($atts['use_bootstrap']) ? ($atts['use_bootstrap'] === 'yes') : true;

// Default industry names lookup array for formatting industry slugs
$default_industry_names = array(
    'nature-based-work' => __('Nature-based work', 'black-potential-pipeline'),
    'environmental-policy' => __('Environmental policy', 'black-potential-pipeline'),
    'climate-science' => __('Climate science', 'black-potential-pipeline'),
    'green-construction' => __('Green construction & infrastructure', 'black-potential-pipeline'),
);

// Get all industry terms for the filter
$industries = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));

// If industries are not properly formatted, provide default industries
if (empty($industries) || is_wp_error($industries)) {
    $industries = array(
        array('slug' => 'nature-based-work', 'term_id' => 'nature-based-work', 'name' => __('Nature-based work', 'black-potential-pipeline')),
        array('slug' => 'environmental-policy', 'term_id' => 'environmental-policy', 'name' => __('Environmental policy', 'black-potential-pipeline')),
        array('slug' => 'climate-science', 'term_id' => 'climate-science', 'name' => __('Climate science', 'black-potential-pipeline')),
        array('slug' => 'green-construction', 'term_id' => 'green-construction', 'name' => __('Green construction & infrastructure', 'black-potential-pipeline')),
    );
}

// Get current page
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Get search query if exists
$search_query = isset($_GET['bpp_search']) ? sanitize_text_field($_GET['bpp_search']) : '';
$industry_filter = isset($_GET['bpp_industry']) ? sanitize_text_field($_GET['bpp_industry']) : '';
$experience_filter = isset($_GET['bpp_experience']) ? sanitize_text_field($_GET['bpp_experience']) : '';

// Base query args
$args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
);

// Add search query if exists
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Industry filter query modifications
if (!empty($industry_filter)) {
    // Create a query that checks for the industry in taxonomy and meta fields
    $tax_meta_query = array(
        'relation' => 'OR',
        // Taxonomy term by slug
        array(
            'taxonomy' => 'bpp_industry',
            'field'    => 'slug',
            'terms'    => $industry_filter,
        )
    );

    // Add meta query options for various ways the category might be stored
    // Check by name from the lookup array (for meta fields using names instead of slugs)
    if (isset($default_industry_names[$industry_filter])) {
        $term_name = $default_industry_names[$industry_filter];
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

    // Add queries for the original slug value
    $tax_meta_query[] = array(
        'key'     => 'bpp_industry',
        'value'   => $industry_filter,
        'compare' => '=',
    );
    $tax_meta_query[] = array(
        'key'     => 'industry',
        'value'   => $industry_filter,
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
}

// Experience filter
if (!empty($experience_filter)) {
    $meta_query = array('relation' => 'OR');
    
    switch ($experience_filter) {
        case '0-2':
            $meta_query[] = array(
                'key' => 'bpp_years_experience',
                'value' => array(0, 1, 2),
                'compare' => 'IN',
                'type' => 'NUMERIC'
            );
            break;
        case '3-5':
            $meta_query[] = array(
                'key' => 'bpp_years_experience',
                'value' => array(3, 4, 5),
                'compare' => 'IN',
                'type' => 'NUMERIC'
            );
            break;
        case '6-10':
            $meta_query[] = array(
                'key' => 'bpp_years_experience',
                'value' => array(6, 7, 8, 9, 10),
                'compare' => 'IN',
                'type' => 'NUMERIC'
            );
            break;
        case '10+':
            $meta_query[] = array(
                'key' => 'bpp_years_experience',
                'value' => 10,
                'compare' => '>',
                'type' => 'NUMERIC'
            );
            break;
    }
    
    if (!empty($args['meta_query'])) {
        $args['meta_query'] = array_merge(array('relation' => 'AND'), array($args['meta_query'], $meta_query));
    } else {
        $args['meta_query'] = $meta_query;
    }
}

// Run the query
$professionals_query = new WP_Query($args);

// Get total count for stats
$total_count = $professionals_query->found_posts;

// Check if we have search/filter parameters to display a message
$has_search_filter = !empty($search_query) || !empty($industry_filter) || !empty($experience_filter);
$search_filter_message = '';

if ($has_search_filter) {
    $filter_parts = array();
    
    if (!empty($search_query)) {
        $filter_parts[] = sprintf(__('Keywords: "%s"', 'black-potential-pipeline'), esc_html($search_query));
    }
    
    if (!empty($industry_filter)) {
        // Find the proper name for the filter
        $industry_name = isset($default_industry_names[$industry_filter]) ? 
            $default_industry_names[$industry_filter] : ucfirst(str_replace('-', ' ', $industry_filter));
            
        $filter_parts[] = sprintf(__('Industry: %s', 'black-potential-pipeline'), esc_html($industry_name));
    }
    
    if (!empty($experience_filter)) {
        $experience_labels = array(
            '0-2' => __('0-2 years', 'black-potential-pipeline'),
            '3-5' => __('3-5 years', 'black-potential-pipeline'),
            '6-10' => __('6-10 years', 'black-potential-pipeline'),
            '10+' => __('10+ years', 'black-potential-pipeline'),
        );
        
        $experience_label = isset($experience_labels[$experience_filter]) ? 
            $experience_labels[$experience_filter] : $experience_filter;
            
        $filter_parts[] = sprintf(__('Experience: %s', 'black-potential-pipeline'), esc_html($experience_label));
    }
    
    if (count($filter_parts) > 0) {
        $search_filter_message = sprintf(
            _n(
                'Found %s professional matching: %s',
                'Found %s professionals matching: %s',
                $total_count,
                'black-potential-pipeline'
            ),
            '<strong>' . number_format_i18n($total_count) . '</strong>',
            '<span class="bpp-filter-criteria">' . implode(', ', $filter_parts) . '</span>'
        );
    }
}

// Calculate total pages for pagination
$total_pages = $professionals_query->max_num_pages;

// Current page URL for form submission
$current_url = esc_url(add_query_arg(array(), get_permalink()));

// Bootstrap class mappings
$container_class = $use_bootstrap ? 'container py-4' : 'bpp-directory-container';
$header_class = $use_bootstrap ? 'text-center mb-4' : 'bpp-directory-header';
$title_class = $use_bootstrap ? 'h2 mb-2' : 'bpp-directory-title';
$description_class = $use_bootstrap ? 'lead text-muted' : 'bpp-directory-description';
$controls_class = $use_bootstrap ? 'row mb-4' : 'bpp-directory-controls';
$search_container_class = $use_bootstrap ? 'col-12 mb-3' : 'bpp-search-filter-container';
$form_class = $use_bootstrap ? 'row g-3 align-items-end' : '';
$search_box_class = $use_bootstrap ? 'col-md-5' : 'bpp-search-box';
$filter_options_class = $use_bootstrap ? 'col-md-7 d-flex flex-wrap gap-2' : 'bpp-filter-options';
$filter_select_class = $use_bootstrap ? 'me-2' : 'bpp-filter-select';
$select_class = $use_bootstrap ? 'form-select' : '';
$input_class = $use_bootstrap ? 'form-control' : '';
$button_primary_class = $use_bootstrap ? 'btn btn-primary' : 'bpp-button bpp-button-primary';
$button_class = $use_bootstrap ? 'btn btn-outline-secondary' : 'bpp-button';
$toggle_class = $use_bootstrap ? 'btn-group ms-2' : 'bpp-layout-toggle';
$active_toggle_class = $use_bootstrap ? 'active' : '';
$content_container_class = $use_bootstrap ? 'row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4' : 'bpp-directory-content';
$card_class = $use_bootstrap ? 'card h-100 shadow-sm' : 'bpp-professional-card';
$card_header_class = $use_bootstrap ? 'card-header d-flex' : 'bpp-professional-header';
$photo_class = $use_bootstrap ? 'me-3' : 'bpp-professional-photo';
$info_class = $use_bootstrap ? 'flex-grow-1' : 'bpp-professional-info';
$card_name_class = $use_bootstrap ? 'h4 mb-1' : 'bpp-professional-name';
$card_title_class = $use_bootstrap ? 'text-muted mb-1' : 'bpp-professional-title';
$card_content_class = $use_bootstrap ? 'card-body' : 'bpp-professional-content';
$card_excerpt_class = $use_bootstrap ? 'mb-3' : 'bpp-professional-excerpt';
$skills_container_class = $use_bootstrap ? 'mt-3' : 'bpp-professional-skills';
$skills_title_class = $use_bootstrap ? 'h6' : '';
$skills_tags_class = $use_bootstrap ? 'd-flex flex-wrap gap-1 mt-2' : 'bpp-skills-tags';
$skill_tag_class = $use_bootstrap ? 'badge bg-secondary rounded-pill' : 'bpp-skill-tag';
$card_footer_class = $use_bootstrap ? 'card-footer text-center' : 'bpp-professional-footer';
$pagination_class = $use_bootstrap ? 'pagination justify-content-center mt-4' : 'bpp-pagination';
?>

<div class="<?php echo esc_attr($container_class); ?>">
    <div class="<?php echo esc_attr($header_class); ?>">
        <h2 class="<?php echo esc_attr($title_class); ?>"><?php echo esc_html($title); ?></h2>
        <p class="<?php echo esc_attr($description_class); ?>"><?php echo esc_html($description); ?></p>
    </div>
    
    <div class="<?php echo esc_attr($controls_class); ?>">
        <div class="<?php echo esc_attr($search_container_class); ?>">
            <form id="bpp-directory-filters" method="get" action="<?php echo $current_url; ?>" class="<?php echo esc_attr($form_class); ?>">
                <div class="<?php echo $use_bootstrap ? 'col-md-5 col-lg-12' : 'bpp-filter-row'; ?>">
                    <div class="<?php echo esc_attr($search_box_class); ?>">
                        <label for="bpp_search" class="<?php echo $use_bootstrap ? 'form-label' : ''; ?>"><?php _e('Search', 'black-potential-pipeline'); ?></label>
                        <input type="text" id="bpp_search" name="bpp_search" class="<?php echo esc_attr($input_class); ?>" placeholder="<?php _e('Search by name, skills or location', 'black-potential-pipeline'); ?>" value="<?php echo esc_attr($search_query); ?>">
                    </div>
                    
                    <div class="<?php echo esc_attr($filter_options_class); ?>">
                        <div class="<?php echo esc_attr($filter_select_class); ?>">
                            <label for="bpp_industry" class="<?php echo $use_bootstrap ? 'form-label' : ''; ?>"><?php _e('Industry', 'black-potential-pipeline'); ?></label>
                            <select id="bpp_industry" name="bpp_industry" class="<?php echo esc_attr($select_class); ?>">
                                <option value=""><?php _e('All Industries', 'black-potential-pipeline'); ?></option>
                                <?php foreach ($industries as $industry) : 
                                    // Check if $industry is an array or object
                                    $slug = is_object($industry) ? $industry->slug : (isset($industry['slug']) ? $industry['slug'] : '');
                                    $name = is_object($industry) ? $industry->name : (isset($industry['name']) ? $industry['name'] : '');
                                    if (empty($slug) || empty($name)) continue;
                                ?>
                                    <option value="<?php echo esc_attr($slug); ?>" <?php selected($industry_filter, $slug); ?>>
                                        <?php echo esc_html($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="<?php echo esc_attr($filter_select_class); ?>">
                            <label for="bpp_experience" class="<?php echo $use_bootstrap ? 'form-label' : ''; ?>"><?php _e('Experience', 'black-potential-pipeline'); ?></label>
                            <select id="bpp_experience" name="bpp_experience" class="<?php echo esc_attr($select_class); ?>">
                                <option value=""><?php _e('All Experience Levels', 'black-potential-pipeline'); ?></option>
                                <option value="0-2" <?php selected($experience_filter, '0-2'); ?>><?php _e('0-2 years', 'black-potential-pipeline'); ?></option>
                                <option value="3-5" <?php selected($experience_filter, '3-5'); ?>><?php _e('3-5 years', 'black-potential-pipeline'); ?></option>
                                <option value="6-10" <?php selected($experience_filter, '6-10'); ?>><?php _e('6-10 years', 'black-potential-pipeline'); ?></option>
                                <option value="10+" <?php selected($experience_filter, '10+'); ?>><?php _e('10+ years', 'black-potential-pipeline'); ?></option>
                            </select>
                        </div>
                        
                        <div class="<?php echo $use_bootstrap ? 'd-flex align-items-end' : ''; ?>">
                            <button type="submit" class="<?php echo esc_attr($button_primary_class); ?>">
                                <?php _e('Filter', 'black-potential-pipeline'); ?>
                            </button>
                            
                            <?php if (!empty($search_query) || !empty($industry_filter) || !empty($experience_filter)) : ?>
                                <a href="<?php echo $current_url; ?>" class="<?php echo $use_bootstrap ? 'btn btn-link' : 'bpp-clear-filters'; ?>">
                                    <?php _e('Clear Filters', 'black-potential-pipeline'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
            
            <?php if (!$use_bootstrap) : ?>
            <div class="bpp-display-options">
                <div class="bpp-layout-toggle">
                    <button type="button" class="bpp-toggle-grid <?php echo ($layout === 'grid') ? 'active' : ''; ?>" data-layout="grid">
                        <span class="dashicons dashicons-grid-view"></span>
                    </button>
                    <button type="button" class="bpp-toggle-list <?php echo ($layout === 'list') ? 'active' : ''; ?>" data-layout="list">
                        <span class="dashicons dashicons-list-view"></span>
                    </button>
                </div>
                
                <div class="bpp-results-count">
                    <?php 
                    printf(
                        _n(
                            'Showing %1$d of %2$d professional', 
                            'Showing %1$d of %2$d professionals', 
                            $total_count, 
                            'black-potential-pipeline'
                        ),
                        min($per_page, $total_count),
                        $total_count
                    ); 
                    ?>
                </div>
            </div>
            <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="results-count">
                    <?php 
                    printf(
                        _n(
                            'Showing %1$d of %2$d professional', 
                            'Showing %1$d of %2$d professionals', 
                            $total_count, 
                            'black-potential-pipeline'
                        ),
                        min($per_page, $total_count),
                        $total_count
                    ); 
                    ?>
                </div>
                
                <div class="btn-group" role="group" aria-label="Layout Options">
                    <button type="button" class="btn btn-outline-secondary <?php echo ($layout === 'grid') ? 'active' : ''; ?>" data-layout="grid">
                        <i class="dashicons dashicons-grid-view"></i> Grid
                    </button>
                    <button type="button" class="btn btn-outline-secondary <?php echo ($layout === 'list') ? 'active' : ''; ?>" data-layout="list">
                        <i class="dashicons dashicons-list-view"></i> List
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($professionals_query->have_posts()) : ?>
        <div class="<?php echo esc_attr($content_container_class); ?> <?php echo !$use_bootstrap ? 'bpp-layout-' . esc_attr($layout) : ''; ?>">
            <?php 
            while ($professionals_query->have_posts()) : $professionals_query->the_post();
                $post_id = get_the_ID();
                $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                $location = get_post_meta($post_id, 'bpp_location', true);
                $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
                $skills = get_post_meta($post_id, 'bpp_skills', true);
                $skills_array = !empty($skills) ? explode(',', $skills) : array();
                
                // Get industry from taxonomy
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
                
                // Get permalink for the profile
                $profile_url = get_permalink();
            ?>
                <?php if ($use_bootstrap) : ?>
                <div class="col">
                <?php endif; ?>
                <a href="<?php echo esc_url($profile_url); ?>" class="bpp-card-link" style="text-decoration: none; color: inherit;">
                    <div class="<?php echo esc_attr($card_class); ?>" style="height: 100%; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <?php if (has_post_thumbnail()) : ?>
                            <div class="card-img-top">
                                <?php the_post_thumbnail('medium', array('class' => 'w-100 img-fluid', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                </div>
                            <?php else : ?>
                            <div class="card-img-top bg-light text-center py-4" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                                <span class="dashicons dashicons-businessperson" style="font-size: 80px; width: 80px; height: 80px; color: #aaa;"></span>
                                </div>
                        <?php endif; ?>
                        
                        <div class="<?php echo esc_attr($card_content_class); ?>">
                            <h3 class="<?php echo esc_attr($card_name_class); ?>" style="margin: 0 0 0.5rem; font-size: 1.2rem; font-weight: 600;"><?php the_title(); ?></h3>
                            
                            <?php if (!empty($job_title)) : ?>
                                <p class="<?php echo esc_attr($card_title_class); ?>" style="margin: 0 0 0.5rem; color: #666;"><?php echo esc_html($job_title); ?></p>
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
                                <div class="<?php echo $use_bootstrap ? 'badge bg-primary' : 'bpp-professional-industry'; ?>" style="<?php echo !$use_bootstrap ? 'display: inline-block; background: #61CE70; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;' : ''; ?>">
                                    <?php echo esc_html($industry); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php 
                                // Display skills in list view only
                                if (!empty($skills_array) && $layout === 'list') : ?>
                                <div class="bpp-professional-skills">
                                    <?php foreach (array_slice($skills_array, 0, 5) as $skill) : ?>
                                        <span class="bpp-skill-tag"><?php echo esc_html(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($skills_array) > 5) : ?>
                                        <span class="bpp-skill-tag">+<?php echo count($skills_array) - 5; ?> more</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php if ($use_bootstrap) : ?>
                </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
        
        <?php if ($total_pages > 1) : ?>
            <div class="mt-4">
                <?php
                if ($use_bootstrap) {
                    // Bootstrap pagination
                    echo '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
                    
                    // Previous page
                    $prev_text = __('&laquo; Previous', 'black-potential-pipeline');
                    if ($paged > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . add_query_arg('paged', $paged - 1) . '">' . $prev_text . '</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">' . $prev_text . '</span></li>';
                    }
                    
                    // Page numbers
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i == $paged) {
                            echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                        } else {
                            echo '<li class="page-item"><a class="page-link" href="' . add_query_arg('paged', $i) . '">' . $i . '</a></li>';
                        }
                    }
                    
                    // Next page
                    $next_text = __('Next &raquo;', 'black-potential-pipeline');
                    if ($paged < $total_pages) {
                        echo '<li class="page-item"><a class="page-link" href="' . add_query_arg('paged', $paged + 1) . '">' . $next_text . '</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">' . $next_text . '</span></li>';
                    }
                    
                    echo '</ul></nav>';
                } else {
                    // Standard WordPress pagination
                    echo '<div class="bpp-pagination">';
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo; Previous', 'black-potential-pipeline'),
                        'next_text' => __('Next &raquo;', 'black-potential-pipeline'),
                        'total' => $total_pages,
                        'current' => $paged,
                        'add_args' => array(
                            'bpp_search' => $search_query,
                            'bpp_industry' => $industry_filter,
                            'bpp_experience' => $experience_filter,
                        ),
                    ));
                    echo '</div>';
                }
                ?>
            </div>
        <?php endif; ?>
        
    <?php else : ?>
        <div class="<?php echo $use_bootstrap ? 'alert alert-info text-center' : 'bpp-no-results'; ?>">
            <p><?php _e('No professionals found matching your criteria.', 'black-potential-pipeline'); ?></p>
            <?php if (!empty($search_query) || !empty($industry_filter) || !empty($experience_filter)) : ?>
                <p>
                    <a href="<?php echo $current_url; ?>" class="<?php echo $use_bootstrap ? 'btn btn-outline-primary' : 'bpp-button'; ?>">
                        <?php _e('Clear all filters', 'black-potential-pipeline'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Layout toggle functionality
    $('.bpp-layout-toggle button, .btn-group button').on('click', function() {
        const layout = $(this).data('layout');
        
        // Update active button
        $('.bpp-layout-toggle button, .btn-group button').removeClass('active');
        $(this).addClass('active');
        
        // Update layout class
        $('.bpp-directory-content')
            .removeClass('bpp-layout-grid bpp-layout-list')
            .addClass('bpp-layout-' + layout);
        
        // Store preference in session
        if (typeof(Storage) !== "undefined") {
            sessionStorage.setItem('bpp_directory_layout', layout);
        }
    });
    
    // Restore layout preference if saved
    if (typeof(Storage) !== "undefined" && sessionStorage.getItem('bpp_directory_layout')) {
        const savedLayout = sessionStorage.getItem('bpp_directory_layout');
        
        // Trigger click on the appropriate button
        $('.bpp-toggle-' + savedLayout + ', .btn-group button[data-layout="' + savedLayout + '"]').trigger('click');
    }
    
    // Add hover effect to card
    $('.bpp-card-link .card, .bpp-card-link .bpp-professional-card').hover(
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
});
</script>

<style>
:root {
    /* GreenJobs color variables - use WordPress theme colors first, then our green/brown scheme */
    --bpp-primary-color: var(--wp--preset--color--primary, #61CE70);
    --bpp-secondary-color: var(--wp--preset--color--secondary, #6F3802);
    --bpp-info-color: var(--wp--preset--color--tertiary, #0dcaf0);
    --bpp-warning-color: var(--wp--preset--color--warning, #ffc107);
    --bpp-light-color: var(--wp--preset--color--light, #f8f9fa);
    --bpp-dark-color: var(--wp--preset--color--dark, #212529);
}

/* Override Bootstrap color classes with theme colors */
.bg-primary {
    background-color: var(--bpp-primary-color) !important;
}

.bg-secondary {
    background-color: var(--bpp-secondary-color) !important;
}

.bg-info {
    background-color: var(--bpp-info-color) !important;
}

.bg-warning {
    background-color: var(--bpp-warning-color) !important;
}

.bg-light {
    background-color: var(--bpp-light-color) !important;
}

.text-dark {
    color: var(--bpp-dark-color) !important;
}

/* Apply theme color to links and buttons */
.bpp-directory-container a:not(.page-link):not(.btn-outline-secondary) {
    color: var(--bpp-primary-color);
}

.btn-primary, .page-item.active .page-link {
    background-color: var(--bpp-primary-color) !important;
    border-color: var(--bpp-primary-color) !important;
}

.btn-outline-primary {
    color: var(--bpp-primary-color) !important;
    border-color: var(--bpp-primary-color) !important;
}

.btn-outline-primary:hover {
    background-color: var(--bpp-primary-color) !important;
    color: white !important;
}

/* Search filter background */
.bpp-filter-message {
    background-color: rgba(97, 206, 112, 0.1);
    border-left: 4px solid var(--bpp-primary-color);
}

/* Improve card hover effects */
.bpp-card-link .card:hover, 
.bpp-professional-card:hover {
    border-color: var(--bpp-primary-color) !important;
}
</style> 