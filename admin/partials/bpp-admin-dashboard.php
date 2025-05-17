<?php
/**
 * Provide a admin area view for the plugin dashboard
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://codemygig.com,
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap bpp-admin-dashboard">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-groups"></span>
        <?php echo esc_html__('Black Potential Pipeline', 'black-potential-pipeline'); ?>
    </h1>
    
    <div class="bpp-admin-header">
        <div class="bpp-logo">
            <!-- Add your logo here if needed -->
        </div>
        <div class="bpp-version">
            <?php echo esc_html__('Version', 'black-potential-pipeline') . ' ' . esc_html(BPP_VERSION); ?>
        </div>
    </div>
    
    <div class="bpp-admin-welcome">
        <h2><?php echo esc_html__('Welcome to the Black Potential Pipeline', 'black-potential-pipeline'); ?></h2>
        <p class="bpp-description">
            <?php echo esc_html__('This plugin helps you create a curated database of Black professionals seeking green jobs. Use the tools below to manage applications and customize your pipeline.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <div class="bpp-admin-cards">
        <div class="bpp-card">
            <div class="bpp-card-header">
                <span class="dashicons dashicons-welcome-widgets-menus"></span>
                <h3><?php echo esc_html__('Quick Stats', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <?php
                // Get application counts
                $post_counts = wp_count_posts('bpp_applicant');
                $new_count = isset($post_counts->draft) ? $post_counts->draft : 0;
                $approved_count = isset($post_counts->publish) ? $post_counts->publish : 0;
                
                // Count rejected applications properly (checking meta fields)
                $rejected_private = new WP_Query(array(
                    'post_type' => 'bpp_applicant',
                    'post_status' => 'private',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => 'bpp_rejected',
                            'value' => '1',
                            'compare' => '=',
                        ),
                    ),
                ));
                
                $rejected_trash = new WP_Query(array(
                    'post_type' => 'bpp_applicant',
                    'post_status' => 'trash',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => 'bpp_application_status',
                            'value' => 'rejected',
                            'compare' => '=',
                        ),
                    ),
                ));
                
                $rejected_count = $rejected_private->found_posts + $rejected_trash->found_posts;
                $total_count = $new_count + $approved_count + $rejected_count;
                
                // Get industry counts
                $industry_terms = get_terms(array(
                    'taxonomy' => 'bpp_industry',
                    'hide_empty' => false,
                ));
                
                // Get featured count
                $featured_count = get_posts(array(
                    'post_type' => 'bpp_applicant',
                    'post_status' => 'publish',
                    'numberposts' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'bpp_featured',
                            'value' => '1',
                            'compare' => '=',
                        ),
                    ),
                ));
                $featured_count = count($featured_count);
                ?>
                <div class="bpp-stats-grid">
                    <div class="bpp-stat-item">
                        <span class="bpp-stat-number"><?php echo esc_html($total_count); ?></span>
                        <span class="bpp-stat-label"><?php echo esc_html__('Total Applications', 'black-potential-pipeline'); ?></span>
                    </div>
                    <div class="bpp-stat-item">
                        <span class="bpp-stat-number"><?php echo esc_html($new_count); ?></span>
                        <span class="bpp-stat-label"><?php echo esc_html__('New Applications', 'black-potential-pipeline'); ?></span>
                    </div>
                    <div class="bpp-stat-item">
                        <span class="bpp-stat-number"><?php echo esc_html($approved_count); ?></span>
                        <span class="bpp-stat-label"><?php echo esc_html__('Approved Professionals', 'black-potential-pipeline'); ?></span>
                    </div>
                    <div class="bpp-stat-item">
                        <span class="bpp-stat-number"><?php echo esc_html($rejected_count); ?></span>
                        <span class="bpp-stat-label"><?php echo esc_html__('Rejected Applications', 'black-potential-pipeline'); ?></span>
                    </div>
                    <div class="bpp-stat-item">
                        <span class="bpp-stat-number"><?php echo esc_html($featured_count); ?></span>
                        <span class="bpp-stat-label"><?php echo esc_html__('Featured Professionals', 'black-potential-pipeline'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bpp-card">
            <div class="bpp-card-header">
                <span class="dashicons dashicons-category"></span>
                <h3><?php echo esc_html__('Industry Breakdown', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <?php if (!empty($industry_terms) && !is_wp_error($industry_terms)) : ?>
                    <ul class="bpp-industry-list">
                        <?php foreach ($industry_terms as $term) : 
                            $count = get_posts(array(
                                'post_type' => 'bpp_applicant',
                                'post_status' => 'publish',
                                'numberposts' => -1,
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'bpp_industry',
                                        'field' => 'term_id',
                                        'terms' => $term->term_id,
                                    ),
                                ),
                            ));
                        ?>
                            <li>
                                <span class="bpp-industry-name"><?php echo esc_html($term->name); ?></span>
                                <span class="bpp-industry-count"><?php echo esc_html(count($count)); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php echo esc_html__('No industry categories found.', 'black-potential-pipeline'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bpp-card">
            <div class="bpp-card-header">
                <span class="dashicons dashicons-admin-tools"></span>
                <h3><?php echo esc_html__('Quick Actions', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <div class="bpp-action-buttons">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-new-applications')); ?>" class="button button-primary">
                        <span class="dashicons dashicons-welcome-write-blog"></span>
                        <?php echo esc_html__('Review New Applications', 'black-potential-pipeline'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-approved')); ?>" class="button">
                        <span class="dashicons dashicons-yes"></span>
                        <?php echo esc_html__('View Approved Professionals', 'black-potential-pipeline'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-settings')); ?>" class="button">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php echo esc_html__('Configure Settings', 'black-potential-pipeline'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="bpp-card">
            <div class="bpp-card-header">
                <span class="dashicons dashicons-star-filled"></span>
                <h3><?php echo esc_html__('Featured Professionals', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <?php
                // Get featured professionals
                $featured_professionals = get_posts(array(
                    'post_type' => 'bpp_applicant',
                    'post_status' => 'publish',
                    'posts_per_page' => 5, // Show only 5 featured pros
                    'meta_query' => array(
                        array(
                            'key' => 'bpp_featured',
                            'value' => '1',
                            'compare' => '=',
                        ),
                    ),
                ));
                
                if (!empty($featured_professionals)) :
                ?>
                    <div class="bpp-featured-professionals">
                        <ul class="bpp-featured-list">
                            <?php foreach ($featured_professionals as $professional) : 
                                $job_title = get_post_meta($professional->ID, 'bpp_job_title', true);
                                $industry_terms = wp_get_post_terms($professional->ID, 'bpp_industry', array('fields' => 'names'));
                                $industry = '';
                                if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                                    $industry = $industry_terms[0];
                                }
                            ?>
                                <li class="bpp-featured-item">
                                    <div class="bpp-featured-pro-info">
                                        <h4><?php echo esc_html($professional->post_title); ?></h4>
                                        <p>
                                            <?php if (!empty($job_title)) : ?>
                                                <span class="bpp-job-title"><?php echo esc_html($job_title); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($industry)) : ?>
                                                <span class="bpp-industry"><?php echo esc_html($industry); ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="bpp-featured-actions">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-applicant-profile&id=' . $professional->ID)); ?>" class="button button-small">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <?php echo esc_html__('Profile', 'black-potential-pipeline'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(get_edit_post_link($professional->ID)); ?>" class="button button-small">
                                            <span class="dashicons dashicons-edit"></span>
                                            <?php echo esc_html__('Edit', 'black-potential-pipeline'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(get_permalink($professional->ID)); ?>" class="button button-small" target="_blank">
                                            <span class="dashicons dashicons-visibility"></span>
                                            <?php echo esc_html__('View', 'black-potential-pipeline'); ?>
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="bpp-featured-footer">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-approved')); ?>" class="button">
                                <?php echo esc_html__('Manage Featured Professionals', 'black-potential-pipeline'); ?>
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <p><?php echo esc_html__('No featured professionals yet. Use the "Feature" button on approved professionals to highlight them.', 'black-potential-pipeline'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-approved')); ?>" class="button">
                        <?php echo esc_html__('Go to Approved Professionals', 'black-potential-pipeline'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bpp-card">
            <div class="bpp-card-header">
                <span class="dashicons dashicons-editor-help"></span>
                <h3><?php echo esc_html__('Help & Resources', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <ul class="bpp-help-list">
                    <li>
                        <span class="dashicons dashicons-book"></span>
                        <a href="#" target="_blank"><?php echo esc_html__('Plugin Documentation', 'black-potential-pipeline'); ?></a>
                    </li>
                    <li>
                        <span class="dashicons dashicons-editor-code"></span>
                        <a href="#" target="_blank"><?php echo esc_html__('Shortcode Reference', 'black-potential-pipeline'); ?></a>
                    </li>
                    <li>
                        <span class="dashicons dashicons-sos"></span>
                        <a href="#" target="_blank"><?php echo esc_html__('Get Support', 'black-potential-pipeline'); ?></a>
                    </li>
                </ul>
                <div class="bpp-shortcode-reference">
                    <h4><?php echo esc_html__('Available Shortcodes', 'black-potential-pipeline'); ?></h4>
                    <code>[black_potential_pipeline_form]</code> - <?php echo esc_html__('Application form', 'black-potential-pipeline'); ?><br>
                    <code>[black_potential_pipeline_directory]</code> - <?php echo esc_html__('Full directory', 'black-potential-pipeline'); ?><br>
                    <code>[black_potential_pipeline_category category="nature-based-work"]</code> - <?php echo esc_html__('Category directory', 'black-potential-pipeline'); ?><br>
                    <code>[black_potential_pipeline_featured]</code> - <?php echo esc_html__('Featured candidates', 'black-potential-pipeline'); ?><br>
                    <code>[black_potential_pipeline_stats]</code> - <?php echo esc_html__('Statistics display', 'black-potential-pipeline'); ?>
                </div>
            </div>
        </div>
    </div>
</div> 