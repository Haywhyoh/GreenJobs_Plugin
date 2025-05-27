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
        console.log('BPP Form initialized');
        
        // Log debug information
        if (typeof bpp_form_obj !== 'undefined') {
            console.log('Form object found with nonce:', bpp_form_obj.nonce);
            console.log('AJAX URL:', bpp_form_obj.ajax_url);
            console.log('Debug mode:', bpp_form_obj.debug ? 'Enabled' : 'Disabled');
            
            // Check for nonce field in the form
            const nonceField = document.querySelector('input[name="bpp_nonce"]');
            if (nonceField) {
                console.log('Form contains nonce field with value:', nonceField.value);
            } else {
                console.warn('No nonce field found in the form!');
            }
            
            // Check for form element
            const form = document.getElementById('bpp-submission-form');
            if (form) {
                console.log('Form element found with action:', form.getAttribute('action') || 'No action specified (will use AJAX)');
            } else {
                console.warn('Form element not found!');
            }
        } else {
            console.error('bpp_form_obj not defined! Script may not be properly localized.');
        }
        
        const $form = $('#bpp-submission-form');
        const $submitButton = $('#bpp-submit-button');
        const $formMessages = $('#bpp-form-messages');
        const $successMessage = $('#bpp-success-message');
        const $spinner = $('#bpp-submit-spinner');
        
        // Function to show field error
        function showFieldError(fieldId, message) {
            const $field = $('#' + fieldId);
            const $errorSpan = $('#' + fieldId + '_error');
            const $parent = $field.closest('.bpp-form-field');
            
            // Apply error class to the parent container, not the field
            $parent.addClass('has-error');
            // Also add the class to the field for styling
            $field.addClass('field-error');
            $errorSpan.text(message).show();
        }
        
        // Function to clear field error
        function clearFieldError(fieldId) {
            const $field = $('#' + fieldId);
            const $errorSpan = $('#' + fieldId + '_error');
            const $parent = $field.closest('.bpp-form-field');
            
            $parent.removeClass('has-error');
            $field.removeClass('field-error');
            $errorSpan.text('').hide();
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
            $form.find('.has-error').removeClass('has-error');
            $form.find('.field-error').removeClass('field-error');
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
                    showFieldError(fieldId, bpp_form_obj.i18n.required_field || 'This field is required');
                    isValid = false;
                }
            });
            
            // Validate email format
            const $email = $('#bpp_email');
            if ($email.length && $email.val() && !validateEmail($email.val())) {
                showFieldError('bpp_email', bpp_form_obj.i18n.invalid_email || 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate resume file
            const resumeInput = document.getElementById('bpp_resume');
            if (resumeInput && resumeInput.hasAttribute('required')) {
                if (!resumeInput.files.length) {
                    // No file selected, but it's required
                    showFieldError('bpp_resume', bpp_form_obj.i18n.required_field || 'Resume file is required');
                    isValid = false;
                } else {
                    const resumeFile = resumeInput.files[0];
                    
                    // Max size: 5MB
                    if (!validateFileSize(resumeFile, 5 * 1024 * 1024)) {
                        showFieldError('bpp_resume', bpp_form_obj.i18n.file_size_error || 'File is too large. Maximum size is 5MB');
                        isValid = false;
                    }
                    
                    // Allowed file types: pdf, doc, docx
                    if (!validateFileType(resumeFile, ['pdf', 'doc', 'docx'])) {
                        showFieldError('bpp_resume', bpp_form_obj.i18n.file_type_error || 'Invalid file format. Please use PDF, DOC, or DOCX');
                        isValid = false;
                    }
                }
            }
            
            // Validate photo file
            const photoInput = document.getElementById('bpp_photo');
            if (photoInput && photoInput.hasAttribute('required')) {
                if (!photoInput.files.length) {
                    // No file selected, but it's required
                    showFieldError('bpp_photo', bpp_form_obj.i18n.required_field || 'Professional photo is required');
                    isValid = false;
                } else {
                    const photoFile = photoInput.files[0];
                    
                    // Max size: 2MB
                    if (!validateFileSize(photoFile, 2 * 1024 * 1024)) {
                        showFieldError('bpp_photo', bpp_form_obj.i18n.file_size_error || 'Photo file is too large. Maximum size is 2MB');
                        isValid = false;
                    }
                    
                    // Allowed file types: jpg, jpeg, png, gif
                    if (!validateFileType(photoFile, ['jpg', 'jpeg', 'png', 'gif'])) {
                        showFieldError('bpp_photo', bpp_form_obj.i18n.file_type_error || 'Invalid photo format. Please use JPG, PNG or GIF');
                        isValid = false;
                    }
                }
            }
            
            return isValid;
        }
        
        // Validate email format
        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }
        
        // Function to prepare form data before submission
        function prepareFormData(formData) {
            console.log('Preparing form data for submission...');
            
            // Debug: Log all form fields
            const formEntries = {};
            for (let [key, value] of formData.entries()) {
                formEntries[key] = value;
                console.log(`Form field: ${key} = ${value}`);
            }
            
            // Handle years_experience field - using the correct ID from the form
            const yearsExpElement = document.getElementById('years_experience');
            if (yearsExpElement) {
                const yearsExpValue = yearsExpElement.value.trim();
                if (yearsExpValue !== '') {
                    // Make sure it's stored as a number
                    formData.set('years_experience', parseInt(yearsExpValue, 10));
                    console.log(`Set years_experience to: ${parseInt(yearsExpValue, 10)}`);
                }
            }
            
            // Handle industry field
            const industryElement = document.getElementById('bpp_industry');
            if (industryElement && industryElement.value) {
                formData.set('industry', industryElement.value);
                console.log(`Set industry to: ${industryElement.value}`);
            }
            
            // Handle professional photo
            const professionalPhotoInput = document.getElementById('bpp_photo');
            if (professionalPhotoInput && professionalPhotoInput.files.length > 0) {
                // Make sure to explicitly add the file to FormData with correct name
                formData.set('professional_photo', professionalPhotoInput.files[0]);
                console.log(`Set professional_photo file: ${professionalPhotoInput.files[0].name}`);
            }
            
            // Handle bio/cover_letter field
            const bioElement = document.getElementById('bpp_bio');
            if (bioElement && bioElement.value) {
                // Set the bio field normally
                formData.set('bio', bioElement.value);
                console.log(`Set bio to length: ${bioElement.value.length} characters`);
            }
            
            return formData;
        }
        
        // Display message
        function displayMessage(message, isSuccess) {
            $formMessages.removeClass('bpp-success bpp-error')
                .addClass(isSuccess ? 'bpp-success' : 'bpp-error')
                .html(message)
                .show();
            
            // Scroll to the message
            $('html, body').animate({
                scrollTop: $formMessages.offset().top - 100
            }, 500);
        }
        
        // Handle form submission via AJAX
        $form.on('submit', function(e) {
            e.preventDefault();
            
            console.log('Form submission started');
            
            // Validate form
            if (!validateForm()) {
                // Find the first error field
                const $firstError = $form.find('.has-error').first();
                if ($firstError.length) {
                    // Scroll to the first error
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 500);
                }
                
                // Show general error message
                displayMessage(bpp_form_obj.i18n.form_has_errors || 'Please correct the errors in the form before submitting.', false);
                
                return false;
            }
            
            // Show loading spinner
            $submitButton.prop('disabled', true);
            $spinner.show();
            
            // Prepare form data
            const formData = new FormData(this);
            formData.append('action', 'bpp_submit_application');
            
            // Add the nonce from the localized script object
            console.log('Adding nonce from script object:', bpp_form_obj.nonce);
            formData.append('nonce', bpp_form_obj.nonce);
            
            // Also try to get the nonce from the form field if it exists
            const bppNonceField = document.querySelector('input[name="bpp_nonce"]');
            if (bppNonceField) {
                console.log('Found bpp_nonce field in form, value:', bppNonceField.value);
                formData.append('bpp_nonce', bppNonceField.value);
            } else {
                console.log('No bpp_nonce field found in form');
            }
            
            // Log all form fields for debugging
            console.log('Form data being submitted:');
            for (let [key, value] of formData.entries()) {
                if (typeof value === 'string') {
                    console.log(`${key}: ${value}`);
                } else {
                    console.log(`${key}: [File or binary data]`);
                }
            }
            
            // Additional form data preparation
            const preparedFormData = prepareFormData(formData);
            
            // Submit form via AJAX
            console.log('Submitting form data to:', bpp_form_obj.ajax_url);
            $.ajax({
                type: 'POST',
                url: bpp_form_obj.ajax_url,
                data: preparedFormData,
                contentType: false,
                processData: false,
                success: function(response) {
                    // Hide spinner
                    $submitButton.prop('disabled', false);
                    $spinner.hide();
                    
                    console.log('AJAX Response:', response);
                    
                    if (response.success) {
                        // Hide form and show success message
                        $form.hide();
                        $successMessage.show();
                        
                        // Scroll to success message
                        $('html, body').animate({
                            scrollTop: $successMessage.offset().top - 100
                        }, 500);
                    } else {
                        // Show error message
                        const errorMessage = response.message || response.data || bpp_form_obj.i18n.submit_error || 'An error occurred. Please try again.';
                        console.error('Form submission error:', errorMessage);
                        displayMessage(errorMessage, false);
                    }
                },
                error: function(xhr, status, error) {
                    // Hide spinner
                    $submitButton.prop('disabled', false);
                    $spinner.hide();
                    
                    // Show error message
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status,
                        statusText: xhr.statusText
                    });
                    
                    // Provide more details in the console
                    try {
                        console.log('Request details:', {
                            url: bpp_form_obj.ajax_url,
                            method: 'POST',
                            nonceValue: bpp_form_obj.nonce,
                            headers: xhr.getAllResponseHeaders()
                        });
                    } catch (e) {
                        console.error('Error logging request details:', e);
                    }
                    
                    let errorMessage = bpp_form_obj.i18n.submit_error || 'An error occurred. Please try again.';
                    
                    // Try to parse the response if it's JSON
                    try {
                        const jsonResponse = JSON.parse(xhr.responseText);
                        if (jsonResponse && (jsonResponse.message || jsonResponse.data)) {
                            errorMessage = jsonResponse.message || jsonResponse.data;
                        }
                    } catch (e) {
                        console.log('Error parsing JSON response:', e);
                    }
                    
                    displayMessage(errorMessage, false);
                }
            });
            
            return false;
        });
        
        // Clear field errors on input
        $form.find('input, textarea, select').on('input change', function() {
            clearFieldError($(this).attr('id'));
        });
    });
})(jQuery); 