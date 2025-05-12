<?php
/**
 * Provide a public-facing view for the full directory of approved professionals
 *
 * This file is used to markup the public-facing aspects of the plugin.
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

// Extract shortcode attributes
$title = isset($atts['title']) ? sanitize_text_field($atts['title']) : __('Black Professionals Directory', 'black-potential-pipeline');
$description = isset($atts['description']) ? sanitize_text_field($atts['description']) : __('Discover talented Black professionals ready to make an impact in green industries.', 'black-potential-pipeline');
$per_page = isset($atts['per_page']) ? intval($atts['per_page']) : 12;
$layout = isset($atts['layout']) ? sanitize_text_field($atts['layout']) : 'grid';

// Get all industry terms for the filter
$industries = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));

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

// Add industry filter if selected
if (!empty($industry_filter)) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'bpp_industry',
            'field' => 'slug',
            'terms' => $industry_filter,
        ),
    );
}

// Add experience filter if selected
if (!empty($experience_filter)) {
    $meta_query = array('relation' => 'OR');
    
    switch ($experience_filter) {
        case '0-2':
            $meta_query[] = array(
                'key' => 'bpp_years_experience',
                'value' => '0-2',
                'compare' => '=',
            );
            break;
            
        case '3-5':
            $meta_query[] = array(
                'key' => 'bpp_years_experience',
                'value' => '3-5',
                'compare' => '=',
            );
            break;
            
        case '6-10':
            $meta_query[] = array(
                'key' => 'bpp_years_experience',
                'value' => '6-10',
                'compare' => '=',
            );
            break;
            
        case '10+':
            $meta_query[] = array(
                'key' => 'bpp_years_experience',
                'value' => '10+',
                'compare' => '=',
            );
            break;
    }
    
    $args['meta_query'] = $meta_query;
}

// Execute the query
$directory_query = new WP_Query($args);

// Calculate total pages for pagination
$total_pages = $directory_query->max_num_pages;

// Current page URL for form submission
$current_url = esc_url(add_query_arg(array(), get_permalink()));
?>

<div class="bpp-directory-container">
    <div class="bpp-directory-header">
        <h2 class="bpp-directory-title"><?php echo esc_html($title); ?></h2>
        <p class="bpp-directory-description"><?php echo esc_html($description); ?></p>
    </div>
    
    <div class="bpp-directory-controls">
        <div class="bpp-search-filter-container">
            <form id="bpp-directory-filters" method="get" action="<?php echo $current_url; ?>">
                <div class="bpp-filter-row">
                    <div class="bpp-search-box">
                        <input type="text" name="bpp_search" placeholder="<?php _e('Search by name, skills or location', 'black-potential-pipeline'); ?>" value="<?php echo esc_attr($search_query); ?>">
                        <button type="submit" class="bpp-search-button">
                            <span class="dashicons dashicons-search"></span>
                        </button>
                    </div>
                    
                    <div class="bpp-filter-options">
                        <div class="bpp-filter-select">
                            <select name="bpp_industry">
                                <option value=""><?php _e('All Industries', 'black-potential-pipeline'); ?></option>
                                <?php foreach ($industries as $industry) : ?>
                                    <option value="<?php echo esc_attr($industry->slug); ?>" <?php selected($industry_filter, $industry->slug); ?>>
                                        <?php echo esc_html($industry->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="bpp-filter-select">
                            <select name="bpp_experience">
                                <option value=""><?php _e('All Experience Levels', 'black-potential-pipeline'); ?></option>
                                <option value="0-2" <?php selected($experience_filter, '0-2'); ?>><?php _e('0-2 years', 'black-potential-pipeline'); ?></option>
                                <option value="3-5" <?php selected($experience_filter, '3-5'); ?>><?php _e('3-5 years', 'black-potential-pipeline'); ?></option>
                                <option value="6-10" <?php selected($experience_filter, '6-10'); ?>><?php _e('6-10 years', 'black-potential-pipeline'); ?></option>
                                <option value="10+" <?php selected($experience_filter, '10+'); ?>><?php _e('10+ years', 'black-potential-pipeline'); ?></option>
                            </select>
                        </div>
                        
                        <button type="submit" class="bpp-filter-button bpp-button-primary">
                            <?php _e('Filter', 'black-potential-pipeline'); ?>
                        </button>
                        
                        <?php if (!empty($search_query) || !empty($industry_filter) || !empty($experience_filter)) : ?>
                            <a href="<?php echo $current_url; ?>" class="bpp-clear-filters">
                                <?php _e('Clear Filters', 'black-potential-pipeline'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
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
                            $directory_query->found_posts, 
                            'black-potential-pipeline'
                        ),
                        min($per_page, $directory_query->found_posts),
                        $directory_query->found_posts
                    ); 
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($directory_query->have_posts()) : ?>
        <div class="bpp-directory-content <?php echo 'bpp-layout-' . esc_attr($layout); ?>">
            <?php 
            while ($directory_query->have_posts()) : $directory_query->the_post();
                $post_id = get_the_ID();
                $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                $location = get_post_meta($post_id, 'bpp_location', true);
                $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
                $skills = get_post_meta($post_id, 'bpp_skills', true);
                $skills_array = !empty($skills) ? explode(',', $skills) : array();
                
                // Get industry from taxonomy
                $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                $industry = !empty($industry_terms) ? $industry_terms[0] : '';
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
                            
                            <?php if (!empty($industry)) : ?>
                                <p class="bpp-professional-industry"><?php echo esc_html($industry); ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($location)) : ?>
                                <p class="bpp-professional-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html($location); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($years_experience)) : ?>
                                <p class="bpp-professional-experience">
                                    <span class="dashicons dashicons-businessman"></span>
                                    <?php printf(_n('%s year experience', '%s years experience', (int)$years_experience, 'black-potential-pipeline'), $years_experience); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="bpp-professional-content">
                        <div class="bpp-professional-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <?php if (!empty($skills_array)) : ?>
                            <div class="bpp-professional-skills">
                                <h4><?php _e('Skills', 'black-potential-pipeline'); ?></h4>
                                <div class="bpp-skills-tags">
                                    <?php foreach ($skills_array as $skill) : ?>
                                        <span class="bpp-skill-tag"><?php echo esc_html(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bpp-professional-footer">
                        <a href="<?php the_permalink(); ?>" class="bpp-view-profile bpp-button">
                            <?php _e('View Full Profile', 'black-potential-pipeline'); ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php if ($total_pages > 1) : ?>
            <div class="bpp-pagination">
                <?php
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
                ?>
            </div>
        <?php endif; ?>
        
    <?php else : ?>
        <div class="bpp-no-results">
            <p><?php _e('No professionals found matching your criteria.', 'black-potential-pipeline'); ?></p>
            <?php if (!empty($search_query) || !empty($industry_filter) || !empty($experience_filter)) : ?>
                <p>
                    <a href="<?php echo $current_url; ?>" class="bpp-button">
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
    $('.bpp-layout-toggle button').on('click', function() {
        const layout = $(this).data('layout');
        
        // Update active button
        $('.bpp-layout-toggle button').removeClass('active');
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
        $('.bpp-toggle-' + savedLayout).trigger('click');
    }
});
</script> 