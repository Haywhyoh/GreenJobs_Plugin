<?php
/**
 * Provide an admin area view for managing approved professionals
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

// Get approved professionals (publish status)
$args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
);
$professionals_query = new WP_Query($args);

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
    $professionals_query = new WP_Query($args);
}
?>

<div class="wrap bpp-admin-approved">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-groups"></span>
        <?php echo esc_html__('Approved Professionals', 'black-potential-pipeline'); ?>
    </h1>
    
    <div class="bpp-admin-header">
        <p class="bpp-description">
            <?php echo esc_html__('Manage the professionals currently displayed in the Black Potential Pipeline directory.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <?php if (!empty($industry_terms) && !is_wp_error($industry_terms)) : ?>
        <div class="bpp-filter-bar">
            <form method="get" action="">
                <input type="hidden" name="page" value="bpp-approved">
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
                    <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-approved')); ?>" class="button">
                        <?php echo esc_html__('Clear Filter', 'black-potential-pipeline'); ?>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>
    
    <?php if ($professionals_query->have_posts()) : ?>
        <div class="bpp-table-container">
            <table class="bpp-professionals-table widefat striped responsive">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Name', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Job Title', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Industry', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Location', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Experience', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Status', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Actions', 'black-potential-pipeline'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($professionals_query->have_posts()) : $professionals_query->the_post(); 
                        $post_id = get_the_ID();
                        $approval_date = get_post_meta($post_id, 'bpp_approval_date', true);
                        $formatted_date = !empty($approval_date) ? date_i18n(get_option('date_format'), strtotime($approval_date)) : '';
                        
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

                        // Get profile views if available
                        $profile_views = get_post_meta($post_id, 'bpp_profile_views', true);

                        // Get featured status
                        $featured = get_post_meta($post_id, 'bpp_featured', true);
                        
                        // Format experience
                        $experience_text = !empty($years_experience) ? 
                            sprintf(_n('%d year', '%d years', $years_experience, 'black-potential-pipeline'), $years_experience) : 
                            '';
                    ?>
                        <tr class="bpp-professional-row" data-id="<?php echo esc_attr($post_id); ?>">
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
                            <td class="bpp-location-column" data-label="<?php echo esc_attr__('Location', 'black-potential-pipeline'); ?>"><?php echo esc_html($location); ?></td>
                            <td class="bpp-experience-column" data-label="<?php echo esc_attr__('Experience', 'black-potential-pipeline'); ?>"><?php echo esc_html($experience_text); ?></td>
                            <td class="bpp-status-column" data-label="<?php echo esc_attr__('Status', 'black-potential-pipeline'); ?>">
                                <?php if (!empty($featured)) : ?>
                                    <span class="bpp-featured-badge">
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <?php echo esc_html__('Featured', 'black-potential-pipeline'); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="bpp-approved-badge">
                                        <span class="dashicons dashicons-yes"></span>
                                        <?php echo esc_html__('Approved', 'black-potential-pipeline'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="bpp-actions-column" data-label="<?php echo esc_attr__('Actions', 'black-potential-pipeline'); ?>">
                                <div class="bpp-action-dropdown">
                                    <button class="button bpp-action-toggle">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                        <?php echo esc_html__('Options', 'black-potential-pipeline'); ?>
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                    <div class="bpp-dropdown-menu">
                                        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank" class="bpp-dropdown-item">
                                            <span class="dashicons dashicons-visibility"></span>
                                            <?php echo esc_html__('View Profile', 'black-potential-pipeline'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-applicant-profile&id=' . $post_id)); ?>" class="bpp-dropdown-item">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <?php echo esc_html__('Detailed View/Edit', 'black-potential-pipeline'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="bpp-dropdown-item">
                                            <span class="dashicons dashicons-edit"></span>
                                            <?php echo esc_html__('Edit', 'black-potential-pipeline'); ?>
                                        </a>
                                        <button type="button" class="bpp-dropdown-item bpp-remove-button" data-id="<?php echo esc_attr($post_id); ?>">
                                            <span class="dashicons dashicons-no"></span>
                                            <?php echo esc_html__('Remove', 'black-potential-pipeline'); ?>
                                        </button>
                                        <button type="button" class="bpp-dropdown-item bpp-<?php echo !empty($featured) ? 'unfeature' : 'feature'; ?>-button" data-id="<?php echo esc_attr($post_id); ?>">
                                            <span class="dashicons dashicons-<?php echo !empty($featured) ? 'star-filled' : 'star-empty'; ?>"></span>
                                            <?php echo !empty($featured) ? esc_html__('Unfeature', 'black-potential-pipeline') : esc_html__('Feature', 'black-potential-pipeline'); ?>
                                        </button>
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
                            <td colspan="7">
                                <div class="bpp-professional-details">
                                    <div class="bpp-details-section">
                                        <h4><?php echo esc_html__('Contact Information', 'black-potential-pipeline'); ?></h4>
                                        <p><strong><?php echo esc_html__('Email:', 'black-potential-pipeline'); ?></strong> <?php echo esc_html($email); ?></p>
                                        <?php if (!empty($phone)) : ?>
                                            <p><strong><?php echo esc_html__('Phone:', 'black-potential-pipeline'); ?></strong> <?php echo esc_html($phone); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($linkedin)) : ?>
                                            <p><strong><?php echo esc_html__('LinkedIn:', 'black-potential-pipeline'); ?></strong> <a href="<?php echo esc_url($linkedin); ?>" target="_blank"><?php echo esc_html__('View Profile', 'black-potential-pipeline'); ?></a></p>
                                        <?php endif; ?>
                                    </div>
                                    
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
                                        <h4><?php echo esc_html__('Bio / Personal Statement', 'black-potential-pipeline'); ?></h4>
                                        <div class="bpp-professional-excerpt">
                                            <?php the_excerpt(); ?>
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
        <div class="bpp-no-professionals">
            <?php if (!empty($selected_industry)) : ?>
                <p><?php echo esc_html__('No approved professionals found in this industry.', 'black-potential-pipeline'); ?></p>
            <?php else : ?>
                <p><?php echo esc_html__('No approved professionals found.', 'black-potential-pipeline'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Remove Professional Modal -->
    <div id="bpp-remove-modal" class="bpp-modal" style="display: none;">
        <div class="bpp-modal-content">
            <span class="bpp-modal-close">&times;</span>
            <h2><?php echo esc_html__('Remove Professional', 'black-potential-pipeline'); ?></h2>
            <p><?php echo esc_html__('Are you sure you want to remove this professional from the directory?', 'black-potential-pipeline'); ?></p>
            <p><?php echo esc_html__('Note: This will move the professional to the rejected status.', 'black-potential-pipeline'); ?></p>
            <input type="hidden" id="bpp-remove-professional-id" value="">
            <div class="bpp-modal-actions">
                <button type="button" class="button button-primary" id="bpp-confirm-remove">
                    <?php echo esc_html__('Confirm Removal', 'black-potential-pipeline'); ?>
                </button>
                <button type="button" class="button" id="bpp-cancel-remove">
                    <?php echo esc_html__('Cancel', 'black-potential-pipeline'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Remove button click handler
    $('.bpp-remove-button').on('click', function() {
        var professionalId = $(this).data('id');
        $('#bpp-remove-professional-id').val(professionalId);
        $('#bpp-remove-modal').show();
    });
    
    // Feature button click handler
    $('.bpp-feature-button').on('click', function() {
        var professionalId = $(this).data('id');
        var $button = $(this);
        var $row = $button.closest('tr.bpp-professional-row');
        
        $.ajax({
            url: bpp_admin_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'bpp_toggle_featured',
                applicant_id: professionalId,
                featured: 1,
                nonce: bpp_admin_obj.nonce
            },
            beforeSend: function() {
                $button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // Update the UI to reflect the new featured status
                    $row.find('.bpp-status-column').html('<span class="bpp-featured-badge"><span class="dashicons dashicons-star-filled"></span>' + bpp_admin_obj.i18n.featured_text + '</span>');
                    
                    // Reload the page to refresh all elements
                    location.reload();
                } else {
                    alert(response.data || bpp_admin_obj.i18n.error);
                }
            },
            error: function() {
                alert(bpp_admin_obj.i18n.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    
    // Unfeature button click handler
    $('.bpp-unfeature-button').on('click', function() {
        var professionalId = $(this).data('id');
        var $button = $(this);
        var $row = $button.closest('tr.bpp-professional-row');
        
        $.ajax({
            url: bpp_admin_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'bpp_toggle_featured',
                applicant_id: professionalId,
                featured: 0,
                nonce: bpp_admin_obj.nonce
            },
            beforeSend: function() {
                $button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // Update the UI to reflect the new unfeatured status
                    $row.find('.bpp-status-column').html('<span class="bpp-approved-badge"><span class="dashicons dashicons-yes"></span>' + bpp_admin_obj.i18n.approved_text + '</span>');
                    
                    // Reload the page to refresh all elements
                    location.reload();
                } else {
                    alert(response.data || bpp_admin_obj.i18n.error);
                }
            },
            error: function() {
                alert(bpp_admin_obj.i18n.error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    
    // Modal close button
    $('.bpp-modal-close, #bpp-cancel-remove').on('click', function() {
        $('#bpp-remove-modal').hide();
    });
    
    // Confirm removal
    $('#bpp-confirm-remove').on('click', function() {
        var professionalId = $('#bpp-remove-professional-id').val();
        var $row = $('.bpp-professional-row[data-id="' + professionalId + '"]');
        
        $.ajax({
            url: bpp_admin_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'bpp_reject_applicant',
                applicant_id: professionalId,
                reason: 'Removed from directory by administrator',
                nonce: bpp_admin_obj.nonce
            },
            beforeSend: function() {
                $row.addClass('bpp-loading');
            },
            success: function(response) {
                if (response.success) {
                    $('#bpp-remove-modal').hide();
                    $row.fadeOut(400, function() {
                        // Also remove the details row
                        $row.next('.bpp-details-row').remove();
                        $row.remove();
                        
                        // Show message if no more professionals
                        if ($('.bpp-professional-row').length === 0) {
                            $('.bpp-professionals-table').replaceWith('<div class="bpp-no-professionals"><p>' + 
                                bpp_admin_obj.i18n.no_professionals + '</p></div>');
                        }
                    });
                } else {
                    alert(response.data || bpp_admin_obj.i18n.error);
                }
            },
            error: function() {
                alert(bpp_admin_obj.i18n.error);
            },
            complete: function() {
                $row.removeClass('bpp-loading');
            }
        });
    });
    
    // Close modal if clicked outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#bpp-remove-modal')) {
            $('#bpp-remove-modal').hide();
        }
    });
    
    // Toggle action dropdown
    $('.bpp-action-toggle').on('click', function(e) {
        e.stopPropagation();
        var $dropdown = $(this).next('.bpp-dropdown-menu');
        $('.bpp-dropdown-menu').not($dropdown).removeClass('active');
        $dropdown.toggleClass('active');
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function() {
        $('.bpp-dropdown-menu').removeClass('active');
    });
    
    // Toggle details row
    $('.bpp-professional-row').on('click', function(e) {
        if (!$(e.target).closest('.bpp-actions-column').length) {
            var $detailsRow = $(this).next('.bpp-details-row');
            $detailsRow.toggle();
            $(this).toggleClass('expanded');
        }
    });
});
</script>

<style>
/* Table styles */
.bpp-table-container {
    margin-top: 20px;
    overflow-x: auto;
}

.bpp-professionals-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.bpp-professionals-table th,
.bpp-professionals-table td {
    padding: 12px;
    text-align: left;
    vertical-align: middle;
}

.bpp-name-column {
    font-weight: bold;
    min-width: 150px;
}

.bpp-thumbnail-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    margin-top: 5px;
}

.bpp-thumbnail-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bpp-professional-row {
    cursor: pointer;
}

.bpp-professional-row.expanded {
    background-color: #f5f5f5;
}

.bpp-featured-badge {
    display: inline-flex;
    align-items: center;
    background-color: #ffc107;
    color: #212529;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.bpp-approved-badge {
    display: inline-flex;
    align-items: center;
    background-color: #28a745;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.bpp-featured-badge .dashicons,
.bpp-approved-badge .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
    margin-right: 4px;
}

/* Action dropdown styles */
.bpp-action-dropdown {
    position: relative;
}

.bpp-action-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
}

.bpp-action-toggle .dashicons {
    margin: 0 2px;
}

.bpp-dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background-color: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    border-radius: 4px;
    min-width: 180px;
    z-index: 100;
}

.bpp-dropdown-menu.active {
    display: block;
}

.bpp-dropdown-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    color: #333;
    text-decoration: none;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    font-size: 13px;
}

.bpp-dropdown-item:hover {
    background-color: #f5f5f5;
}

.bpp-dropdown-item .dashicons {
    margin-right: 8px;
    color: #666;
}

/* Details row styles */
.bpp-details-row td {
    padding: 0;
}

.bpp-professional-details {
    padding: 15px;
    background-color: #f9f9f9;
    border-top: 1px solid #eee;
}

.bpp-details-section {
    margin-bottom: 15px;
}

.bpp-details-section:last-child {
    margin-bottom: 0;
}

.bpp-details-section h4 {
    margin-top: 0;
    margin-bottom: 8px;
    font-size: 14px;
    color: #23282d;
}

.bpp-skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 8px;
}

.bpp-skill-tag {
    background-color: #e9e9e9;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    display: inline-block;
}

/* Loading state */
.bpp-loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Responsive adjustments */
@media screen and (max-width: 782px) {
    .bpp-professionals-table th,
    .bpp-professionals-table td {
        padding: 8px;
    }
    
    .bpp-action-toggle span:not(.dashicons) {
        display: none;
    }
}

@media screen and (max-width: 600px) {
    .bpp-professionals-table {
        display: block;
    }
    
    .bpp-professionals-table thead {
        display: none;
    }
    
    .bpp-professionals-table tbody,
    .bpp-professionals-table tr {
        display: block;
    }
    
    .bpp-professionals-table tr.bpp-professional-row {
        margin-bottom: 15px;
        border: 1px solid #ddd;
    }
    
    .bpp-professionals-table td {
        display: block;
        text-align: right;
        padding-left: 40%;
        position: relative;
        border-bottom: 1px solid #eee;
    }
    
    .bpp-professionals-table td:before {
        content: attr(data-label);
        position: absolute;
        left: 12px;
        width: 35%;
        padding-right: 10px;
        text-align: left;
        font-weight: bold;
    }
    
    .bpp-professionals-table td:last-child {
        border-bottom: 0;
    }
    
    .bpp-actions-column {
        text-align: center !important;
        padding-left: 12px !important;
    }
    
    .bpp-actions-column:before {
        display: none;
    }
    
    .bpp-dropdown-menu {
        left: 0;
        right: auto;
    }
}
</style> 