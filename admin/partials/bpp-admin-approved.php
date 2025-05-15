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
        <div class="bpp-professionals-list">
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
            ?>
                <div class="bpp-professional-card" data-id="<?php echo esc_attr($post_id); ?>">
                    <div class="bpp-professional-header">
                        <div class="bpp-professional-title-area">
                            <h2 class="bpp-professional-title"><?php the_title(); ?></h2>
                            <?php if (!empty($formatted_date)) : ?>
                                <span class="bpp-professional-date">
                                    <?php echo esc_html__('Approved:', 'black-potential-pipeline') . ' ' . esc_html($formatted_date); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="bpp-professional-thumbnail">
                                <?php the_post_thumbnail('thumbnail'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bpp-professional-body">
                        <div class="bpp-professional-meta">
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
                            
                            <?php if (!empty($profile_views)) : ?>
                                <div class="bpp-meta-item">
                                    <span class="bpp-meta-label"><?php echo esc_html__('Profile Views:', 'black-potential-pipeline'); ?></span>
                                    <span class="bpp-meta-value"><?php echo esc_html($profile_views); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($skills)) : ?>
                            <div class="bpp-professional-skills">
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
                        
                        <div class="bpp-professional-content">
                            <h3><?php echo esc_html__('Bio / Personal Statement', 'black-potential-pipeline'); ?></h3>
                            <div class="bpp-professional-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bpp-professional-footer">
                        <div class="bpp-professional-actions">
                            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="button button-primary" target="_blank">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php echo esc_html__('View Profile', 'black-potential-pipeline'); ?>
                            </a>
                            
                            <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="button">
                                <span class="dashicons dashicons-edit"></span>
                                <?php echo esc_html__('Edit', 'black-potential-pipeline'); ?>
                            </a>
                            
                            <button type="button" class="button bpp-remove-button" data-id="<?php echo esc_attr($post_id); ?>">
                                <span class="dashicons dashicons-no"></span>
                                <?php echo esc_html__('Remove', 'black-potential-pipeline'); ?>
                            </button>
                            
                            <button type="button" class="button <?php echo (!empty($featured)) ? 'button-primary bpp-unfeature-button' : 'bpp-feature-button'; ?>" data-id="<?php echo esc_attr($post_id); ?>">
                                <span class="dashicons <?php echo (!empty($featured)) ? 'dashicons-star-filled' : 'dashicons-star-empty'; ?>"></span>
                                <?php echo (!empty($featured)) ? esc_html__('Unfeature', 'black-potential-pipeline') : esc_html__('Feature', 'black-potential-pipeline'); ?>
                            </button>
                            
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
                    $button.removeClass('bpp-feature-button').addClass('button-primary bpp-unfeature-button');
                    $button.find('.dashicons').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
                    $button.html('<span class="dashicons dashicons-star-filled"></span> ' + bpp_admin_obj.i18n.unfeature_text);
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
                    $button.removeClass('button-primary bpp-unfeature-button').addClass('bpp-feature-button');
                    $button.find('.dashicons').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
                    $button.html('<span class="dashicons dashicons-star-empty"></span> ' + bpp_admin_obj.i18n.feature_text);
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
        var $card = $('.bpp-professional-card[data-id="' + professionalId + '"]');
        
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
                $card.addClass('bpp-loading');
            },
            success: function(response) {
                if (response.success) {
                    $('#bpp-remove-modal').hide();
                    $card.fadeOut(400, function() {
                        $card.remove();
                        
                        // Show message if no more professionals
                        if ($('.bpp-professional-card').length === 0) {
                            $('.bpp-professionals-list').html('<div class="bpp-no-professionals"><p>' + 
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
                $card.removeClass('bpp-loading');
            }
        });
    });
    
    // Close modal if clicked outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#bpp-remove-modal')) {
            $('#bpp-remove-modal').hide();
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

/* Professional Cards */
.bpp-professionals-list {
    margin-top: 20px;
}

.bpp-professional-card {
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.bpp-professional-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bpp-professional-title-area {
    flex: 1;
}

.bpp-professional-title {
    margin: 0;
    font-size: 18px;
    color: #23282d;
}

.bpp-professional-date {
    color: #888;
    font-size: 12px;
}

.bpp-professional-thumbnail {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    margin-left: 15px;
}

.bpp-professional-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bpp-professional-body {
    padding: 15px;
}

.bpp-professional-meta {
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

.bpp-professional-skills {
    margin-bottom: 15px;
}

.bpp-professional-skills h3 {
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

.bpp-professional-content h3 {
    margin-top: 0;
    font-size: 14px;
}

.bpp-professional-excerpt {
    font-size: 14px;
    color: #555;
    line-height: 1.5;
}

.bpp-professional-footer {
    padding: 15px;
    background-color: #f9f9f9;
    border-top: 1px solid #eee;
}

.bpp-professional-actions {
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
    .bpp-professional-meta {
        grid-template-columns: 1fr;
    }
    
    .bpp-professional-actions {
        flex-direction: column;
    }
    
    .bpp-professional-actions a,
    .bpp-professional-actions button {
        width: 100%;
        margin-bottom: 5px;
        text-align: center;
    }
    
    .bpp-modal-content {
        width: 90%;
    }
}
</style> 