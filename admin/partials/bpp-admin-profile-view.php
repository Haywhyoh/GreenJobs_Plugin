<?php
/**
 * Admin view for viewing and editing a single applicant profile
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

// Get applicant ID from URL or return to dashboard if not found
$applicant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$applicant_id) {
    wp_redirect(admin_url('admin.php?page=bpp-approved'));
    exit;
}

// Get post data
$applicant = get_post($applicant_id);
if (!$applicant || $applicant->post_type !== 'bpp_applicant') {
    wp_die(__('Invalid applicant ID.', 'black-potential-pipeline'));
}

// Get applicant metadata
$job_title = get_post_meta($applicant_id, 'bpp_job_title', true);
$location = get_post_meta($applicant_id, 'bpp_location', true);
$years_experience = get_post_meta($applicant_id, 'bpp_years_experience', true);
$skills = get_post_meta($applicant_id, 'bpp_skills', true);
$skills_array = !empty($skills) ? explode(',', $skills) : array();
$bio = get_post_meta($applicant_id, 'bpp_bio', true) ?: $applicant->post_content;
$website = get_post_meta($applicant_id, 'bpp_website', true);
$linkedin = get_post_meta($applicant_id, 'bpp_linkedin', true);
$email = get_post_meta($applicant_id, 'bpp_email', true);
$phone = get_post_meta($applicant_id, 'bpp_phone', true);
$resume_id = get_post_meta($applicant_id, 'bpp_resume', true);
$resume_url = !empty($resume_id) ? wp_get_attachment_url($resume_id) : '';
$resume_filename = !empty($resume_id) ? basename(get_attached_file($resume_id)) : '';
$featured = (bool) get_post_meta($applicant_id, 'bpp_featured', true);
$submission_date = get_post_meta($applicant_id, 'bpp_submission_date', true);
$approval_date = get_post_meta($applicant_id, 'bpp_approval_date', true);
$formatted_submission_date = !empty($submission_date) ? date_i18n(get_option('date_format'), strtotime($submission_date)) : '';
$formatted_approval_date = !empty($approval_date) ? date_i18n(get_option('date_format'), strtotime($approval_date)) : '';

// Get applicant status
$status = $applicant->post_status;
$status_text = '';
$status_class = '';

switch ($status) {
    case 'publish':
        $status_text = __('Approved', 'black-potential-pipeline');
        $status_class = 'approved';
        break;
    case 'draft':
        $status_text = __('New Application', 'black-potential-pipeline');
        $status_class = 'new';
        break;
    case 'private':
    case 'trash':
        $status_text = __('Rejected', 'black-potential-pipeline');
        $status_class = 'rejected';
        break;
    default:
        $status_text = ucfirst($status);
        $status_class = $status;
}

// Get industry from taxonomy
$industry_terms = wp_get_post_terms($applicant_id, 'bpp_industry', array('fields' => 'names'));
$industry = '';
if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
    $industry = $industry_terms[0];
}

// Get all available industries for the dropdown
$all_industries = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));

// Success message if profile was updated
$updated = isset($_GET['updated']) && $_GET['updated'] == 'true';
?>

<div class="wrap bpp-admin-profile-view">
    <h1 class="wp-heading-inline">
        <?php echo esc_html__('Applicant Profile', 'black-potential-pipeline'); ?>
    </h1>
    
    <div class="bpp-admin-header">
        <p class="bpp-description">
            <?php echo esc_html__('View and edit applicant information.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <?php if ($updated) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Profile updated successfully.', 'black-potential-pipeline'); ?></p>
        </div>
    <?php endif; ?>
    
    <?php
    // Check if there are any fields hidden from public view
    $directory_settings = get_option('bpp_directory_settings', array());
    $visibility_settings = isset($directory_settings['profile_visibility']) ? $directory_settings['profile_visibility'] : array();
    $default_visibility = array(
        'photo' => true,
        'job_title' => true,
        'industry' => true,
        'location' => true,
        'years_experience' => true,
        'skills' => true,
        'bio' => true,
        'website' => true,
        'linkedin' => true,
        'email' => true,
        'phone' => false,
        'resume' => true
    );
    // Merge with defaults
    $visibility = array_merge($default_visibility, $visibility_settings);
    
    // Get names of hidden fields
    $hidden_fields = array();
    $field_labels = array(
        'photo' => __('Profile Photo', 'black-potential-pipeline'),
        'job_title' => __('Job Title', 'black-potential-pipeline'),
        'industry' => __('Industry', 'black-potential-pipeline'),
        'location' => __('Location', 'black-potential-pipeline'),
        'years_experience' => __('Years of Experience', 'black-potential-pipeline'),
        'skills' => __('Skills', 'black-potential-pipeline'),
        'bio' => __('Professional Bio', 'black-potential-pipeline'),
        'website' => __('Website', 'black-potential-pipeline'),
        'linkedin' => __('LinkedIn Profile', 'black-potential-pipeline'),
        'email' => __('Email Address', 'black-potential-pipeline'),
        'phone' => __('Phone Number', 'black-potential-pipeline'),
        'resume' => __('Resume', 'black-potential-pipeline')
    );
    
    foreach ($visibility as $field => $is_visible) {
        if (!$is_visible && isset($field_labels[$field])) {
            $hidden_fields[] = $field_labels[$field];
        }
    }
    ?>
    
    <?php if (!empty($hidden_fields) && $status === 'publish') : ?>
        <div class="notice notice-info">
            <p>
                <span class="dashicons dashicons-privacy" style="color:#72aee6;vertical-align:text-bottom;"></span>
                <?php 
                echo esc_html__('Privacy notice: The following information is hidden from the public profile: ', 'black-potential-pipeline'); 
                echo '<strong>' . esc_html(implode(', ', $hidden_fields)) . '</strong>';
                ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=bpp-settings#directory-settings')); ?>">
                    <?php echo esc_html__('Change settings', 'black-potential-pipeline'); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="bpp-profile-actions">
        <?php
        // Generate links to appropriate listing pages based on status
        $status_page = 'bpp-approved';
        if ($status === 'draft') {
            $status_page = 'bpp-new-applications';
        } elseif ($status === 'private' || $status === 'trash') {
            $status_page = 'bpp-rejected';
        }
        ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=' . $status_page)); ?>" class="button">
            <span class="dashicons dashicons-arrow-left-alt"></span>
            <?php echo esc_html__('Back to List', 'black-potential-pipeline'); ?>
        </a>
        
        <?php if ($status === 'publish') : ?>
            <a href="<?php echo esc_url(get_permalink($applicant_id)); ?>" class="button" target="_blank">
                <span class="dashicons dashicons-visibility"></span>
                <?php echo esc_html__('View Public Profile', 'black-potential-pipeline'); ?>
            </a>
        <?php endif; ?>
        
        <?php if (!empty($resume_url)) : ?>
            <a href="<?php echo esc_url($resume_url); ?>" class="button" target="_blank">
                <span class="dashicons dashicons-media-document"></span>
                <?php echo esc_html__('View Resume', 'black-potential-pipeline'); ?>
            </a>
        <?php endif; ?>
    </div>
    
    <div class="bpp-profile-container">
        <div class="bpp-profile-header-card">
            <div class="bpp-profile-header-info">
                <div class="bpp-profile-photo">
                    <?php if (has_post_thumbnail($applicant_id)) : ?>
                        <?php echo get_the_post_thumbnail($applicant_id, 'thumbnail', array('class' => 'bpp-profile-image')); ?>
                    <?php else : ?>
                        <div class="bpp-no-photo">
                            <span class="dashicons dashicons-admin-users"></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="bpp-profile-header-details">
                    <h2><?php echo esc_html($applicant->post_title); ?></h2>
                    <div class="bpp-profile-meta">
                        <?php if (!empty($job_title)) : ?>
                            <div class="bpp-meta-item">
                                <span class="dashicons dashicons-businessman"></span>
                                <?php echo esc_html($job_title); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($industry)) : ?>
                            <div class="bpp-meta-item">
                                <span class="dashicons dashicons-category"></span>
                                <?php echo esc_html($industry); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($location)) : ?>
                            <div class="bpp-meta-item">
                                <span class="dashicons dashicons-location"></span>
                                <?php echo esc_html($location); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bpp-profile-status">
                        <span class="bpp-status-badge bpp-status-<?php echo esc_attr($status_class); ?>">
                            <?php echo esc_html($status_text); ?>
                        </span>
                        
                        <?php if ($featured) : ?>
                            <span class="bpp-featured-badge">
                                <span class="dashicons dashicons-star-filled"></span>
                                <?php echo esc_html__('Featured', 'black-potential-pipeline'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="bpp-profile-header-dates">
                <?php if (!empty($formatted_submission_date)) : ?>
                    <div class="bpp-date-item">
                        <span class="bpp-date-label"><?php echo esc_html__('Submitted:', 'black-potential-pipeline'); ?></span>
                        <span class="bpp-date-value"><?php echo esc_html($formatted_submission_date); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($formatted_approval_date)) : ?>
                    <div class="bpp-date-item">
                        <span class="bpp-date-label"><?php echo esc_html__('Approved:', 'black-potential-pipeline'); ?></span>
                        <span class="bpp-date-value"><?php echo esc_html($formatted_approval_date); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bpp-edit-profile-form-container">
            <h3><?php echo esc_html__('Edit Profile Information', 'black-potential-pipeline'); ?></h3>
            
            <form id="bpp-edit-profile-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('bpp_update_profile', 'bpp_profile_nonce'); ?>
                <input type="hidden" name="action" value="bpp_update_profile">
                <input type="hidden" name="applicant_id" value="<?php echo esc_attr($applicant_id); ?>">
                
                <div class="bpp-form-section">
                    <h4><?php echo esc_html__('Basic Information', 'black-potential-pipeline'); ?></h4>
                    
                    <div class="bpp-form-row">
                        <div class="bpp-form-field">
                            <label for="bpp_name"><?php echo esc_html__('Full Name', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                            <input type="text" id="bpp_name" name="bpp_name" value="<?php echo esc_attr($applicant->post_title); ?>" required>
                        </div>
                        
                        <div class="bpp-form-field">
                            <label for="bpp_job_title"><?php echo esc_html__('Job Title', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                            <input type="text" id="bpp_job_title" name="bpp_job_title" value="<?php echo esc_attr($job_title); ?>" required>
                        </div>
                    </div>
                    
                    <div class="bpp-form-row">
                        <div class="bpp-form-field">
                            <label for="bpp_industry"><?php echo esc_html__('Industry', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                            <select id="bpp_industry" name="bpp_industry" required>
                                <option value=""><?php echo esc_html__('Select Industry', 'black-potential-pipeline'); ?></option>
                                <?php foreach ($all_industries as $ind) : ?>
                                    <option value="<?php echo esc_attr($ind->slug); ?>" <?php selected($industry, $ind->name); ?>>
                                        <?php echo esc_html($ind->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="bpp-form-field">
                            <label for="bpp_location"><?php echo esc_html__('Location', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                            <input type="text" id="bpp_location" name="bpp_location" value="<?php echo esc_attr($location); ?>" required>
                        </div>
                    </div>
                    
                    <div class="bpp-form-row">
                        <div class="bpp-form-field">
                            <label for="bpp_years_experience"><?php echo esc_html__('Years of Experience', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                            <input type="number" id="bpp_years_experience" name="bpp_years_experience" value="<?php echo esc_attr($years_experience); ?>" min="0" max="70" required>
                        </div>
                        
                        <div class="bpp-form-field">
                            <label for="bpp_status"><?php echo esc_html__('Status', 'black-potential-pipeline'); ?></label>
                            <select id="bpp_status" name="bpp_status">
                                <option value="publish" <?php selected($status, 'publish'); ?>><?php echo esc_html__('Approved', 'black-potential-pipeline'); ?></option>
                                <option value="draft" <?php selected($status, 'draft'); ?>><?php echo esc_html__('New Application', 'black-potential-pipeline'); ?></option>
                                <option value="private" <?php selected($status, 'private'); ?>><?php echo esc_html__('Rejected', 'black-potential-pipeline'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="bpp-form-row">
                        <div class="bpp-form-field bpp-checkbox-field">
                            <input type="checkbox" id="bpp_featured" name="bpp_featured" value="1" <?php checked($featured, true); ?>>
                            <label for="bpp_featured"><?php echo esc_html__('Featured Professional', 'black-potential-pipeline'); ?></label>
                        </div>
                    </div>
                </div>
                
                <div class="bpp-form-section">
                    <h4><?php echo esc_html__('Contact Information', 'black-potential-pipeline'); ?></h4>
                    
                    <div class="bpp-form-row">
                        <div class="bpp-form-field">
                            <label for="bpp_email"><?php echo esc_html__('Email', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                            <input type="email" id="bpp_email" name="bpp_email" value="<?php echo esc_attr($email); ?>" required>
                        </div>
                        
                        <div class="bpp-form-field">
                            <label for="bpp_phone"><?php echo esc_html__('Phone', 'black-potential-pipeline'); ?></label>
                            <input type="tel" id="bpp_phone" name="bpp_phone" value="<?php echo esc_attr($phone); ?>">
                        </div>
                    </div>
                    
                    <div class="bpp-form-row">
                        <div class="bpp-form-field">
                            <label for="bpp_linkedin"><?php echo esc_html__('LinkedIn Profile', 'black-potential-pipeline'); ?></label>
                            <input type="url" id="bpp_linkedin" name="bpp_linkedin" value="<?php echo esc_attr($linkedin); ?>" placeholder="https://linkedin.com/in/username">
                        </div>
                        
                        <div class="bpp-form-field">
                            <label for="bpp_website"><?php echo esc_html__('Personal Website', 'black-potential-pipeline'); ?></label>
                            <input type="url" id="bpp_website" name="bpp_website" value="<?php echo esc_attr($website); ?>" placeholder="https://example.com">
                        </div>
                    </div>
                </div>
                
                <div class="bpp-form-section">
                    <h4><?php echo esc_html__('Skills & Experience', 'black-potential-pipeline'); ?></h4>
                    
                    <div class="bpp-form-field">
                        <label for="bpp_skills"><?php echo esc_html__('Skills', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                        <input type="text" id="bpp_skills" name="bpp_skills" value="<?php echo esc_attr($skills); ?>" required placeholder="<?php echo esc_attr__('Enter skills separated by commas', 'black-potential-pipeline'); ?>">
                        <p class="description"><?php echo esc_html__('Enter skills separated by commas (e.g. Project Management, Renewable Energy, Green Building)', 'black-potential-pipeline'); ?></p>
                    </div>
                    
                    <div class="bpp-form-field">
                        <label for="bpp_bio"><?php echo esc_html__('Professional Bio', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                        <textarea id="bpp_bio" name="bpp_bio" rows="6" required><?php echo esc_textarea($bio); ?></textarea>
                    </div>
                </div>
                
                <div class="bpp-form-actions">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php echo esc_html__('Save Changes', 'black-potential-pipeline'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Profile View Styles */
.bpp-admin-profile-view {
    max-width: 1200px;
}

.bpp-profile-actions {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}

.bpp-profile-container {
    background-color: #fff;
    border: 1px solid #e2e4e7;
    border-radius: 4px;
}

.bpp-profile-header-card {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #e2e4e7;
}

.bpp-profile-header-info {
    display: flex;
    gap: 20px;
}

.bpp-profile-photo {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #f1f1f1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bpp-profile-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bpp-no-photo .dashicons {
    font-size: 48px;
    color: #999;
}

.bpp-profile-header-details h2 {
    margin-top: 0;
    margin-bottom: 10px;
}

.bpp-profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 10px;
}

.bpp-meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #555;
}

.bpp-meta-item .dashicons {
    color: #777;
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.bpp-profile-status {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.bpp-status-badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    color: white;
}

.bpp-status-approved {
    background-color: #46b450;
}

.bpp-status-new {
    background-color: #00a0d2;
}

.bpp-status-rejected {
    background-color: #dc3232;
}

.bpp-featured-badge {
    display: flex;
    align-items: center;
    gap: 5px;
    background-color: #f0b849;
    color: #333;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.bpp-featured-badge .dashicons {
    color: #f0810f;
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.bpp-profile-header-dates {
    display: flex;
    flex-direction: column;
    gap: 5px;
    color: #666;
    font-size: 13px;
}

.bpp-date-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.bpp-date-label {
    font-weight: 600;
}

.bpp-edit-profile-form-container {
    padding: 20px;
}

.bpp-form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.bpp-form-section h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #23282d;
    font-size: 16px;
}

.bpp-form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 15px;
}

.bpp-form-field {
    flex: 1;
    min-width: 250px;
}

.bpp-form-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.bpp-form-field input,
.bpp-form-field select,
.bpp-form-field textarea {
    width: 100%;
    max-width: 100%;
}

.bpp-form-field textarea {
    min-height: 150px;
}

.bpp-checkbox-field {
    display: flex;
    align-items: center;
    gap: 8px;
}

.bpp-checkbox-field input {
    width: auto;
}

.bpp-checkbox-field label {
    margin-bottom: 0;
}

.bpp-form-field .required {
    color: #dc3232;
}

.bpp-form-actions {
    margin-top: 20px;
}

.bpp-form-actions .button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Make it responsive */
@media screen and (max-width: 782px) {
    .bpp-profile-header-card {
        flex-direction: column;
        gap: 15px;
    }
    
    .bpp-profile-header-dates {
        align-self: flex-start;
    }
    
    .bpp-form-field {
        min-width: 100%;
    }
}
</style> 