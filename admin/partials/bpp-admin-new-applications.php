<?php
/**
 * Provide a admin area view for managing new applications
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

/**
 * Helper function to ensure values are strings before passing to WordPress formatting functions
 * This prevents PHP 8.1 deprecation warnings about passing null to string functions
 *
 * @param mixed $value The value to ensure is a string
 * @return string The value as a string, or empty string if null
 */
function bpp_ensure_string($value) {
    return $value === null ? '' : (string)$value;
}

// Include WordPress core files for linter recognition
require_once(ABSPATH . 'wp-includes/class-wp-query.php');
require_once(ABSPATH . 'wp-includes/post.php');
require_once(ABSPATH . 'wp-includes/formatting.php');
require_once(ABSPATH . 'wp-includes/link-template.php');
require_once(ABSPATH . 'wp-includes/post-template.php');
require_once(ABSPATH . 'wp-includes/media.php');
require_once(ABSPATH . 'wp-includes/l10n.php');

// Get new applications (draft status)
$args = array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'draft',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
);

// Safely create WP_Query
try {
    $applications_query = new WP_Query($args);
} catch (Exception $e) {
    // Handle exception
    $applications_query = null;
    echo '<div class="error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
}
?>

<div class="wrap bpp-admin-new-applications">
    <h1 class="wp-heading-inline"><?php echo esc_html__('New Applications', 'black-potential-pipeline'); ?></h1>
    
    <style>
        /* Responsive Table Styles */
        .bpp-table-container {
            margin-top: 20px;
            overflow-x: auto;
        }
        
        .bpp-applications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .bpp-applications-table th {
            background-color: #f1f1f1;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }
        
        .bpp-applications-table td {
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
        
        .bpp-application-details {
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
            .bpp-applications-table {
                display: block;
            }
            
            .bpp-applications-table thead {
                display: none;
            }
            
            .bpp-applications-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #e5e5e5;
            }
            
            .bpp-applications-table td {
                display: block;
                padding: 10px;
                text-align: right;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .bpp-applications-table td:last-child {
                border-bottom: none;
            }
            
            .bpp-applications-table td:before {
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
            <?php echo esc_html__('Review and manage new applications submitted to the Black Potential Pipeline.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <?php if ($applications_query && $applications_query->have_posts()) : ?>
        <div class="bpp-table-container">
            <table class="bpp-applications-table widefat striped responsive">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Name', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Job Title', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Industry', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Experience', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Location', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Date', 'black-potential-pipeline'); ?></th>
                        <th><?php echo esc_html__('Actions', 'black-potential-pipeline'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($applications_query->have_posts()) : $applications_query->the_post(); 
                        $post_id = get_the_ID();
                        $submission_date = get_post_meta($post_id, 'bpp_submission_date', true);
                        $formatted_date = !empty($submission_date) ? date_i18n(get_option('date_format'), strtotime($submission_date)) : '';
                        
                        $email = get_post_meta($post_id, 'bpp_email', true);
                        $phone = get_post_meta($post_id, 'bpp_phone', true);
                        $job_title = get_post_meta($post_id, 'bpp_job_title', true);
                        $years_experience = get_post_meta($post_id, 'bpp_years_experience', true);
                        $location = get_post_meta($post_id, 'bpp_location', true);
                        $linkedin = get_post_meta($post_id, 'bpp_linkedin', true);
                        
                        // Get industry
                        $industry_terms = wp_get_post_terms($post_id, 'bpp_industry', array('fields' => 'names'));
                        $industry = '';
                        if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
                            $industry = $industry_terms[0];
                        }
                        
                        // Get skills
                        $skills = get_post_meta($post_id, 'bpp_skills', true);
                        
                        // Safely handle resume data
                        $resume_id = get_post_meta($post_id, 'bpp_resume', true);
                        $resume_url = '';
                        $resume_filename = '';
                        
                        if (!empty($resume_id)) {
                            if (is_wp_error($resume_id)) {
                                $resume_url = '';
                                $resume_filename = 'Error: ' . $resume_id->get_error_message();
                            } else {
                                $resume_url = wp_get_attachment_url($resume_id);
                                $resume_filename = basename(get_attached_file($resume_id));
                            }
                        }
                        
                        // For the resume filename
                        if (!empty($resume_url)) {
                            $resume_filename = (string)($resume_filename ?: '');
                        }
                        
                        // Make sure years_experience is properly handled for _n() function
                        if (!empty($years_experience) && is_numeric($years_experience)) {
                            $years_experience_display = sprintf(
                                esc_html(_n('%d year', '%d years', intval($years_experience), 'black-potential-pipeline')), 
                                intval($years_experience)
                            );
                        } else {
                            $years_experience_display = '';
                        }
                    ?>
                        <tr class="bpp-application-row" data-id="<?php echo esc_attr((string)$post_id); ?>">
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
                            <td class="bpp-experience-column" data-label="<?php echo esc_attr__('Experience', 'black-potential-pipeline'); ?>"><?php echo $years_experience_display; ?></td>
                            <td class="bpp-location-column" data-label="<?php echo esc_attr__('Location', 'black-potential-pipeline'); ?>"><?php echo esc_html($location); ?></td>
                            <td class="bpp-date-column" data-label="<?php echo esc_attr__('Date', 'black-potential-pipeline'); ?>"><?php echo esc_html($formatted_date); ?></td>
                            <td class="bpp-actions-column" data-label="<?php echo esc_attr__('Actions', 'black-potential-pipeline'); ?>">
                                <div class="bpp-action-dropdown">
                                    <button class="button bpp-action-toggle">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                        <?php echo esc_html__('Options', 'black-potential-pipeline'); ?>
                                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                                    </button>
                                    <div class="bpp-dropdown-menu">
                                        <button type="button" class="bpp-dropdown-item bpp-approve-button" data-id="<?php echo esc_attr((string)$post_id); ?>">
                                            <span class="dashicons dashicons-yes"></span>
                                            <?php echo esc_html__('Approve', 'black-potential-pipeline'); ?>
                                        </button>
                                        <button type="button" class="bpp-dropdown-item bpp-reject-button" data-id="<?php echo esc_attr((string)$post_id); ?>">
                                            <span class="dashicons dashicons-no"></span>
                                            <?php echo esc_html__('Reject', 'black-potential-pipeline'); ?>
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
                                            <a href="<?php echo esc_url((string)$resume_url); ?>" class="bpp-dropdown-item" target="_blank">
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
                                <div class="bpp-application-details">
                                    <div class="bpp-details-section">
                                        <h4><?php echo esc_html__('Contact Information', 'black-potential-pipeline'); ?></h4>
                                        <p><strong><?php echo esc_html__('Email:', 'black-potential-pipeline'); ?></strong> <?php echo esc_html($email); ?></p>
                                        <?php if (!empty($phone)) : ?>
                                            <p><strong><?php echo esc_html__('Phone:', 'black-potential-pipeline'); ?></strong> <?php echo esc_html($phone); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($linkedin)) : ?>
                                            <p><strong><?php echo esc_html__('LinkedIn:', 'black-potential-pipeline'); ?></strong> 
                                                <a href="<?php echo esc_url($linkedin); ?>" target="_blank"><?php echo esc_html__('View Profile', 'black-potential-pipeline'); ?></a>
                                            </p>
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
                                        <h4><?php echo esc_html__('Cover Letter / Personal Statement', 'black-potential-pipeline'); ?></h4>
                                        <div class="bpp-application-excerpt">
                                            <?php the_content(); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($resume_url)) : ?>
                                        <div class="bpp-details-section">
                                            <h4><?php echo esc_html__('Resume', 'black-potential-pipeline'); ?></h4>
                                            <p>
                                                <a href="<?php echo esc_url((string)$resume_url); ?>" target="_blank" class="button">
                                                    <span class="dashicons dashicons-media-document"></span>
                                                    <?php echo esc_html__('Download Resume', 'black-potential-pipeline'); ?>
                                                </a>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="bpp-details-actions" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; text-align: right;">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-applicant-profile&id=' . $post_id)); ?>" class="button button-primary" style="display: inline-flex; align-items: center; gap: 5px;">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <?php echo esc_html__('View Full Profile', 'black-potential-pipeline'); ?>
                                        </a>
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
        <div class="bpp-no-applications">
            <p><?php echo esc_html__('No new applications at this time.', 'black-potential-pipeline'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Rejection Modal -->
    <div id="bpp-rejection-modal" class="bpp-modal" style="display: none;">
        <div class="bpp-modal-content">
            <span class="bpp-modal-close">&times;</span>
            <h2><?php echo esc_html__('Reject Application', 'black-potential-pipeline'); ?></h2>
            <p><?php echo esc_html__('Please provide a reason for rejection (optional):', 'black-potential-pipeline'); ?></p>
            <textarea id="bpp-rejection-reason" rows="4"></textarea>
            <input type="hidden" id="bpp-rejection-applicant-id" value="">
            <div class="bpp-modal-actions">
                <button type="button" class="button button-primary" id="bpp-confirm-reject">
                    <?php echo esc_html__('Confirm Rejection', 'black-potential-pipeline'); ?>
                </button>
                <button type="button" class="button" id="bpp-cancel-reject">
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
    $('.bpp-application-row').on('click', function(e) {
        if(!$(e.target).closest('.bpp-action-dropdown').length) {
            const postId = $(this).data('id');
            $(this).next('.bpp-details-row').toggle();
        }
    });
    
    // Approve button click handler
    $('.bpp-approve-button').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (confirm(bpp_admin_obj.i18n.approve_confirm)) {
            const applicantId = $(this).data('id');
            const $row = $(this).closest('tr');
            
            $.ajax({
                url: bpp_admin_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'bpp_approve_applicant',
                    applicant_id: applicantId,
                    nonce: bpp_admin_obj.nonce
                },
                beforeSend: function() {
                    $row.css('opacity', '0.5');
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(400, function() {
                            $row.next('.bpp-details-row').remove();
                            $row.remove();
                            
                            // Show message if no more applications
                            if ($('.bpp-application-row').length === 0) {
                                $('.bpp-table-container').html('<div class="bpp-no-applications"><p>' + 
                                    bpp_admin_obj.i18n.no_applications + '</p></div>');
                            }
                        });
                    } else {
                        alert(response.data || bpp_admin_obj.i18n.generic_error);
                        $row.css('opacity', '1');
                    }
                },
                error: function() {
                    alert(bpp_admin_obj.i18n.generic_error);
                    $row.css('opacity', '1');
                }
            });
        }
    });
    
    // Reject button click handler
    $('.bpp-reject-button').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const applicantId = $(this).data('id');
        $('#bpp-rejection-applicant-id').val(applicantId);
        $('#bpp-rejection-modal').show();
    });
    
    // Modal close button
    $('.bpp-modal-close, #bpp-cancel-reject').on('click', function() {
        $('#bpp-rejection-modal').hide();
    });
    
    // Confirm rejection
    $('#bpp-confirm-reject').on('click', function() {
        const applicantId = $('#bpp-rejection-applicant-id').val();
        const rejectionReason = $('#bpp-rejection-reason').val();
        const $row = $('.bpp-application-row[data-id="' + applicantId + '"]');
        
        $.ajax({
            url: bpp_admin_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'bpp_reject_applicant',
                applicant_id: applicantId,
                rejection_reason: rejectionReason,
                nonce: bpp_admin_obj.nonce
            },
            beforeSend: function() {
                $row.css('opacity', '0.5');
            },
            success: function(response) {
                if (response.success) {
                    $('#bpp-rejection-modal').hide();
                    $row.fadeOut(400, function() {
                        $row.next('.bpp-details-row').remove();
                        $row.remove();
                        
                        // Show message if no more applications
                        if ($('.bpp-application-row').length === 0) {
                            $('.bpp-table-container').html('<div class="bpp-no-applications"><p>' + 
                                bpp_admin_obj.i18n.no_applications + '</p></div>');
                        }
                    });
                } else {
                    alert(response.data || bpp_admin_obj.i18n.generic_error);
                    $row.css('opacity', '1');
                }
            },
            error: function() {
                alert(bpp_admin_obj.i18n.generic_error);
                $row.css('opacity', '1');
            },
            complete: function() {
                $('#bpp-rejection-reason').val('');
            }
        });
    });
    
    // Close modal if clicked outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#bpp-rejection-modal')) {
            $('#bpp-rejection-modal').hide();
        }
    });
});
</script>

<style>
/* Modal Styles */
.bpp-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.bpp-modal-content {
    position: relative;
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
    border-radius: 4px;
}

.bpp-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.bpp-modal-close:hover,
.bpp-modal-close:focus {
    color: black;
    text-decoration: none;
}

.bpp-modal h2 {
    margin-top: 0;
}

.bpp-modal textarea {
    width: 100%;
    margin: 10px 0;
}

.bpp-modal-actions {
    text-align: right;
    margin-top: 15px;
}

.bpp-modal-actions button {
    margin-left: 10px;
}

/* Loading state */
.bpp-loading {
    opacity: 0.6;
    pointer-events: none;
}
</style> 