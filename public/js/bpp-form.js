/**
 * Form handling for the Black Potential Pipeline plugin
 *
 * Handles form validation, file uploads, and AJAX submission
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 */

(function($) {
    'use strict';

    // Form validation and submission
    $(document).ready(function() {
        const $form = $('#bpp-submission-form');
        const $submitButton = $form.find('.bpp-submit-button');
        const originalButtonText = $submitButton.text();

        // File size validation
        function validateFileSize(file, maxSize) {
            // Convert MB to bytes (1MB = 1048576 bytes)
            const maxBytes = maxSize * 1048576;
            
            if (file && file.size > maxBytes) {
                return false;
            }
            return true;
        }

        // File type validation
        function validateFileType(file, allowedTypes) {
            if (!file) return true;
            
            const fileName = file.name;
            const fileExt = fileName.split('.').pop().toLowerCase();
            
            return allowedTypes.indexOf(fileExt) !== -1;
        }

        // Form validation
        function validateForm() {
            let isValid = true;
            const errorMessages = [];
            
            // Validate required fields
            $form.find('[required]').each(function() {
                const $field = $(this);
                const fieldType = $field.attr('type');
                
                // Clear previous error state
                $field.removeClass('bpp-field-error');
                
                if (fieldType === 'checkbox' || fieldType === 'radio') {
                    if (!$field.is(':checked')) {
                        $field.addClass('bpp-field-error');
                        errorMessages.push(bpp_form_obj.i18n.required_field + ': ' + $field.closest('.bpp-form-field').find('label').text().replace(' *', ''));
                        isValid = false;
                    }
                } else if ($field.val() === '') {
                    $field.addClass('bpp-field-error');
                    errorMessages.push(bpp_form_obj.i18n.required_field + ': ' + $field.closest('.bpp-form-field').find('label').text().replace(' *', ''));
                    isValid = false;
                }
            });
            
            // Validate email format
            const $email = $form.find('#bpp-email');
            if ($email.length && $email.val() !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test($email.val())) {
                    $email.addClass('bpp-field-error');
                    errorMessages.push(bpp_form_obj.i18n.invalid_email);
                    isValid = false;
                }
            }
            
            // Validate resume file size and type
            const resumeInput = document.getElementById('bpp-resume');
            if (resumeInput && resumeInput.files.length > 0) {
                const resumeFile = resumeInput.files[0];
                const allowedResumeTypes = ['pdf', 'doc', 'docx'];
                
                if (!validateFileSize(resumeFile, 2)) { // 2MB max
                    $(resumeInput).addClass('bpp-field-error');
                    errorMessages.push(bpp_form_obj.i18n.file_size_error + ' (Resume)');
                    isValid = false;
                }
                
                if (!validateFileType(resumeFile, allowedResumeTypes)) {
                    $(resumeInput).addClass('bpp-field-error');
                    errorMessages.push(bpp_form_obj.i18n.file_type_error + ' (Resume)');
                    isValid = false;
                }
            }
            
            // Validate photo file size and type
            const photoInput = document.getElementById('bpp-photo');
            if (photoInput && photoInput.files.length > 0) {
                const photoFile = photoInput.files[0];
                const allowedPhotoTypes = ['jpg', 'jpeg', 'png'];
                
                if (!validateFileSize(photoFile, 1)) { // 1MB max
                    $(photoInput).addClass('bpp-field-error');
                    errorMessages.push(bpp_form_obj.i18n.file_size_error + ' (Photo)');
                    isValid = false;
                }
                
                if (!validateFileType(photoFile, allowedPhotoTypes)) {
                    $(photoInput).addClass('bpp-field-error');
                    errorMessages.push(bpp_form_obj.i18n.file_type_error + ' (Photo)');
                    isValid = false;
                }
            }
            
            // Display error messages if any
            if (!isValid) {
                $('.bpp-form-error').show().find('.bpp-error-message').html(errorMessages.join('<br>'));
            } else {
                $('.bpp-form-error').hide();
            }
            
            return isValid;
        }

        // Form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateForm()) {
                return false;
            }
            
            // Prepare form data
            const formData = new FormData(this);
            formData.append('action', 'bpp_submit_application');
            formData.append('nonce', bpp_form_obj.nonce);
            
            // Hide notices and disable submit button
            $('.bpp-form-notice').hide();
            $submitButton.prop('disabled', true).text(bpp_form_obj.i18n.submitting || 'Submitting...');
            
            // Submit form via AJAX
            $.ajax({
                url: bpp_form_obj.ajax_url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('.bpp-form-success').show();
                        $form.trigger('reset');
                        
                        // Scroll to success message
                        $('html, body').animate({
                            scrollTop: $('.bpp-form-success').offset().top - 100
                        }, 500);
                    } else {
                        $('.bpp-form-error').show().find('.bpp-error-message').text(response.message);
                    }
                },
                error: function() {
                    $('.bpp-form-error').show().find('.bpp-error-message').text(bpp_form_obj.i18n.submit_error);
                },
                complete: function() {
                    $submitButton.prop('disabled', false).text(originalButtonText);
                }
            });
            
            return false;
        });
        
        // Clear error state on input change
        $form.find('input, textarea, select').on('change input', function() {
            $(this).removeClass('bpp-field-error');
            if ($('.bpp-field-error').length === 0) {
                $('.bpp-form-error').hide();
            }
        });
    });

})(jQuery); 