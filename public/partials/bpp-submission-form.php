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
$title = isset($atts['title']) ? $atts['title'] : __('Join the Black Potential Pipeline', 'black-potential-pipeline');
$description = isset($atts['description']) ? $atts['description'] : __('Submit your information to be included in our database of Black professionals seeking green jobs.', 'black-potential-pipeline');
$success_message = isset($atts['success_message']) ? $atts['success_message'] : __('Your application has been submitted successfully. Our team will review your information and you\'ll be notified once your profile is approved.', 'black-potential-pipeline');

// Get industries from taxonomy
$industries = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));

// If industries are not properly formatted, provide default industries
if (empty($industries) || is_wp_error($industries)) {
    $industries = array(
        array('term_id' => 'technology', 'name' => __('Technology', 'black-potential-pipeline')),
        array('term_id' => 'finance', 'name' => __('Finance', 'black-potential-pipeline')),
        array('term_id' => 'healthcare', 'name' => __('Healthcare', 'black-potential-pipeline')),
        array('term_id' => 'education', 'name' => __('Education', 'black-potential-pipeline')),
        array('term_id' => 'manufacturing', 'name' => __('Manufacturing', 'black-potential-pipeline')),
        array('term_id' => 'retail', 'name' => __('Retail', 'black-potential-pipeline')),
        array('term_id' => 'media', 'name' => __('Media & Entertainment', 'black-potential-pipeline')),
        array('term_id' => 'nonprofit', 'name' => __('Non-profit', 'black-potential-pipeline')),
        array('term_id' => 'other', 'name' => __('Other', 'black-potential-pipeline')),
    );
}

// Get form fields configuration from settings (or use defaults)
$required_fields = get_option('bpp_required_fields', array('first_name', 'last_name', 'email', 'industry', 'resume'));
$optional_fields = get_option('bpp_optional_fields', array('phone', 'website', 'linkedin', 'years_experience', 'skills', 'photo', 'bio'));

// Generate nonce for form submission
$nonce = wp_create_nonce('bpp_form_nonce');
?>

<div class="bpp-form-container">
    <div class="bpp-form-header">
        <h2 class="bpp-form-title"><?php echo esc_html($title); ?></h2>
        <p class="bpp-form-description"><?php echo esc_html($description); ?></p>
    </div>

    <div id="bpp-form-messages" class="bpp-form-messages bpp-error" style="display: none;"></div>

    <form id="bpp-submission-form" enctype="multipart/form-data" method="post" class="bpp-form" novalidate>
        <?php wp_nonce_field('bpp_form_nonce', 'bpp_nonce'); ?>
        
        <div class="bpp-card">
            <div class="bpp-card-header">
                <h3><?php _e('Personal Information', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <div class="bpp-form-row">
                    <div class="bpp-form-field bpp-half-width">
                        <label for="bpp_first_name">
                            <?php _e('First Name', 'black-potential-pipeline'); ?>
                            <?php if (in_array('first_name', $required_fields)) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="bpp_first_name" name="first_name" 
                               <?php echo in_array('first_name', $required_fields) ? 'required' : ''; ?>>
                        <div id="bpp_first_name_error" class="bpp-field-error"></div>
                    </div>
                    
                    <div class="bpp-form-field bpp-half-width">
                        <label for="bpp_last_name">
                            <?php _e('Last Name', 'black-potential-pipeline'); ?>
                            <?php if (in_array('last_name', $required_fields)) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="bpp_last_name" name="last_name" 
                               <?php echo in_array('last_name', $required_fields) ? 'required' : ''; ?>>
                        <div id="bpp_last_name_error" class="bpp-field-error"></div>
                    </div>
                </div>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field bpp-half-width">
                        <label for="bpp_email">
                            <?php _e('Email Address', 'black-potential-pipeline'); ?>
                            <?php if (in_array('email', $required_fields)) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="email" id="bpp_email" name="email" 
                               <?php echo in_array('email', $required_fields) ? 'required' : ''; ?>>
                        <div id="bpp_email_error" class="bpp-field-error"></div>
                    </div>
                    
                    <?php if (in_array('phone', array_merge($required_fields, $optional_fields))) : ?>
                        <div class="bpp-form-field bpp-half-width">
                            <label for="bpp_phone">
                                <?php _e('Phone Number', 'black-potential-pipeline'); ?>
                                <?php if (in_array('phone', $required_fields)) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="tel" id="bpp_phone" name="phone" 
                                   <?php echo in_array('phone', $required_fields) ? 'required' : ''; ?>>
                            <div id="bpp_phone_error" class="bpp-field-error"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="bpp-card">
            <div class="bpp-card-header">
                <h3><?php _e('Professional Information', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <div class="bpp-form-row">
                    <div class="bpp-form-field bpp-half-width">
                        <label for="bpp_job_title">
                            <?php _e('Job Title', 'black-potential-pipeline'); ?>
                            <?php if (in_array('job_title', $required_fields)) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="bpp_job_title" name="job_title" 
                               <?php echo in_array('job_title', $required_fields) ? 'required' : ''; ?>>
                        <div id="bpp_job_title_error" class="bpp-field-error"></div>
                    </div>
                    
                    <div class="bpp-form-field bpp-half-width">
                        <label for="bpp_industry">
                            <?php _e('Industry', 'black-potential-pipeline'); ?>
                            <?php if (in_array('industry', $required_fields)) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                        <select id="bpp_industry" name="industry" 
                                <?php echo in_array('industry', $required_fields) ? 'required' : ''; ?>>
                            <option value=""><?php _e('Select an industry', 'black-potential-pipeline'); ?></option>
                            <?php 
                            foreach ($industries as $industry) : 
                                // Determine if we're dealing with a term object or a simple array
                                if (is_object($industry) && isset($industry->term_id) && isset($industry->name)) {
                                    $term_id = $industry->slug;
                                    $name = $industry->name;
                                } elseif (is_array($industry) && isset($industry['term_id']) && isset($industry['name'])) {
                                    $term_id = $industry['term_id'];
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
                        <div id="bpp_industry_error" class="bpp-field-error"></div>
                    </div>
                </div>
                
                <?php if (in_array('years_experience', array_merge($required_fields, $optional_fields))) : ?>
                    <div class="bpp-form-row">
                        <div class="bpp-form-field bpp-half-width">
                            <label for="bpp_years_experience">
                                <?php _e('Years of Experience', 'black-potential-pipeline'); ?>
                                <?php if (in_array('years_experience', $required_fields)) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="number" id="bpp_years_experience" name="years_experience" min="0" max="50" 
                                   <?php echo in_array('years_experience', $required_fields) ? 'required' : ''; ?>>
                            <div id="bpp_years_experience_error" class="bpp-field-error"></div>
                        </div>
                        
                        <?php if (in_array('website', array_merge($required_fields, $optional_fields))) : ?>
                            <div class="bpp-form-field bpp-half-width">
                                <label for="bpp_website">
                                    <?php _e('Website', 'black-potential-pipeline'); ?>
                                    <?php if (in_array('website', $required_fields)) : ?>
                                        <span class="required">*</span>
                                    <?php endif; ?>
                                </label>
                                <input type="url" id="bpp_website" name="website" 
                                       placeholder="https://" 
                                       <?php echo in_array('website', $required_fields) ? 'required' : ''; ?>>
                                <div id="bpp_website_error" class="bpp-field-error"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('linkedin', array_merge($required_fields, $optional_fields))) : ?>
                    <div class="bpp-form-row">
                        <div class="bpp-form-field bpp-full-width">
                            <label for="bpp_linkedin">
                                <?php _e('LinkedIn Profile', 'black-potential-pipeline'); ?>
                                <?php if (in_array('linkedin', $required_fields)) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="url" id="bpp_linkedin" name="linkedin" 
                                   placeholder="https://linkedin.com/in/your-profile" 
                                   <?php echo in_array('linkedin', $required_fields) ? 'required' : ''; ?>>
                            <div id="bpp_linkedin_error" class="bpp-field-error"></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('skills', array_merge($required_fields, $optional_fields))) : ?>
                    <div class="bpp-form-row">
                        <div class="bpp-form-field bpp-full-width">
                            <label for="bpp_skills">
                                <?php _e('Skills', 'black-potential-pipeline'); ?>
                                <?php if (in_array('skills', $required_fields)) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <textarea id="bpp_skills" name="skills" rows="3" 
                                      <?php echo in_array('skills', $required_fields) ? 'required' : ''; ?>></textarea>
                            <p class="bpp-field-description"><?php _e('List your key skills, separated by commas.', 'black-potential-pipeline'); ?></p>
                            <div id="bpp_skills_error" class="bpp-field-error"></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('bio', array_merge($required_fields, $optional_fields))) : ?>
                    <div class="bpp-form-row">
                        <div class="bpp-form-field bpp-full-width">
                            <label for="bpp_bio">
                                <?php _e('Professional Bio', 'black-potential-pipeline'); ?>
                                <?php if (in_array('bio', $required_fields)) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <textarea id="bpp_bio" name="bio" rows="5" 
                                      <?php echo in_array('bio', $required_fields) ? 'required' : ''; ?>></textarea>
                            <p class="bpp-field-description"><?php _e('A brief professional bio about yourself.', 'black-potential-pipeline'); ?></p>
                            <div id="bpp_bio_error" class="bpp-field-error"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bpp-card">
            <div class="bpp-card-header">
                <h3><?php _e('Files and Documents', 'black-potential-pipeline'); ?></h3>
            </div>
            <div class="bpp-card-body">
                <?php if (in_array('resume', array_merge($required_fields, $optional_fields))) : ?>
                    <div class="bpp-form-row">
                        <div class="bpp-form-field bpp-full-width">
                            <label for="bpp_resume">
                                <?php _e('Resume/CV', 'black-potential-pipeline'); ?>
                                <?php if (in_array('resume', $required_fields)) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="file" id="bpp_resume" name="resume" 
                                   accept=".pdf,.doc,.docx"
                                   <?php echo in_array('resume', $required_fields) ? 'required' : ''; ?>>
                            <p class="bpp-field-description"><?php _e('Upload your resume (PDF, DOC, or DOCX format, max 5MB).', 'black-potential-pipeline'); ?></p>
                            <div id="bpp_resume_error" class="bpp-field-error"></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array('photo', array_merge($required_fields, $optional_fields))) : ?>
                    <div class="bpp-form-row">
                        <div class="bpp-form-field bpp-full-width">
                            <label for="bpp_photo">
                                <?php _e('Professional Photo', 'black-potential-pipeline'); ?>
                                <?php if (in_array('photo', $required_fields)) : ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="file" id="bpp_photo" name="photo" 
                                   accept=".jpg,.jpeg,.png,.gif"
                                   <?php echo in_array('photo', $required_fields) ? 'required' : ''; ?>>
                            <p class="bpp-field-description"><?php _e('Upload a professional photo (JPG, PNG, or GIF format, max 2MB).', 'black-potential-pipeline'); ?></p>
                            <div id="bpp_photo_error" class="bpp-field-error"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bpp-form-row">
            <div class="bpp-form-field bpp-full-width">
                <div class="bpp-checkbox-field">
                    <input type="checkbox" id="bpp_terms" name="terms" required>
                    <label for="bpp_terms">
                        <?php _e('I agree to have my information stored and displayed in the Black Potential Pipeline directory.', 'black-potential-pipeline'); ?>
                        <span class="required">*</span>
                    </label>
                </div>
                <div id="bpp_terms_error" class="bpp-field-error"></div>
            </div>
        </div>
        
        <div class="bpp-form-actions">
            <button type="submit" id="bpp-submit-button" class="bpp-button bpp-button-primary">
                <?php _e('Submit Application', 'black-potential-pipeline'); ?>
                <span id="bpp-submit-spinner" class="bpp-spinner" style="display: none;"></span>
            </button>
        </div>
    </form>
    
    <div id="bpp-success-message" style="display: none;">
        <h3><?php _e('Thank You!', 'black-potential-pipeline'); ?></h3>
        <p><?php echo esc_html($success_message); ?></p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('Form initialized');
    // Form handling is managed by the bpp-form.js script
});
</script> 