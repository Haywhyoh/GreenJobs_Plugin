<?php
/**
 * Provide a public-facing view for the submission form
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://codemygig.com,
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/public/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Set default values for attributes
$title = isset($atts['title']) ? sanitize_text_field($atts['title']) : __('Apply to the Black Potential Pipeline', 'black-potential-pipeline');
$description = isset($atts['description']) ? sanitize_text_field($atts['description']) : __('Submit your information to be included in our database of Black professionals seeking green jobs.', 'black-potential-pipeline');
$use_bootstrap = isset($atts['use_bootstrap']) ? $atts['use_bootstrap'] === 'yes' : true;

// Get industries from taxonomy
$industries = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));

// If industries are not properly formatted, provide default industries
if (empty($industries) || is_wp_error($industries)) {
    $industries = array(
        array('slug' => 'nature-based-work', 'term_id' => 'nature-based-work', 'name' => __('Nature-based work', 'black-potential-pipeline')),
        array('slug' => 'environmental-policy', 'term_id' => 'environmental-policy', 'name' => __('Environmental policy', 'black-potential-pipeline')),
        array('slug' => 'climate-science', 'term_id' => 'climate-science', 'name' => __('Climate science', 'black-potential-pipeline')),
        array('slug' => 'green-construction', 'term_id' => 'green-construction', 'name' => __('Green construction & infrastructure', 'black-potential-pipeline')),
    );
}

// Get form fields configuration from settings (or use defaults)
$required_fields = get_option('bpp_required_fields', array('first_name', 'last_name', 'email', 'industry', 'resume'));
$optional_fields = get_option('bpp_optional_fields', array('phone', 'website', 'linkedin', 'years_experience', 'skills', 'photo', 'bio'));

// Generate nonce for form submission
$nonce = wp_create_nonce('bpp_form_nonce');

// Define CSS classes based on whether Bootstrap is enabled
$container_class = $use_bootstrap ? 'container py-4 bpp-submission-form' : 'bpp-form-container';
$form_class = $use_bootstrap ? 'needs-validation' : '';
$form_group_class = $use_bootstrap ? 'mb-3' : 'bpp-form-group';
$form_row_class = $use_bootstrap ? 'row g-3' : 'bpp-form-row';
$input_class = $use_bootstrap ? 'form-control' : '';
$select_class = $use_bootstrap ? 'form-select' : '';
$label_class = $use_bootstrap ? 'form-label' : '';
$check_class = $use_bootstrap ? 'form-check' : 'bpp-terms-group';
$check_input_class = $use_bootstrap ? 'form-check-input' : '';
$check_label_class = $use_bootstrap ? 'form-check-label' : '';
$button_class = $use_bootstrap ? 'btn btn-primary' : 'bpp-button bpp-button-primary';
$required_class = $use_bootstrap ? 'text-danger' : 'required';
$section_class = $use_bootstrap ? 'mb-4' : 'bpp-form-section';
$col_class = $use_bootstrap ? 'col-md-6' : '';
$card_class = $use_bootstrap ? 'card mb-4' : '';
$card_header_class = $use_bootstrap ? 'card-header bg-primary text-white' : '';
$card_body_class = $use_bootstrap ? 'card-body' : '';
$alert_success_class = $use_bootstrap ? 'alert alert-success' : 'bpp-form-messages bpp-success';
$alert_error_class = $use_bootstrap ? 'alert alert-danger' : 'bpp-form-messages bpp-error';
$error_feedback_class = $use_bootstrap ? 'invalid-feedback' : 'bpp-field-error';
$help_text_class = $use_bootstrap ? 'form-text text-muted' : 'bpp-field-help';
$spinner_class = $use_bootstrap ? 'spinner-border spinner-border-sm text-light ms-2' : 'bpp-spinner';
?>

<div class="<?php echo esc_attr($container_class); ?>">
    <?php if ($use_bootstrap): ?>
    <div class="row justify-content-center">
        <div class="col-lg-10">
    <?php endif; ?>
    
            <?php if ($use_bootstrap): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="m-0"><?php echo esc_html($title); ?></h2>
                </div>
                <div class="card-body">
                    <p class="lead"><?php echo esc_html($description); ?></p>
                </div>
            </div>
            <?php else: ?>
            <div class="bpp-form-header">
                <h2 class="bpp-form-title"><?php echo esc_html($title); ?></h2>
                <p class="bpp-form-description"><?php echo esc_html($description); ?></p>
            </div>
            <?php endif; ?>
    
            <div id="bpp-form-messages" class="<?php echo esc_attr($alert_error_class); ?>" style="display: none;"></div>
    
            <form id="bpp-submission-form" enctype="multipart/form-data" method="post" class="<?php echo esc_attr($form_class); ?>" novalidate>
                <?php wp_nonce_field('bpp_form_nonce', 'bpp_nonce'); ?>
                
                <?php if ($use_bootstrap): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 m-0"><?php _e('Personal Information', 'black-potential-pipeline'); ?></h3>
                    </div>
                    <div class="card-body">
                <?php else: ?>
                <div class="bpp-form-section">
                    <h3><?php _e('Personal Information', 'black-potential-pipeline'); ?></h3>
                <?php endif; ?>
                
                    <div class="<?php echo esc_attr($form_row_class); ?>">
                        <div class="<?php echo esc_attr($col_class); ?>">
                            <div class="<?php echo esc_attr($form_group_class); ?>">
                                <label for="bpp_first_name" class="<?php echo esc_attr($label_class); ?>">
                                    <?php _e('First Name', 'black-potential-pipeline'); ?>
                                    <?php if (in_array('first_name', $required_fields)) : ?>
                                        <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" id="bpp_first_name" name="first_name" 
                                       class="<?php echo esc_attr($input_class); ?>"
                                       <?php echo in_array('first_name', $required_fields) ? 'required' : ''; ?>>
                                <div id="bpp_first_name_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                            </div>
                        </div>
                        
                        <div class="<?php echo esc_attr($col_class); ?>">
                            <div class="<?php echo esc_attr($form_group_class); ?>">
                                <label for="bpp_last_name" class="<?php echo esc_attr($label_class); ?>">
                                    <?php _e('Last Name', 'black-potential-pipeline'); ?>
                                    <?php if (in_array('last_name', $required_fields)) : ?>
                                        <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" id="bpp_last_name" name="last_name" 
                                       class="<?php echo esc_attr($input_class); ?>"
                                       <?php echo in_array('last_name', $required_fields) ? 'required' : ''; ?>>
                                <div id="bpp_last_name_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="<?php echo esc_attr($form_row_class); ?>">
                        <div class="<?php echo esc_attr($col_class); ?>">
                            <div class="<?php echo esc_attr($form_group_class); ?>">
                                <label for="bpp_email" class="<?php echo esc_attr($label_class); ?>">
                                    <?php _e('Email Address', 'black-potential-pipeline'); ?>
                                    <?php if (in_array('email', $required_fields)) : ?>
                                        <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                    <?php endif; ?>
                                </label>
                                <input type="email" id="bpp_email" name="email" 
                                       class="<?php echo esc_attr($input_class); ?>"
                                       <?php echo in_array('email', $required_fields) ? 'required' : ''; ?>>
                                <div id="bpp_email_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                            </div>
                        </div>
                        
                        <?php if (in_array('phone', array_merge($required_fields, $optional_fields))) : ?>
                            <div class="<?php echo esc_attr($col_class); ?>">
                                <div class="<?php echo esc_attr($form_group_class); ?>">
                                    <label for="bpp_phone" class="<?php echo esc_attr($label_class); ?>">
                                        <?php _e('Phone Number', 'black-potential-pipeline'); ?>
                                        <?php if (in_array('phone', $required_fields)) : ?>
                                            <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <input type="tel" id="bpp_phone" name="phone" 
                                           class="<?php echo esc_attr($input_class); ?>"
                                           <?php echo in_array('phone', $required_fields) ? 'required' : ''; ?>>
                                    <div id="bpp_phone_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php if ($use_bootstrap): ?>
                    </div>
                </div>
                <?php else: ?>
                </div>
                <?php endif; ?>
                
                <?php if ($use_bootstrap): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 m-0"><?php _e('Professional Information', 'black-potential-pipeline'); ?></h3>
                    </div>
                    <div class="card-body">
                <?php else: ?>
                <div class="bpp-form-section">
                    <h3><?php _e('Professional Information', 'black-potential-pipeline'); ?></h3>
                <?php endif; ?>
                
                    <div class="<?php echo esc_attr($form_row_class); ?>">
                        <div class="<?php echo esc_attr($col_class); ?>">
                            <div class="<?php echo esc_attr($form_group_class); ?>">
                                <label for="bpp_job_title" class="<?php echo esc_attr($label_class); ?>">
                                    <?php _e('Job Title', 'black-potential-pipeline'); ?>
                                    <?php if (in_array('job_title', $required_fields)) : ?>
                                        <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" id="bpp_job_title" name="job_title" 
                                       class="<?php echo esc_attr($input_class); ?>"
                                       <?php echo in_array('job_title', $required_fields) ? 'required' : ''; ?>>
                                <div id="bpp_job_title_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                            </div>
                        </div>
                        
                        <div class="<?php echo esc_attr($col_class); ?>">
                            <div class="<?php echo esc_attr($form_group_class); ?>">
                                <label for="bpp_industry" class="<?php echo esc_attr($label_class); ?>">
                                    <?php _e('Industry', 'black-potential-pipeline'); ?>
                                    <?php if (in_array('industry', $required_fields)) : ?>
                                        <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                    <?php endif; ?>
                                </label>
                                <select id="bpp_industry" name="industry" 
                                        class="<?php echo esc_attr($select_class); ?>"
                                        <?php echo in_array('industry', $required_fields) ? 'required' : ''; ?>>
                                    <option value=""><?php _e('Select an industry', 'black-potential-pipeline'); ?></option>
                                    <?php 
                                    foreach ($industries as $industry) : 
                                        // Determine if we're dealing with a term object or a simple array
                                        if (is_object($industry) && isset($industry->term_id) && isset($industry->name)) {
                                            $term_id = $industry->slug;
                                            $name = $industry->name;
                                        } elseif (is_array($industry) && isset($industry['term_id']) && isset($industry['name'])) {
                                            $term_id = $industry['slug'];
                                            $name = $industry['name'];
                                        } else {
                                            continue; // Skip if neither format is valid
                                        }
                                    ?>
                                        <option value="<?php echo esc_attr($term_id); ?>">
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="bpp_industry_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="<?php echo esc_attr($form_group_class); ?>">
                        <label for="years_experience"><?php _e('Years of Experience', 'business-professional-profiles'); ?> <span class="required">*</span></label>
                        <input type="number" name="years_experience" id="years_experience" class="form-control" min="0" required>
                    </div>
                    
                    <?php if (in_array('skills', array_merge($required_fields, $optional_fields))) : ?>
                        <div class="<?php echo esc_attr($form_group_class); ?>">
                            <label for="bpp_skills" class="<?php echo esc_attr($label_class); ?>">
                                <?php _e('Skills', 'black-potential-pipeline'); ?>
                                <?php if (in_array('skills', $required_fields)) : ?>
                                    <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                <?php endif; ?>
                            </label>
                            <textarea id="bpp_skills" name="skills" rows="3" 
                                      class="<?php echo esc_attr($input_class); ?>"
                                      placeholder="<?php _e('List your skills separated by commas', 'black-potential-pipeline'); ?>"
                                      <?php echo in_array('skills', $required_fields) ? 'required' : ''; ?>></textarea>
                            <div id="bpp_skills_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                        </div>
                    <?php endif; ?>
                <?php if ($use_bootstrap): ?>
                    </div>
                </div>
                <?php else: ?>
                </div>
                <?php endif; ?>
                
                <?php if (in_array('website', array_merge($required_fields, $optional_fields)) || in_array('linkedin', array_merge($required_fields, $optional_fields))): ?>
                <?php if ($use_bootstrap): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 m-0"><?php _e('Online Presence', 'black-potential-pipeline'); ?></h3>
                    </div>
                    <div class="card-body">
                <?php else: ?>
                <div class="bpp-form-section">
                    <h3><?php _e('Online Presence', 'black-potential-pipeline'); ?></h3>
                <?php endif; ?>
                
                    <?php if (in_array('website', array_merge($required_fields, $optional_fields))) : ?>
                        <div class="<?php echo esc_attr($form_group_class); ?>">
                            <label for="bpp_website" class="<?php echo esc_attr($label_class); ?>">
                                <?php _e('Website', 'black-potential-pipeline'); ?>
                                <?php if (in_array('website', $required_fields)) : ?>
                                    <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="url" id="bpp_website" name="website" 
                                   class="<?php echo esc_attr($input_class); ?>"
                                   placeholder="https://" 
                                   <?php echo in_array('website', $required_fields) ? 'required' : ''; ?>>
                            <div id="bpp_website_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array('linkedin', array_merge($required_fields, $optional_fields))) : ?>
                        <div class="<?php echo esc_attr($form_group_class); ?>">
                            <label for="bpp_linkedin" class="<?php echo esc_attr($label_class); ?>">
                                <?php _e('LinkedIn Profile', 'black-potential-pipeline'); ?>
                                <?php if (in_array('linkedin', $required_fields)) : ?>
                                    <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="url" id="bpp_linkedin" name="linkedin" 
                                   class="<?php echo esc_attr($input_class); ?>"
                                   placeholder="https://linkedin.com/in/yourprofile" 
                                   <?php echo in_array('linkedin', $required_fields) ? 'required' : ''; ?>>
                            <div id="bpp_linkedin_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                        </div>
                    <?php endif; ?>
                <?php if ($use_bootstrap): ?>
                    </div>
                </div>
                <?php else: ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($use_bootstrap): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 m-0"><?php _e('Additional Information', 'black-potential-pipeline'); ?></h3>
                    </div>
                    <div class="card-body">
                <?php else: ?>
                <div class="bpp-form-section">
                    <h3><?php _e('Additional Information', 'black-potential-pipeline'); ?></h3>
                <?php endif; ?>
                
                    <?php if (in_array('bio', array_merge($required_fields, $optional_fields))) : ?>
                        <div class="<?php echo esc_attr($form_group_class); ?>">
                            <label for="bpp_bio" class="<?php echo esc_attr($label_class); ?>">
                                <?php _e('Professional Bio', 'black-potential-pipeline'); ?>
                                <?php if (in_array('bio', $required_fields)) : ?>
                                    <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                <?php endif; ?>
                            </label>
                            <textarea id="bpp_bio" name="cover_letter" rows="5" 
                                      class="<?php echo esc_attr($input_class); ?>"
                                      placeholder="<?php _e('Tell us about yourself and your professional background', 'black-potential-pipeline'); ?>"
                                      <?php echo in_array('bio', $required_fields) ? 'required' : ''; ?>></textarea>
                            <div id="bpp_bio_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array('resume', array_merge($required_fields, $optional_fields))) : ?>
                        <div class="<?php echo esc_attr($form_group_class); ?>">
                            <label for="bpp_resume" class="<?php echo esc_attr($label_class); ?>">
                                <?php _e('Resume/CV (PDF format)', 'black-potential-pipeline'); ?>
                                <?php if (in_array('resume', $required_fields)) : ?>
                                    <span class="<?php echo esc_attr($required_class); ?>">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="file" id="bpp_resume" name="resume" 
                                   class="<?php echo $use_bootstrap ? 'form-control' : ''; ?>"
                                   accept=".pdf,.doc,.docx" 
                                   <?php echo in_array('resume', $required_fields) ? 'required' : ''; ?>>
                            <div id="bpp_resume_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                            <small class="<?php echo esc_attr($help_text_class); ?>">
                                <?php _e('Maximum file size: 5MB. Accepted formats: PDF, DOC, DOCX', 'black-potential-pipeline'); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array('photo', array_merge($required_fields, $optional_fields)) || true): ?>
                        <div class="<?php echo esc_attr($form_group_class); ?>">
                            <label for="bpp_photo" class="<?php echo esc_attr($label_class); ?>">
                                <?php _e('Professional Photo', 'black-potential-pipeline'); ?>
                                <span class="<?php echo esc_attr($required_class); ?>">*</span>
                            </label>
                            <input type="file" id="bpp_photo" name="professional_photo" 
                                   class="<?php echo $use_bootstrap ? 'form-control' : ''; ?>"
                                   accept="image/*" 
                                   required>
                            <div id="bpp_photo_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                            <small class="<?php echo esc_attr($help_text_class); ?>">
                                <?php _e('Upload a professional headshot or profile picture. Maximum file size: 2MB. Accepted formats: JPG, PNG, GIF', 'black-potential-pipeline'); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                <?php if ($use_bootstrap): ?>
                    </div>
                </div>
                <?php else: ?>
                </div>
                <?php endif; ?>
                
                <?php if ($use_bootstrap): ?>
                <div class="card mb-4">
                    <div class="card-body">
                <?php else: ?>
                <div class="bpp-form-section">
                <?php endif; ?>
                    <div class="<?php echo esc_attr($check_class); ?>">
                        <input type="checkbox" id="bpp_terms" name="consent" required 
                               class="<?php echo esc_attr($check_input_class); ?>">
                        <label for="bpp_terms" class="<?php echo esc_attr($check_label_class); ?>">
                            <?php _e('I agree to the terms and conditions and consent to having my information stored in the Black Potential Pipeline database.', 'black-potential-pipeline'); ?>
                            <span class="<?php echo esc_attr($required_class); ?>">*</span>
                        </label>
                        <div id="bpp_terms_error" class="<?php echo esc_attr($error_feedback_class); ?>"></div>
                    </div>
                <?php if ($use_bootstrap): ?>
                    </div>
                </div>
                <?php else: ?>
                </div>
                <?php endif; ?>
                
                <?php if ($use_bootstrap): ?>
                <div class="text-center mb-4">
                <?php else: ?>
                <div class="bpp-form-actions">
                <?php endif; ?>
                    <button type="submit" id="bpp-submit-button" class="<?php echo esc_attr($button_class); ?>">
                        <?php _e('Submit Application', 'black-potential-pipeline'); ?>
                        <span id="bpp-submit-spinner" class="<?php echo esc_attr($spinner_class); ?>" style="display: none;"></span>
                    </button>
                </div>
            </form>
            
            <?php if ($use_bootstrap): ?>
            <div id="bpp-success-message" class="alert alert-success" style="display: none;">
                <h3 class="h4"><?php _e('Thank You!', 'black-potential-pipeline'); ?></h3>
                <p><?php _e('Your application has been submitted successfully. Our team will review your information and you\'ll be notified once your profile is approved.', 'black-potential-pipeline'); ?></p>
            </div>
            <?php else: ?>
            <div id="bpp-success-message" class="bpp-success-message" style="display: none;">
                <h3><?php _e('Thank You!', 'black-potential-pipeline'); ?></h3>
                <p><?php _e('Your application has been submitted successfully. Our team will review your information and you\'ll be notified once your profile is approved.', 'black-potential-pipeline'); ?></p>
            </div>
            <?php endif; ?>

    <?php if ($use_bootstrap): ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!$use_bootstrap): ?>
<style>
.bpp-form-messages {
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 4px;
}
.bpp-form-messages.bpp-success {
    background-color: #dff0d8;
    border: 1px solid #d6e9c6;
    color: #3c763d;
}
.bpp-form-messages.bpp-error {
    background-color: #f2dede;
    border: 1px solid #ebccd1;
    color: #a94442;
}
/* When shown, make sure the display is block */
.bpp-form-messages.bpp-success:not([style*="display: none"]),
.bpp-form-messages.bpp-error:not([style*="display: none"]) {
    display: block !important;
}
</style>
<?php endif; ?>

<!-- Include JavaScript for form validation and submission -->
<script>
    // For debugging purposes
    console.log('Form template loaded');
    console.log('Bootstrap enabled:', <?php echo json_encode($use_bootstrap); ?>);
    console.log('Required fields:', <?php echo json_encode($required_fields); ?>);
    
    // The form submission logic is handled by bpp-form.js
    // See the script enqueue in the main plugin file
</script>

<!-- Add GreenJobs color scheme -->
<style>
:root {
    /* GreenJobs color variables - use WordPress theme colors first, then our green/brown scheme */
    --bpp-primary-color: var(--wp--preset--color--primary, #61CE70);
    --bpp-secondary-color: var(--wp--preset--color--secondary, #6F3802);
    --bpp-info-color: var(--wp--preset--color--tertiary, #0dcaf0);
    --bpp-warning-color: var(--wp--preset--color--warning, #ffc107);
    --bpp-light-color: var(--wp--preset--color--light, #f8f9fa);
    --bpp-dark-color: var(--wp--preset--color--dark, #212529);
}

/* Override Bootstrap color classes with theme colors */
.card-header.bg-primary {
    background-color: var(--bpp-primary-color) !important;
}

.btn-primary {
    background-color: var(--bpp-primary-color) !important;
    border-color: var(--bpp-primary-color) !important;
}

.btn-primary:hover {
    background-color: var(--bpp-secondary-color) !important;
    border-color: var(--bpp-secondary-color) !important;
}

/* Customize form styles */
.form-control:focus, .form-select:focus {
    border-color: var(--bpp-primary-color);
    box-shadow: 0 0 0 0.25rem rgba(97, 206, 112, 0.25);
}

/* Success alert styling */
.alert-success {
    background-color: rgba(97, 206, 112, 0.1);
    border-color: var(--bpp-primary-color);
    color: var(--bpp-secondary-color);
}

/* Non-Bootstrap form customizations */
.bpp-form-title {
    color: var(--bpp-secondary-color);
}

.bpp-form-section h3 {
    color: var(--bpp-primary-color);
    border-bottom: 2px solid var(--bpp-primary-color);
    padding-bottom: 8px;
    margin-bottom: 20px;
}

.bpp-success-message {
    background-color: rgba(97, 206, 112, 0.1);
    border-left: 4px solid var(--bpp-primary-color);
    padding: 15px;
    margin-bottom: 20px;
    color: var(--bpp-secondary-color);
}
</style> 