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

    // Get form reference
    const $form = $('#bpp-submission-form');
    const $submitButton = $('#bpp-submit-button');
    const $formMessages = $('#bpp-form-messages');
    const originalButtonText = $submitButton.text();

    // Function to validate file size
    function validateFileSize(file, maxSize) {
        if (!file) return true;
        return file.size <= maxSize;
    }

    // Function to validate file type
    function validateFileType(file, allowedTypes) {
        if (!file) return true;
        return allowedTypes.includes(file.type);
    }

    // Function to display error message for a field
    function showFieldError(fieldId, message) {
        const $field = $('#' + fieldId);
        const $errorSpan = $('#' + fieldId + '_error');
        
        $field.addClass('bpp-error');
        if ($errorSpan.length) {
            $errorSpan.text(message).show();
        } else {
            $field.after('<span id="' + fieldId + '_error" class="bpp-error-message">' + message + '</span>');
        }
    }

    // Function to clear error message for a field
    function clearFieldError(fieldId) {
        const $field = $('#' + fieldId);
        const $errorSpan = $('#' + fieldId + '_error');
        
        $field.removeClass('bpp-error');
        if ($errorSpan.length) {
            $errorSpan.hide();
        }
    }

    // Function to validate the form before submission
    function validateForm() {
        let isValid = true;
        
        // Clear previous error messages
        $('.bpp-error-message').hide();
        $('.bpp-error').removeClass('bpp-error');
        
        // Validate required fields
        $form.find('[required]').each(function() {
            const $field = $(this);
            const fieldId = $field.attr('id');
            
            if (!$field.val().trim()) {
                showFieldError(fieldId, bpp_form_obj.i18n.required_field);
                isValid = false;
            } else {
                clearFieldError(fieldId);
            }
        });
        
        // Validate email format
        const $email = $('#bpp_email');
        if ($email.length && $email.val().trim()) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test($email.val().trim())) {
                showFieldError('bpp_email', bpp_form_obj.i18n.invalid_email);
                isValid = false;
            }
        }
        
        // Validate resume file
        const resumeInput = document.getElementById('bpp_resume');
        if (resumeInput && resumeInput.files.length > 0) {
            const resumeFile = resumeInput.files[0];
            
            // Check resume size (5MB max)
            if (!validateFileSize(resumeFile, 5 * 1024 * 1024)) {
                showFieldError('bpp_resume', bpp_form_obj.i18n.file_size_error);
                isValid = false;
            }
            
            // Check resume file type
            const allowedResumeTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!validateFileType(resumeFile, allowedResumeTypes)) {
                showFieldError('bpp_resume', bpp_form_obj.i18n.file_type_error);
                isValid = false;
            }
        }
        
        // Validate photo file
        const photoInput = document.getElementById('bpp_photo');
        if (photoInput && photoInput.files.length > 0) {
            const photoFile = photoInput.files[0];
            
            // Check photo size (2MB max)
            if (!validateFileSize(photoFile, 2 * 1024 * 1024)) {
                showFieldError('bpp_photo', bpp_form_obj.i18n.file_size_error);
                isValid = false;
            }
            
            // Check photo file type
            const allowedPhotoTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validateFileType(photoFile, allowedPhotoTypes)) {
                showFieldError('bpp_photo', bpp_form_obj.i18n.file_type_error);
                isValid = false;
            }
        }
        
        return isValid;
    }

    // Handle form submission
    $form.on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return false;
        }
        
        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true).addClass('bpp-submitting');
        
        // Show loading state
        $formMessages.removeClass('bpp-error bpp-success').text('Processing...').show();
        
        // Create FormData object
        const formData = new FormData(this);
        
        // Add action and nonce
        formData.append('action', 'bpp_submit_application');
        formData.append('nonce', bpp_form_obj.nonce);
        
        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: bpp_form_obj.ajax_url,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Re-enable submit button
                $submitButton.prop('disabled', false).removeClass('bpp-submitting');
                
                if (response.success) {
                    // Show success message
                    $formMessages.removeClass('bpp-error').addClass('bpp-success').html(response.message).show();
                    
                    // Reset form
                    $form[0].reset();
                    
                    // Optionally hide the form to emphasize success
                    $form.hide();
                    
                    // Scroll to message
                    $('html, body').animate({
                        scrollTop: $formMessages.offset().top - 100
                    }, 500);
                } else {
                    // Show error message
                    $formMessages.removeClass('bpp-success').addClass('bpp-error').html(response.message).show();
                    
                    // Show field-specific errors if available
                    if (response.errors) {
                        $.each(response.errors, function(fieldId, errorMessage) {
                            showFieldError(fieldId, errorMessage);
                        });
                    }
                    
                    // Scroll to first error
                    const $firstError = $('.bpp-error:first');
                    if ($firstError.length) {
                        $('html, body').animate({
                            scrollTop: $firstError.offset().top - 100
                        }, 500);
                    } else {
                        $('html, body').animate({
                            scrollTop: $formMessages.offset().top - 100
                        }, 500);
                    }
                }
            },
            error: function() {
                // Re-enable submit button
                $submitButton.prop('disabled', false).removeClass('bpp-submitting');
                
                // Show error message
                $formMessages.removeClass('bpp-success').addClass('bpp-error').html(bpp_form_obj.i18n.submit_error).show();
                
                // Scroll to message
                $('html, body').animate({
                    scrollTop: $formMessages.offset().top - 100
                }, 500);
            }
        });
        
        return false;
    });

    // Clear error state on input change
    $form.find('input, textarea, select').on('change input', function() {
        $(this).removeClass('bpp-field-error');
        $('#' + $(this).attr('id') + '_error').text('');
    });

})(jQuery); 