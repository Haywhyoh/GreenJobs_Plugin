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

    $(document).ready(function() {
        const $form = $('#bpp-submission-form');
        const $submitButton = $('#bpp-submit-button');
        const $formMessages = $('#bpp-form-messages');
        const $successMessage = $('#bpp-success-message');
        const $spinner = $('#bpp-submit-spinner');
        const isBootstrapForm = $form.hasClass('needs-validation');

        // Function to show field error
        function showFieldError(fieldId, message) {
            const $field = $('#' + fieldId);
            const $errorSpan = $('#' + fieldId + '_error');
            
            if (isBootstrapForm) {
                $field.addClass('is-invalid');
                $errorSpan.text(message).show();
            } else {
                $field.addClass('bpp-field-error');
                $errorSpan.text(message).show();
            }
        }
        
        // Function to clear field error
        function clearFieldError(fieldId) {
            const $field = $('#' + fieldId);
            const $errorSpan = $('#' + fieldId + '_error');
            
            if (isBootstrapForm) {
                $field.removeClass('is-invalid');
                $errorSpan.text('').hide();
            } else {
                $field.removeClass('bpp-field-error');
                $errorSpan.text('').hide();
            }
        }

        // Validate file size
        function validateFileSize(file, maxSize) {
            if (file && file.size > maxSize) {
                return false;
            }
            return true;
        }

        // Validate file type
        function validateFileType(file, allowedTypes) {
            if (!file) {
                return true;
            }
            
            // Get file extension and convert to lowercase
            const extension = file.name.split('.').pop().toLowerCase();
            
            // Check if file type is allowed
            return allowedTypes.indexOf(extension) !== -1;
        }

        // Validate form
        function validateForm() {
            let isValid = true;
            
            // Clear previous errors
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.bpp-field-error').removeClass('bpp-field-error');
            $form.find('[id$="_error"]').text('').hide();
            
            // Validate required fields
            $form.find('[required]').each(function() {
                const $field = $(this);
                const fieldId = $field.attr('id');
                const fieldType = $field.attr('type');
                
                // Skip file inputs (handled separately)
                if (fieldType === 'file') {
                    return;
                }
                
                // Validate based on field type
                if ((fieldType === 'checkbox' && !$field.is(':checked')) || 
                    (fieldType !== 'checkbox' && !$field.val())) {
                    showFieldError(fieldId, bpp_form_obj.i18n.required_field);
                    isValid = false;
                }
            });
            
            // Validate email format
            const $email = $('#bpp_email');
            if ($email.length && $email.val() && !validateEmail($email.val())) {
                showFieldError('bpp_email', bpp_form_obj.i18n.invalid_email);
                isValid = false;
            }
            
            // Validate resume file
            const resumeInput = document.getElementById('bpp_resume');
            if (resumeInput && resumeInput.hasAttribute('required') && resumeInput.files.length) {
                const resumeFile = resumeInput.files[0];
                
                // Max size: 5MB
                if (!validateFileSize(resumeFile, 5 * 1024 * 1024)) {
                    showFieldError('bpp_resume', bpp_form_obj.i18n.file_size_error);
                    isValid = false;
                }
                
                // Allowed file types: pdf, doc, docx
                if (!validateFileType(resumeFile, ['pdf', 'doc', 'docx'])) {
                    showFieldError('bpp_resume', bpp_form_obj.i18n.file_type_error);
                    isValid = false;
                }
            }
            
            // Validate photo file
            const photoInput = document.getElementById('bpp_photo');
            if (photoInput && photoInput.hasAttribute('required') && photoInput.files.length) {
                const photoFile = photoInput.files[0];
                
                // Max size: 2MB
                if (!validateFileSize(photoFile, 2 * 1024 * 1024)) {
                    showFieldError('bpp_photo', bpp_form_obj.i18n.file_size_error);
                    isValid = false;
                }
                
                // Allowed file types: jpg, jpeg, png, gif
                if (!validateFileType(photoFile, ['jpg', 'jpeg', 'png', 'gif'])) {
                    showFieldError('bpp_photo', bpp_form_obj.i18n.file_type_error);
                    isValid = false;
                }
            }
            
            return isValid;
        }
        
        // Validate email format
        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }
        
        // Display message
        function displayMessage(message, isSuccess) {
            if (isBootstrapForm) {
                $formMessages.removeClass('d-none alert-success alert-danger')
                    .addClass(isSuccess ? 'alert alert-success' : 'alert alert-danger')
                    .html(message)
                    .show();
            } else {
                $formMessages.removeClass('bpp-success bpp-error')
                    .addClass(isSuccess ? 'bpp-success' : 'bpp-error')
                    .html(message)
                    .show();
            }
        }

        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateForm()) {
                return false;
            }
            
            // Disable submit button and show spinner
            $submitButton.prop('disabled', true);
            $spinner.show();
            
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
                    // Re-enable submit button and hide spinner
                    $submitButton.prop('disabled', false);
                    $spinner.hide();
                    
                    if (response.success) {
                        // Show success message
                        displayMessage(response.message, true);
                        
                        // Reset form
                        $form[0].reset();
                        
                        // Hide form and show success message
                        $form.hide();
                        $successMessage.show();
                        
                        // Scroll to success message
                        $('html, body').animate({
                            scrollTop: $successMessage.offset().top - 100
                        }, 500);
                    } else {
                        // Show error message
                        displayMessage(response.message, false);
                        
                        // Show field-specific errors if available
                        if (response.errors) {
                            $.each(response.errors, function(fieldId, errorMessage) {
                                showFieldError(fieldId, errorMessage);
                            });
                        }
                        
                        // Scroll to error message
                        $('html, body').animate({
                            scrollTop: $formMessages.offset().top - 100
                        }, 500);
                    }
                },
                error: function() {
                    // Re-enable submit button and hide spinner
                    $submitButton.prop('disabled', false);
                    $spinner.hide();
                    
                    // Show error message
                    displayMessage(bpp_form_obj.i18n.submit_error, false);
                    
                    // Scroll to error message
                    $('html, body').animate({
                        scrollTop: $formMessages.offset().top - 100
                    }, 500);
                }
            });
            
            return false;
        });

        // Clear error state on input change
        $form.find('input, textarea, select').on('change input', function() {
            clearFieldError($(this).attr('id'));
        });
    });
})(jQuery); 