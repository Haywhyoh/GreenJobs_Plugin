<?php
/**
 * Provide a public-facing view for the submission form
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://example.com
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

// Get industries from taxonomy
$industries = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));

// If industries are not properly formatted, provide default industries
if (empty($industries) || is_wp_error($industries)) {
    $industries = array(
        array('term_id' => 'nature-based-work', 'name' => 'Nature-based work'),
        array('term_id' => 'environmental-policy', 'name' => 'Environmental policy'),
        array('term_id' => 'climate-science', 'name' => 'Climate science'),
        array('term_id' => 'green-construction', 'name' => 'Green construction & infrastructure')
    );
}

// Get form fields configuration from settings (or use defaults)
$required_fields = get_option('bpp_required_fields', array('first_name', 'last_name', 'email', 'industry', 'resume'));
$optional_fields = get_option('bpp_optional_fields', array('phone', 'website', 'linkedin', 'years_experience', 'skills', 'photo', 'bio'));

// Generate nonce for form submission
$nonce = wp_create_nonce('bpp_submission_nonce');
?>

<div class="bpp-form-container">
    <div class="bpp-form-header">
        <h2 class="bpp-form-title"><?php echo esc_html($title); ?></h2>
        <p class="bpp-form-description"><?php echo esc_html($description); ?></p>
    </div>
    
    <div id="bpp-form-messages"></div>
    
    <form id="bpp-submission-form" enctype="multipart/form-data" method="post">
        <?php wp_nonce_field('bpp_submission_nonce', 'bpp_nonce'); ?>
        
        <div class="bpp-form-section">
            <h3><?php _e('Personal Information', 'black-potential-pipeline'); ?></h3>
            
            <div class="bpp-form-row">
                <div class="bpp-form-group">
                    <label for="bpp_first_name">
                        <?php _e('First Name', 'black-potential-pipeline'); ?>
                        <?php if (in_array('first_name', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="text" id="bpp_first_name" name="bpp_first_name" 
                           <?php echo in_array('first_name', $required_fields) ? 'required' : ''; ?>>
                    <span class="bpp-field-error" id="bpp_first_name_error"></span>
                </div>
                
                <div class="bpp-form-group">
                    <label for="bpp_last_name">
                        <?php _e('Last Name', 'black-potential-pipeline'); ?>
                        <?php if (in_array('last_name', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="text" id="bpp_last_name" name="bpp_last_name" 
                           <?php echo in_array('last_name', $required_fields) ? 'required' : ''; ?>>
                    <span class="bpp-field-error" id="bpp_last_name_error"></span>
                </div>
            </div>
            
            <div class="bpp-form-row">
                <div class="bpp-form-group">
                    <label for="bpp_email">
                        <?php _e('Email Address', 'black-potential-pipeline'); ?>
                        <?php if (in_array('email', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="email" id="bpp_email" name="bpp_email" 
                           <?php echo in_array('email', $required_fields) ? 'required' : ''; ?>>
                    <span class="bpp-field-error" id="bpp_email_error"></span>
                </div>
                
                <?php if (in_array('phone', array_merge($required_fields, $optional_fields))) : ?>
                    <div class="bpp-form-group">
                        <label for="bpp_phone">
                            <?php _e('Phone Number', 'black-potential-pipeline'); ?>
                            <?php if (in_array('phone', $required_fields)) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="tel" id="bpp_phone" name="bpp_phone" 
                               <?php echo in_array('phone', $required_fields) ? 'required' : ''; ?>>
                        <span class="bpp-field-error" id="bpp_phone_error"></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bpp-form-section">
            <h3><?php _e('Professional Information', 'black-potential-pipeline'); ?></h3>
            
            <div class="bpp-form-row">
                <div class="bpp-form-group">
                    <label for="bpp_job_title">
                        <?php _e('Job Title', 'black-potential-pipeline'); ?>
                        <?php if (in_array('job_title', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="text" id="bpp_job_title" name="bpp_job_title" 
                           <?php echo in_array('job_title', $required_fields) ? 'required' : ''; ?>>
                    <span class="bpp-field-error" id="bpp_job_title_error"></span>
                </div>
                
                <div class="bpp-form-group">
                    <label for="bpp_industry">
                        <?php _e('Industry', 'black-potential-pipeline'); ?>
                        <?php if (in_array('industry', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <select id="bpp_industry" name="bpp_industry" 
                            <?php echo in_array('industry', $required_fields) ? 'required' : ''; ?>>
                        <option value=""><?php _e('Select an industry', 'black-potential-pipeline'); ?></option>
                        <?php foreach ($industries as $industry) : 
                            $term_id = is_object($industry) ? $industry->term_id : $industry['term_id'];
                            $name = is_object($industry) ? $industry->name : $industry['name'];
                        ?>
                            <option value="<?php echo esc_attr($term_id); ?>">
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="bpp-field-error" id="bpp_industry_error"></span>
                </div>
            </div>
            
            <?php if (in_array('years_experience', array_merge($required_fields, $optional_fields))) : ?>
                <div class="bpp-form-group">
                    <label for="bpp_years_experience">
                        <?php _e('Years of Experience', 'black-potential-pipeline'); ?>
                        <?php if (in_array('years_experience', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <select id="bpp_years_experience" name="bpp_years_experience" 
                            <?php echo in_array('years_experience', $required_fields) ? 'required' : ''; ?>>
                        <option value=""><?php _e('Select years of experience', 'black-potential-pipeline'); ?></option>
                        <option value="0-2"><?php _e('0-2 years', 'black-potential-pipeline'); ?></option>
                        <option value="3-5"><?php _e('3-5 years', 'black-potential-pipeline'); ?></option>
                        <option value="6-10"><?php _e('6-10 years', 'black-potential-pipeline'); ?></option>
                        <option value="10+"><?php _e('10+ years', 'black-potential-pipeline'); ?></option>
                    </select>
                    <span class="bpp-field-error" id="bpp_years_experience_error"></span>
                </div>
            <?php endif; ?>
            
            <?php if (in_array('skills', array_merge($required_fields, $optional_fields))) : ?>
                <div class="bpp-form-group">
                    <label for="bpp_skills">
                        <?php _e('Skills', 'black-potential-pipeline'); ?>
                        <?php if (in_array('skills', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <textarea id="bpp_skills" name="bpp_skills" rows="3" 
                              placeholder="<?php _e('List your skills separated by commas', 'black-potential-pipeline'); ?>"
                              <?php echo in_array('skills', $required_fields) ? 'required' : ''; ?>></textarea>
                    <span class="bpp-field-error" id="bpp_skills_error"></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bpp-form-section">
            <h3><?php _e('Online Presence', 'black-potential-pipeline'); ?></h3>
            
            <?php if (in_array('website', array_merge($required_fields, $optional_fields))) : ?>
                <div class="bpp-form-group">
                    <label for="bpp_website">
                        <?php _e('Website', 'black-potential-pipeline'); ?>
                        <?php if (in_array('website', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="url" id="bpp_website" name="bpp_website" 
                           placeholder="https://" 
                           <?php echo in_array('website', $required_fields) ? 'required' : ''; ?>>
                    <span class="bpp-field-error" id="bpp_website_error"></span>
                </div>
            <?php endif; ?>
            
            <?php if (in_array('linkedin', array_merge($required_fields, $optional_fields))) : ?>
                <div class="bpp-form-group">
                    <label for="bpp_linkedin">
                        <?php _e('LinkedIn Profile', 'black-potential-pipeline'); ?>
                        <?php if (in_array('linkedin', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="url" id="bpp_linkedin" name="bpp_linkedin" 
                           placeholder="https://linkedin.com/in/yourprofile" 
                           <?php echo in_array('linkedin', $required_fields) ? 'required' : ''; ?>>
                    <span class="bpp-field-error" id="bpp_linkedin_error"></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bpp-form-section">
            <h3><?php _e('Additional Information', 'black-potential-pipeline'); ?></h3>
            
            <?php if (in_array('bio', array_merge($required_fields, $optional_fields))) : ?>
                <div class="bpp-form-group">
                    <label for="bpp_bio">
                        <?php _e('Professional Bio', 'black-potential-pipeline'); ?>
                        <?php if (in_array('bio', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <textarea id="bpp_bio" name="bpp_bio" rows="5" 
                              placeholder="<?php _e('Tell us about yourself and your professional background', 'black-potential-pipeline'); ?>"
                              <?php echo in_array('bio', $required_fields) ? 'required' : ''; ?>></textarea>
                    <span class="bpp-field-error" id="bpp_bio_error"></span>
                </div>
            <?php endif; ?>
            
            <?php if (in_array('resume', array_merge($required_fields, $optional_fields))) : ?>
                <div class="bpp-form-group">
                    <label for="bpp_resume">
                        <?php _e('Resume/CV (PDF format)', 'black-potential-pipeline'); ?>
                        <?php if (in_array('resume', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="file" id="bpp_resume" name="bpp_resume" accept=".pdf" 
                           <?php echo in_array('resume', $required_fields) ? 'required' : ''; ?>>
                    <span class="bpp-field-error" id="bpp_resume_error"></span>
                    <span class="bpp-field-help"><?php _e('Maximum file size: 5MB', 'black-potential-pipeline'); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (in_array('photo', array_merge($required_fields, $optional_fields))) : ?>
                <div class="bpp-form-group">
                    <label for="bpp_photo">
                        <?php _e('Professional Photo', 'black-potential-pipeline'); ?>
                        <?php if (in_array('photo', $required_fields)) : ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <input type="file" id="bpp_photo" name="bpp_photo" accept="image/*" 
                           <?php echo in_array('photo', $required_fields) ? 'required' : ''; ?>>
                    <span class="bpp-field-error" id="bpp_photo_error"></span>
                    <span class="bpp-field-help"><?php _e('Maximum file size: 2MB. Recommended dimensions: 400x400px', 'black-potential-pipeline'); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bpp-form-section">
            <div class="bpp-form-group bpp-terms-group">
                <label for="bpp_terms">
                    <input type="checkbox" id="bpp_terms" name="bpp_terms" required>
                    <?php _e('I agree to the terms and conditions and consent to having my information stored in the Black Potential Pipeline database.', 'black-potential-pipeline'); ?>
                    <span class="required">*</span>
                </label>
                <span class="bpp-field-error" id="bpp_terms_error"></span>
            </div>
        </div>
        
        <div class="bpp-form-actions">
            <button type="submit" id="bpp-submit-button" class="bpp-button bpp-button-primary">
                <?php _e('Submit Application', 'black-potential-pipeline'); ?>
            </button>
            <div id="bpp-submit-spinner" class="bpp-spinner" style="display: none;"></div>
        </div>
    </form>
    
    <div id="bpp-success-message" class="bpp-success-message" style="display: none;">
        <h3><?php _e('Thank You!', 'black-potential-pipeline'); ?></h3>
        <p><?php _e('Your application has been submitted successfully. Our team will review your information and you\'ll be notified once your profile is approved.', 'black-potential-pipeline'); ?></p>
    </div>
</div>

<!-- Include JavaScript for form validation and submission -->
<script>
    // The form submission logic is handled by bpp-form.js
    // See the script enqueue in the main plugin file
</script> 