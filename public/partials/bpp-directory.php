<?php
/**
 * Provide a public-facing view for the professionals directory
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
$per_page = intval($atts['per_page']);
$layout = sanitize_text_field($atts['layout']);

// Get industry terms for filter
$industry_terms = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));

// Get current page
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Get applicants query
$args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'paged' => $paged,
    'orderby' => 'title',
    'order' => 'ASC',
);

// Apply filters if set
$industry_filter = isset($_GET['industry']) ? sanitize_text_field($_GET['industry']) : '';
if (!empty($industry_filter)) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'bpp_industry',
            'field' => 'slug',
            'terms' => $industry_filter,
        ),
    );
}

$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

$applicants_query = new WP_Query($args);
?>

<div class="bpp-directory-container">
    <div class="bpp-directory-header">
        <h2 class="bpp-directory-title"><?php echo esc_html($atts['title']); ?></h2>
        <p class="bpp-directory-description">
            <?php echo esc_html__('Browse our curated database of talented Black professionals seeking opportunities in green industries.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <div class="bpp-directory-filters">
        <form method="get" class="bpp-filter-form">
            <div class="bpp-filter-row">
                <div class="bpp-filter-field">
                    <label for="bpp-industry-filter"><?php echo esc_html__('Filter by Industry', 'black-potential-pipeline'); ?></label>
                    <select id="bpp-industry-filter" name="industry">
                        <option value=""><?php echo esc_html__('All Industries', 'black-potential-pipeline'); ?></option>
                        <?php if (!empty($industry_terms) && !is_wp_error($industry_terms)) : ?>
                            <?php foreach ($industry_terms as $term) : ?>
                                <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($industry_filter, $term->slug); ?>>
                                    <?php echo esc_html($term->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="bpp-filter-field">
                    <label for="bpp-search"><?php echo esc_html__('Search', 'black-potential-pipeline'); ?></label>
                    <input type="text" id="bpp-search" name="search" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php echo esc_attr__('Search by name, skills, or location', 'black-potential-pipeline'); ?>">
                </div>
                
                <div class="bpp-filter-actions">
                    <button type="submit" class="bpp-filter-button"><?php echo esc_html__('Apply Filters', 'black-potential-pipeline'); ?></button>
                    <a href="<?php echo esc_url(remove_query_arg(array('industry', 'search'))); ?>" class="bpp-reset-button"><?php echo esc_html__('Reset', 'black-potential-pipeline'); ?></a>
                </div>
            </div>
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
            <p class="bpp-results-count">
                <?php
                echo sprintf(
                    esc_html(_n('Showing %1$d of %2$d professional', 'Showing %1$d of %2$d professionals', $applicants_query->found_posts, 'black-potential-pipeline')),
                    $applicants_query->post_count,
                    $applicants_query->found_posts
                );
                ?>
            </p>
        </div>
        
        <div class="bpp-directory-content bpp-layout-<?php echo esc_attr($layout); ?>">
            <?php while ($applicants_query->have_posts()) : $applicants_query->the_post(); 
                $post_id = get_the_ID();
                $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                $location = get_post_meta($post_id, 'bpp_location', true);
                $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
                $skills = get_post_meta($post_id, 'bpp_skills', true);
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
                            <div class="bpp-professional-meta">
                                <?php if (!empty($industry)) : ?>
                                    <span class="bpp-professional-industry">
                                        <span class="dashicons dashicons-category"></span>
                                        <?php echo esc_html($industry); ?>
                                    </span>
                                <?php endif; ?>
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
                    
                    <div class="bpp-professional-content">
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
                    </div>
                    
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
            <p><?php echo esc_html__('No professionals found matching your criteria.', 'black-potential-pipeline'); ?></p>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div> 