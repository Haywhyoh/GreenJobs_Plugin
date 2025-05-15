<?php
/**
 * Provide an admin area view for managing rejected applications
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get rejected applications (private status with rejected meta)
$args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'private',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
    'meta_query' => array(
        array(
            'key' => 'bpp_rejected',
            'value' => '1',
            'compare' => '=',
        ),
    ),
);
$rejected_query = new WP_Query($args);

// Get industry terms for filtering
$industry_terms = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));

// Get selected industry filter
$selected_industry = isset($_GET['industry']) ? sanitize_text_field($_GET['industry']) : '';

// Apply filter if selected
if (!empty($selected_industry)) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'bpp_industry',
            'field' => 'slug',
            'terms' => $selected_industry,
        ),
    );
    $rejected_query = new WP_Query($args);
}
?>

<div class="wrap bpp-admin-rejected">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Rejected Applications', 'black-potential-pipeline'); ?></h1>
    
    <style>
        /* Responsive Table Styles */
        .bpp-table-container {
            margin-top: 20px;
            overflow-x: auto;
        }
        
        .bpp-rejected-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .bpp-rejected-table th {
            background-color: #f1f1f1;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }
        
        .bpp-rejected-table td {
            padding: 12px 10px;
            vertical-align: top;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .bpp-name-column {
            font-weight: 500;
        }
        
        .bpp-thumbnail-small {
            max-width: 40px;
            margin-top: 5px;
        }
        
        .bpp-thumbnail-small img {
            width: 100%;
            height: auto;
            border-radius: 50%;
        }
        
        .bpp-action-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .bpp-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            z-index: 100;
            min-width: 200px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border-radius: 4px;
            padding: 5px 0;
        }
        
        .bpp-dropdown-item {
            display: block;
            width: 100%;
            text-align: left;
            padding: 8px 12px;
            font-size: 13px;
            color: #333;
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .bpp-dropdown-item:hover {
            background-color: #f8f8f8;
            color: #0073aa;
        }
        
        .bpp-dropdown-item .dashicons {
            margin-right: 8px;
            vertical-align: middle;
        }
        
        /* Details Row Styles */
        .bpp-details-row td {
            padding: 0;
        }
        
        .bpp-rejected-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .bpp-details-section {
            margin-bottom: 20px;
        }
        
        .bpp-details-section h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #23282d;
            font-weight: 600;
        }
        
        .bpp-skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .bpp-skill-tag {
            background-color: #e5f7ff;
            color: #0073aa;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
        }
        
        /* Responsive table */
        @media screen and (max-width: 782px) {
            .bpp-rejected-table {
                display: block;
            }
            
            .bpp-rejected-table thead {
                display: none;
            }
            
            .bpp-rejected-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #e5e5e5;
            }
            
            .bpp-rejected-table td {
                display: block;
                padding: 10px;
                text-align: right;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .bpp-rejected-table td:last-child {
                border-bottom: none;
            }
            
            .bpp-rejected-table td:before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 12px;
            }
            
            .bpp-action-dropdown {
                display: block;
                text-align: center;
            }
            
            .bpp-dropdown-menu {
                right: auto;
                left: 50%;
                transform: translateX(-50%);
            }
        }
    </style>
    
    <div class="bpp-admin-header">
        <p class="bpp-description">
            <?php echo esc_html__('View and manage applications that have been rejected from the Black Potential Pipeline.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <?php if (!empty($industry_terms) && !is_wp_error($industry_terms)) : ?>
        <div class="bpp-filter-bar">
            <form method="get" action="">
                <input type="hidden" name="page" value="bpp-rejected">
                <select name="industry">
                    <option value=""><?php echo esc_html__('All Industries', 'black-potential-pipeline'); ?></option>
                    <?php foreach ($industry_terms as $term) : ?>
                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($selected_industry, $term->slug); ?>>
                            <?php echo esc_html($term->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button"><?php echo esc_html__('Filter', 'black-potential-pipeline'); ?></button>
                <?php if (!empty($selected_industry)) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-rejected')); ?>" class="button">
                        <?php echo esc_html__('Clear Filter', 'black-potential-pipeline'); ?>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>
    
    <?php if ($rejected_query->have_posts()) : ?>
        <div class="bpp-table-container">
            <table class="bpp-rejected-table widefat striped responsive">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Name', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Job Title', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Industry', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Rejection Date', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Reason', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Actions', 'black-potential-pipeline'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rejected_query->have_posts()) : $rejected_query->the_post(); 
                        $post_id = get_the_ID();
                        $rejection_date = get_post_meta($post_id, 'bpp_rejection_date', true);
                        $formatted_date = !empty($rejection_date) ? date_i18n(get_option('date_format'), strtotime($rejection_date)) : '';
                        $rejection_reason = get_post_meta($post_id, 'bpp_rejection_reason', true);
                        
                        $email = get_post_meta($post_id, 'bpp_email', true);
                        $phone = get_post_meta($post_id, 'bpp_phone', true);
                        $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                        $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
                        $location = get_post_meta($post_id, 'bpp_location', true);
                        $linkedin = get_post_meta($post_id, 'bpp_linkedin', true);
                        $skills = get_post_meta($post_id, 'bpp_skills', true);
                        
                        $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                        $industry = '';
                        if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                            $industry = $industry_terms[0];
                        }
                        
                        $resume_id = get_post_meta($post_id, 'bpp_resume', true);
                        $resume_url = !empty($resume_id) ? wp_get_attachment_url($resume_id) : '';
                        
                        // Format experience
                        $experience_text = !empty($years_experience) ? 
                            sprintf(_n('%d year', '%d years', $years_experience, 'black-potential-pipeline'), $years_experience) : 
                            '';
                    ?>
                        <tr class="bpp-rejected-row" data-id="<?php echo esc_attr($post_id); ?>">
                            <td class="bpp-name-column" data-label="<?php echo esc_attr__('Name', 'black-potential-pipeline'); ?>">
                                <strong><?php the_title(); ?></strong>
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="bpp-thumbnail-small">
                                        <?php the_post_thumbnail('thumbnail'); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="bpp-job-column" data-label="<?php echo esc_attr__('Job Title', 'black-potential-pipeline'); ?>"><?php echo esc_html($job_title); ?></td>
                            <td class="bpp-industry-column" data-label="<?php echo esc_attr__('Industry', 'black-potential-pipeline'); ?>"><?php echo esc_html($industry); ?></td>
                            <td class="bpp-date-column" data-label="<?php echo esc_attr__('Rejection Date', 'black-potential-pipeline'); ?>"><?php echo esc_html($formatted_date); ?></td>
                            <td class="bpp-reason-column" data-label="<?php echo esc_attr__('Reason', 'black-potential-pipeline'); ?>">
                                <?php 
                                if (!empty($rejection_reason)) {
                                    echo '<span class="bpp-reason-preview">' . esc_html(substr($rejection_reason, 0, 50)) . (strlen($rejection_reason) > 50 ? '...' : '') . '</span>';
                                } else {
                                    echo '<em>' . esc_html__('No reason provided', 'black-potential-pipeline') . '</em>';
                                }
                                ?>
                            </td>
                            <td class="bpp-actions-column" data-label="<?php echo esc_attr__('Actions', 'black-potential-pipeline'); ?>">
                                <div class="bpp-action-dropdown">
                                    <button class="button bpp-action-toggle">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                        <?php echo esc_html__('Options', 'black-potential-pipeline'); ?>
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                    <div class="bpp-dropdown-menu">
                                        <button type="button" class="bpp-dropdown-item bpp-reconsider-button" data-id="<?php echo esc_attr($post_id); ?>">
                                            <span class="dashicons dashicons-yes"></span>
                                            <?php echo esc_html__('Reconsider & Approve', 'black-potential-pipeline'); ?>
                                        </button>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-applicant-profile&id=' . $post_id)); ?>" class="bpp-dropdown-item">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <?php echo esc_html__('Detailed View/Edit', 'black-potential-pipeline'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="bpp-dropdown-item">
                                            <span class="dashicons dashicons-edit"></span>
                                            <?php echo esc_html__('Edit', 'black-potential-pipeline'); ?>
                                        </a>
                                        <?php if (!empty($resume_url)) : ?>
                                            <a href="<?php echo esc_url($resume_url); ?>" class="bpp-dropdown-item" target="_blank">
                                                <span class="dashicons dashicons-media-document"></span>
                                                <?php echo esc_html__('View Resume', 'black-potential-pipeline'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr class="bpp-details-row" style="display: none;">
                            <td colspan="6">
                                <div class="bpp-rejected-details">
                                    <div class="bpp-details-section">
                                        <h4><?php echo esc_html__('Contact Information', 'black-potential-pipeline'); ?></h4>
                                        <p><strong><?php echo esc_html__('Email:', 'black-potential-pipeline'); ?></strong> <?php echo esc_html($email); ?></p>
                                        <?php if (!empty($phone)) : ?>
                                            <p><strong><?php echo esc_html__('Phone:', 'black-potential-pipeline'); ?></strong> <?php echo esc_html($phone); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($location)) : ?>
                                            <p><strong><?php echo esc_html__('Location:', 'black-potential-pipeline'); ?></strong> <?php echo esc_html($location); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($experience_text)) : ?>
                                            <p><strong><?php echo esc_html__('Experience:', 'black-potential-pipeline'); ?></strong> <?php echo esc_html($experience_text); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($rejection_reason)) : ?>
                                        <div class="bpp-details-section">
                                            <h4><?php echo esc_html__('Rejection Reason', 'black-potential-pipeline'); ?></h4>
                                            <div class="bpp-reason-content">
                                                <?php echo esc_html($rejection_reason); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($skills)) : ?>
                                        <div class="bpp-details-section">
                                            <h4><?php echo esc_html__('Skills', 'black-potential-pipeline'); ?></h4>
                                            <div class="bpp-skills-list">
                                                <?php 
                                                $skills_array = explode(',', $skills);
                                                foreach ($skills_array as $skill) {
                                                    echo '<span class="bpp-skill-tag">' . esc_html(trim($skill)) . '</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="bpp-details-section">
                                        <h4><?php echo esc_html__('Cover Letter / Personal Statement', 'black-potential-pipeline'); ?></h4>
                                        <div class="bpp-rejected-excerpt">
                                            <?php the_content(); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <?php wp_reset_postdata(); ?>
        
    <?php else : ?>
        <div class="bpp-no-rejected">
            <?php if (!empty($selected_industry)) : ?>
                <p><?php echo esc_html__('No rejected applications found in this industry.', 'black-potential-pipeline'); ?></p>
            <?php else : ?>
                <p><?php echo esc_html__('No rejected applications found.', 'black-potential-pipeline'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Reconsider Application Modal -->
    <div id="bpp-reconsider-modal" class="bpp-modal" style="display: none;">
        <div class="bpp-modal-content">
            <span class="bpp-modal-close">&times;</span>
            <h2><?php echo esc_html__('Reconsider Application', 'black-potential-pipeline'); ?></h2>
            <p><?php echo esc_html__('Are you sure you want to reconsider and approve this application?', 'black-potential-pipeline'); ?></p>
            <p><?php echo esc_html__('This will move the application to the approved professionals list.', 'black-potential-pipeline'); ?></p>
            <input type="hidden" id="bpp-reconsider-applicant-id" value="">
            <div class="bpp-modal-actions">
                <button type="button" class="button button-primary" id="bpp-confirm-reconsider">
                    <?php echo esc_html__('Confirm Approval', 'black-potential-pipeline'); ?>
                </button>
                <button type="button" class="button" id="bpp-cancel-reconsider">
                    <?php echo esc_html__('Cancel', 'black-potential-pipeline'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Toggle dropdown menu
    $('.bpp-action-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const dropdown = $(this).next('.bpp-dropdown-menu');
        $('.bpp-dropdown-menu').not(dropdown).hide();
        dropdown.toggle();
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if(!$(e.target).closest('.bpp-action-dropdown').length) {
            $('.bpp-dropdown-menu').hide();
        }
    });
    
    // Row click to expand details
    $('.bpp-rejected-row').on('click', function(e) {
        if(!$(e.target).closest('.bpp-action-dropdown').length) {
            const postId = $(this).data('id');
            $(this).next('.bpp-details-row').toggle();
        }
    });
    
    // Reconsider & Approve button handler
    $('.bpp-reconsider-button').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if(confirm('<?php echo esc_js(__('Are you sure you want to reconsider and approve this application?', 'black-potential-pipeline')); ?>')) {
            const postId = $(this).data('id');
            const $row = $(this).closest('tr');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bpp_reconsider_applicant',
                    post_id: postId,
                    security: '<?php echo wp_create_nonce('bpp_reconsider_applicant_nonce'); ?>'
                },
                beforeSend: function() {
                    $row.css('opacity', '0.5');
                },
                success: function(response) {
                    if(response.success) {
                        $row.fadeOut(300, function() {
                            $row.next('.bpp-details-row').remove();
                            $row.remove();
                            
                            // Show message if no more rows
                            if($('.bpp-rejected-row').length === 0) {
                                $('.bpp-table-container').html('<div class="bpp-no-rejected"><p><?php echo esc_js(__('No rejected applications found.', 'black-potential-pipeline')); ?></p></div>');
                            }
                        });
                    } else {
                        alert(response.data || '<?php echo esc_js(__('Error reconsidering applicant', 'black-potential-pipeline')); ?>');
                        $row.css('opacity', '1');
                    }
                },
                error: function() {
                    alert('<?php echo esc_js(__('Error reconsidering applicant', 'black-potential-pipeline')); ?>');
                    $row.css('opacity', '1');
                }
            });
        }
    });
});
</script> 