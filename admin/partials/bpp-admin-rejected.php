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
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-dismiss"></span>
        <?php echo esc_html__('Rejected Applications', 'black-potential-pipeline'); ?>
    </h1>
    
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
        <div class="bpp-rejected-list">
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
            ?>
                <div class="bpp-rejected-card" data-id="<?php echo esc_attr($post_id); ?>">
                    <div class="bpp-rejected-header">
                        <div class="bpp-rejected-title-area">
                            <h2 class="bpp-rejected-title"><?php the_title(); ?></h2>
                            <?php if (!empty($formatted_date)) : ?>
                                <span class="bpp-rejected-date">
                                    <?php echo esc_html__('Rejected:', 'black-potential-pipeline') . ' ' . esc_html($formatted_date); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="bpp-rejected-thumbnail">
                                <?php the_post_thumbnail('thumbnail'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bpp-rejected-body">
                        <div class="bpp-rejected-meta">
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
                            
                            <?php if (!empty($years_experience)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Experience:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value">
                                        <?php echo sprintf(esc_html(_n('%d year', '%d years', $years_experience, 'black-potential-pipeline')), $years_experience); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($location)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Location:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value"><?php echo esc_html($location); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($skills)) : ?>
                            <div class="bpp-rejected-skills">
                                <h3><?php echo esc_html__('Skills', 'black-potential-pipeline'); ?></h3>
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
                        
                        <?php if (!empty($rejection_reason)) : ?>
                            <div class="bpp-rejection-reason">
                                <h3><?php echo esc_html__('Rejection Reason', 'black-potential-pipeline'); ?></h3>
                                <div class="bpp-reason-content">
                                    <?php echo esc_html($rejection_reason); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="bpp-rejected-content">
                            <h3><?php echo esc_html__('Cover Letter / Personal Statement', 'black-potential-pipeline'); ?></h3>
                            <div class="bpp-rejected-excerpt">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bpp-rejected-footer">
                        <div class="bpp-rejected-actions">
                            <button type="button" class="button button-primary bpp-reconsider-button" data-id="<?php echo esc_attr($post_id); ?>">
                                <span class="dashicons dashicons-yes"></span>
                                <?php echo esc_html__('Reconsider & Approve', 'black-potential-pipeline'); ?>
                            </button>
                            
                            <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="button">
                                <span class="dashicons dashicons-edit"></span>
                                <?php echo esc_html__('Edit', 'black-potential-pipeline'); ?>
                            </a>
                            
                            <?php if (!empty($resume_url)) : ?>
                                <a href="<?php echo esc_url($resume_url); ?>" class="button" target="_blank">
                                    <span class="dashicons dashicons-media-document"></span>
                                    <?php echo esc_html__('View Resume', 'black-potential-pipeline'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
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
    // Reconsider button click handler
    $('.bpp-reconsider-button').on('click', function() {
        var applicantId = $(this).data('id');
        $('#bpp-reconsider-applicant-id').val(applicantId);
        $('#bpp-reconsider-modal').show();
    });
    
    // Modal close button
    $('.bpp-modal-close, #bpp-cancel-reconsider').on('click', function() {
        $('#bpp-reconsider-modal').hide();
    });
    
    // Confirm reconsideration
    $('#bpp-confirm-reconsider').on('click', function() {
        var applicantId = $('#bpp-reconsider-applicant-id').val();
        var $card = $('.bpp-rejected-card[data-id="' + applicantId + '"]');
        
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
                    $('#bpp-reconsider-modal').hide();
                    $card.fadeOut(400, function() {
                        $card.remove();
                        
                        // Show message if no more rejected applications
                        if ($('.bpp-rejected-card').length === 0) {
                            $('.bpp-rejected-list').html('<div class="bpp-no-rejected"><p>' + 
                                'No rejected applications found.' + '</p></div>');
                        }
                    });
                } else {
                    alert(response.data || 'An error occurred. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                $card.removeClass('bpp-loading');
            }
        });
    });
    
    // Close modal if clicked outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#bpp-reconsider-modal')) {
            $('#bpp-reconsider-modal').hide();
        }
    });
});
</script>

<style>
/* Filter Bar */
.bpp-filter-bar {
    margin: 20px 0;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 4px;
}

.bpp-filter-bar form {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Rejected Application Cards */
.bpp-rejected-list {
    margin-top: 20px;
}

.bpp-rejected-card {
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
    border-left: 4px solid #dc3232;
}

.bpp-rejected-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bpp-rejected-title-area {
    flex: 1;
}

.bpp-rejected-title {
    margin: 0;
    font-size: 18px;
    color: #23282d;
}

.bpp-rejected-date {
    color: #888;
    font-size: 12px;
}

.bpp-rejected-thumbnail {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    margin-left: 15px;
}

.bpp-rejected-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bpp-rejected-body {
    padding: 15px;
}

.bpp-rejected-meta {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 15px;
}

.bpp-meta-item {
    display: flex;
    flex-direction: column;
}

.bpp-meta-label {
    font-weight: bold;
    font-size: 12px;
    color: #555;
}

.bpp-meta-value {
    font-size: 14px;
}

.bpp-rejected-skills {
    margin-bottom: 15px;
}

.bpp-rejected-skills h3 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 14px;
}

.bpp-skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.bpp-skill-tag {
    background-color: #f0f0f0;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    display: inline-block;
}

.bpp-rejection-reason {
    margin-bottom: 15px;
    padding: 10px;
    background-color: #fef7f7;
    border-left: 3px solid #dc3232;
}

.bpp-rejection-reason h3 {
    margin-top: 0;
    font-size: 14px;
    color: #dc3232;
}

.bpp-reason-content {
    font-style: italic;
    color: #555;
}

.bpp-rejected-content h3 {
    margin-top: 0;
    font-size: 14px;
}

.bpp-rejected-excerpt {
    font-size: 14px;
    color: #555;
    line-height: 1.5;
}

.bpp-rejected-footer {
    padding: 15px;
    background-color: #f9f9f9;
    border-top: 1px solid #eee;
}

.bpp-rejected-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

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

/* Responsive */
@media screen and (max-width: 782px) {
    .bpp-rejected-meta {
        grid-template-columns: 1fr;
    }
    
    .bpp-rejected-actions {
        flex-direction: column;
    }
    
    .bpp-rejected-actions a,
    .bpp-rejected-actions button {
        width: 100%;
        margin-bottom: 5px;
        text-align: center;
    }
    
    .bpp-modal-content {
        width: 90%;
    }
}
</style> 