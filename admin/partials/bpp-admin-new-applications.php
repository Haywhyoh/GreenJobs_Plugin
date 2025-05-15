<?php
/**
 * Provide a admin area view for managing new applications
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
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-welcome-write-blog"></span>
        <?php echo esc_html__('New Applications', 'black-potential-pipeline'); ?>
    </h1>
    
    <div class="bpp-admin-header">
        <p class="bpp-description">
            <?php echo esc_html__('Review and manage new applications submitted to the Black Potential Pipeline.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <?php if ($applications_query && $applications_query->have_posts()) : ?>
        <div class="bpp-application-list">
            <?php while ($applications_query->have_posts()) : $applications_query->the_post(); 
                $post_id = get_the_ID();
                $submission_date = get_post_meta($post_id, 'bpp_submission_date', true);
                $formatted_date = !empty($submission_date) ? date_i18n(get_option('date_format'), strtotime($submission_date)) : '';
                
                $email = get_post_meta($post_id, 'bpp_email', true) ?: '';
                $phone = get_post_meta($post_id, 'bpp_phone', true) ?: '';
                $job_title = get_post_meta($post_id, 'bpp_job_title', true) ?: '';
                $years_experience = get_post_meta($post_id, 'bpp_years_experience', true) ?: '';
                $location = get_post_meta($post_id, 'bpp_location', true) ?: '';
                $linkedin = get_post_meta($post_id, 'bpp_linkedin', true) ?: '';
                
                // Completely safe approach to get industry
                $industry = '';
                if (taxonomy_exists('bpp_industry')) {
                    $terms = get_the_terms($post_id, 'bpp_industry');
                    if ($terms && !is_wp_error($terms) && !empty($terms) && isset($terms[0])) {
                        $industry = $terms[0]->name;
                    }
                }
                
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
                <div class="bpp-application-card" data-id="<?php echo esc_attr((string)$post_id); ?>">
                    <div class="bpp-application-header">
                        <h2 class="bpp-application-title"><?php the_title(); ?></h2>
                        <?php if (!empty($formatted_date)) : ?>
                            <span class="bpp-application-date">
                                <?php echo esc_html__('Submitted:', 'black-potential-pipeline') . ' ' . esc_html($formatted_date); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bpp-application-body">
                        <div class="bpp-application-meta">
                            <?php if (!empty($email)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Email:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value"><?php echo esc_html($email); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($phone)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Phone:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value"><?php echo esc_html($phone); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($job_title)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Job Title:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value"><?php echo esc_html($job_title); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($industry)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Industry:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value"><?php echo esc_html($industry); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($years_experience_display)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Experience:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value">
                                        <?php echo $years_experience_display; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($location)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Location:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value"><?php echo esc_html($location); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($linkedin)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('LinkedIn:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value">
                                        <a href="<?php echo esc_url($linkedin); ?>" target="_blank"><?php echo esc_html__('View Profile', 'black-potential-pipeline'); ?></a>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($resume_url)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Resume:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value">
                                        <?php if ($resume_url) : ?>
                                            <a href="<?php echo esc_url((string)$resume_url); ?>" target="_blank"><?php echo esc_html((string)$resume_filename); ?></a>
                                        <?php else : ?>
                                            <?php echo esc_html((string)($resume_filename ?: __('No resume', 'black-potential-pipeline'))); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bpp-application-content">
                            <h3><?php echo esc_html__('Cover Letter / Personal Statement', 'black-potential-pipeline'); ?></h3>
                            <div class="bpp-application-excerpt">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bpp-application-footer">
                        <div class="bpp-application-actions">
                            <button type="button" class="button button-primary bpp-approve-button" data-id="<?php echo esc_attr((string)$post_id); ?>">
                                <span class="dashicons dashicons-yes"></span>
                                <?php echo esc_html__('Approve', 'black-potential-pipeline'); ?>
                            </button>
                            
                            <button type="button" class="button bpp-reject-button" data-id="<?php echo esc_attr((string)$post_id); ?>">
                                <span class="dashicons dashicons-no"></span>
                                <?php echo esc_html__('Reject', 'black-potential-pipeline'); ?>
                            </button>
                            
                            <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="button">
                                <span class="dashicons dashicons-edit"></span>
                                <?php echo esc_html__('Edit', 'black-potential-pipeline'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
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
    // Approve button click handler
    $('.bpp-approve-button').on('click', function() {
        if (confirm(bpp_admin_obj.i18n.approve_confirm)) {
            var applicantId = $(this).data('id');
            var $card = $(this).closest('.bpp-application-card');
            
            $.ajax({
                url: bpp_admin_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'bpp_approve_applicant',
                    applicant_id: applicantId,
                    nonce: bpp_admin_obj.nonce
                },
                beforeSend: function() {
                    $card.addClass('bpp-loading');
                },
                success: function(response) {
                    if (response.success) {
                        $card.fadeOut(400, function() {
                            $card.remove();
                            
                            // Show message if no more applications
                            if ($('.bpp-application-card').length === 0) {
                                $('.bpp-application-list').html('<div class="bpp-no-applications"><p>' + 
                                    bpp_admin_obj.i18n.no_applications + '</p></div>');
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
                    $card.removeClass('bpp-loading');
                }
            });
        }
    });
    
    // Reject button click handler
    $('.bpp-reject-button').on('click', function() {
        var applicantId = $(this).data('id');
        $('#bpp-rejection-applicant-id').val(applicantId);
        $('#bpp-rejection-reason').val('');
        $('#bpp-rejection-modal').show();
    });
    
    // Modal close button
    $('.bpp-modal-close, #bpp-cancel-reject').on('click', function() {
        $('#bpp-rejection-modal').hide();
    });
    
    // Confirm rejection
    $('#bpp-confirm-reject').on('click', function() {
        var applicantId = $('#bpp-rejection-applicant-id').val();
        var reason = $('#bpp-rejection-reason').val();
        var $card = $('.bpp-application-card[data-id="' + applicantId + '"]');
        
        $.ajax({
            url: bpp_admin_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'bpp_reject_applicant',
                applicant_id: applicantId,
                reason: reason,
                nonce: bpp_admin_obj.nonce
            },
            beforeSend: function() {
                $card.addClass('bpp-loading');
            },
            success: function(response) {
                if (response.success) {
                    $('#bpp-rejection-modal').hide();
                    $card.fadeOut(400, function() {
                        $card.remove();
                        
                        // Show message if no more applications
                        if ($('.bpp-application-card').length === 0) {
                            $('.bpp-application-list').html('<div class="bpp-no-applications"><p>' + 
                                bpp_admin_obj.i18n.no_applications + '</p></div>');
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
                $card.removeClass('bpp-loading');
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