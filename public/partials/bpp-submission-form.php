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

// Get industry terms for dropdown
$industry_terms = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => false,
));
?>

<div class="bpp-submission-form-container">
    <div class="bpp-form-header">
        <h2 class="bpp-form-title"><?php echo esc_html($atts['title']); ?></h2>
        <p class="bpp-form-description">
            <?php echo esc_html__('Join our curated database of Black professionals seeking green jobs. Fill out the form below to submit your application.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <div class="bpp-submission-form-wrapper">
        <form id="bpp-submission-form" class="bpp-form" enctype="multipart/form-data">
            <?php wp_nonce_field('bpp_form_nonce', 'bpp_nonce'); ?>
            
            <div class="bpp-form-notice bpp-form-success" style="display: none;">
                <p><?php echo esc_html($atts['success_message']); ?></p>
            </div>
            
            <div class="bpp-form-notice bpp-form-error" style="display: none;">
                <p class="bpp-error-message"></p>
            </div>
            
            <div class="bpp-form-section">
                <h3><?php echo esc_html__('Personal Information', 'black-potential-pipeline'); ?></h3>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field">
                        <label for="bpp-first-name"><?php echo esc_html__('First Name', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                        <input type="text" id="bpp-first-name" name="first_name" required>
                    </div>
                    
                    <div class="bpp-form-field">
                        <label for="bpp-last-name"><?php echo esc_html__('Last Name', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                        <input type="text" id="bpp-last-name" name="last_name" required>
                    </div>
                </div>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field">
                        <label for="bpp-email"><?php echo esc_html__('Email Address', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                        <input type="email" id="bpp-email" name="email" required>
                    </div>
                    
                    <div class="bpp-form-field">
                        <label for="bpp-phone"><?php echo esc_html__('Phone Number', 'black-potential-pipeline'); ?></label>
                        <input type="tel" id="bpp-phone" name="phone">
                    </div>
                </div>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field">
                        <label for="bpp-linkedin"><?php echo esc_html__('LinkedIn Profile URL', 'black-potential-pipeline'); ?></label>
                        <input type="url" id="bpp-linkedin" name="linkedin" placeholder="https://linkedin.com/in/yourprofile">
                    </div>
                    
                    <div class="bpp-form-field">
                        <label for="bpp-location"><?php echo esc_html__('Location', 'black-potential-pipeline'); ?></label>
                        <input type="text" id="bpp-location" name="location" placeholder="City, State/Province, Country">
                    </div>
                </div>
            </div>
            
            <div class="bpp-form-section">
                <h3><?php echo esc_html__('Professional Information', 'black-potential-pipeline'); ?></h3>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field">
                        <label for="bpp-industry"><?php echo esc_html__('Industry Category', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                        <select id="bpp-industry" name="industry" required>
                            <option value=""><?php echo esc_html__('Select an industry', 'black-potential-pipeline'); ?></option>
                            <?php if (!empty($industry_terms) && !is_wp_error($industry_terms)) : ?>
                                <?php foreach ($industry_terms as $term) : ?>
                                    <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="bpp-form-field">
                        <label for="bpp-job-type"><?php echo esc_html__('Preferred Job Type', 'black-potential-pipeline'); ?></label>
                        <select id="bpp-job-type" name="job_type">
                            <option value=""><?php echo esc_html__('Select job type', 'black-potential-pipeline'); ?></option>
                            <option value="full-time"><?php echo esc_html__('Full-time', 'black-potential-pipeline'); ?></option>
                            <option value="part-time"><?php echo esc_html__('Part-time', 'black-potential-pipeline'); ?></option>
                            <option value="contract"><?php echo esc_html__('Contract', 'black-potential-pipeline'); ?></option>
                            <option value="internship"><?php echo esc_html__('Internship', 'black-potential-pipeline'); ?></option>
                            <option value="any"><?php echo esc_html__('Any', 'black-potential-pipeline'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field">
                        <label for="bpp-job-title"><?php echo esc_html__('Current Job Title', 'black-potential-pipeline'); ?></label>
                        <input type="text" id="bpp-job-title" name="job_title">
                    </div>
                    
                    <div class="bpp-form-field">
                        <label for="bpp-years-experience"><?php echo esc_html__('Years of Experience', 'black-potential-pipeline'); ?></label>
                        <input type="number" id="bpp-years-experience" name="years_experience" min="0" max="50">
                    </div>
                </div>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field bpp-full-width">
                        <label for="bpp-skills"><?php echo esc_html__('Skills & Expertise', 'black-potential-pipeline'); ?></label>
                        <textarea id="bpp-skills" name="skills" rows="3" placeholder="<?php echo esc_attr__('List your key skills, separated by commas', 'black-potential-pipeline'); ?>"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="bpp-form-section">
                <h3><?php echo esc_html__('Application Materials', 'black-potential-pipeline'); ?></h3>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field bpp-full-width">
                        <label for="bpp-cover-letter"><?php echo esc_html__('Cover Letter / Personal Statement', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                        <textarea id="bpp-cover-letter" name="cover_letter" rows="5" required placeholder="<?php echo esc_attr__('Tell us about yourself, your career goals, and why you are interested in green jobs', 'black-potential-pipeline'); ?>"></textarea>
                    </div>
                </div>
                
                <div class="bpp-form-row">
                    <div class="bpp-form-field">
                        <label for="bpp-resume"><?php echo esc_html__('Resume/CV', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                        <input type="file" id="bpp-resume" name="resume" accept=".pdf,.doc,.docx" required>
                        <p class="bpp-field-description"><?php echo esc_html__('Accepted formats: PDF, DOC, DOCX. Max size: 2MB', 'black-potential-pipeline'); ?></p>
                    </div>
                    
                    <div class="bpp-form-field">
                        <label for="bpp-photo"><?php echo esc_html__('Professional Photo', 'black-potential-pipeline'); ?></label>
                        <input type="file" id="bpp-photo" name="photo" accept=".jpg,.jpeg,.png">
                        <p class="bpp-field-description"><?php echo esc_html__('Accepted formats: JPG, JPEG, PNG. Max size: 1MB', 'black-potential-pipeline'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bpp-form-section">
                <div class="bpp-form-row">
                    <div class="bpp-form-field bpp-full-width bpp-checkbox-field">
                        <input type="checkbox" id="bpp-consent" name="consent" value="yes" required>
                        <label for="bpp-consent"><?php echo esc_html__('I consent to having my profile information and materials displayed in the Black Potential Pipeline directory for potential employers to view.', 'black-potential-pipeline'); ?> <span class="required">*</span></label>
                    </div>
                </div>
            </div>
            
            <div class="bpp-form-actions">
                <button type="submit" class="bpp-submit-button"><?php echo esc_html__('Submit Application', 'black-potential-pipeline'); ?></button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#bpp-submission-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'bpp_submit_application');
        formData.append('nonce', bpp_form_obj.nonce);
        
        $('.bpp-form-notice').hide();
        $('.bpp-submit-button').prop('disabled', true).text(bpp_form_obj.i18n.submitting);
        
        $.ajax({
            url: bpp_form_obj.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    $('.bpp-form-success').show();
                    $('#bpp-submission-form').trigger('reset');
                } else {
                    $('.bpp-error-message').text(response.message);
                    $('.bpp-form-error').show();
                }
            },
            error: function() {
                $('.bpp-error-message').text(bpp_form_obj.i18n.submit_error);
                $('.bpp-form-error').show();
            },
            complete: function() {
                $('.bpp-submit-button').prop('disabled', false).text(bpp_form_obj.i18n.submit);
            }
        });
    });
});
</script> 