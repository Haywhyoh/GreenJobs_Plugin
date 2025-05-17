<?php
/**
 * Handle form submissions for the Black Potential Pipeline plugin.
 *
 * This class processes form submissions, validates data, and creates applicant entries.
 *
 * @link       https://codemygig.com,
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The form handler class.
 *
 * This class handles all form submissions for the Black Potential Pipeline plugin,
 * including validation, file uploads, and database operations.
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 * @author     Adedayo Ayomide Samue ayomide@codemygig.com
 */
class BPP_Form_Handler {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Register AJAX handlers
        add_action('wp_ajax_bpp_submit_application', array($this, 'process_application_submission'));
        add_action('wp_ajax_nopriv_bpp_submit_application', array($this, 'process_application_submission'));
    }

    /**
     * Process application form submission.
     *
     * @since    1.0.0
     */
    public function process_application_submission() {
        try {
            // Debug information
            error_log('BPP Form Handler: Processing application submission');
            error_log('$_FILES contents: ' . print_r($_FILES, true));
            error_log('$_POST contents: ' . print_r($_POST, true));
            
            // Check nonce for security
            if (!check_ajax_referer('bpp_form_nonce', 'nonce', false)) {
                $this->send_error_response(__('Security check failed. Please refresh the page and try again.', 'black-potential-pipeline'));
                return;
            }

            // Get form fields configuration
            $form_fields = get_option('bpp_form_fields', array());
            
            // Determine required fields
            $required_fields = array();
            foreach ($form_fields as $field_id => $field) {
                if (isset($field['required']) && $field['required'] && isset($field['enabled']) && $field['enabled']) {
                    $required_fields[] = $field_id;
                }
            }
            
            // If no configuration is found, use default required fields
            if (empty($required_fields)) {
                $required_fields = array('first_name', 'last_name', 'email', 'industry', 'cover_letter', 'resume');
            }

            // Validate required fields (except file uploads)
            foreach ($required_fields as $field) {
                // Skip file fields - we'll check them separately
                if ($field === 'resume' || $field === 'photo') {
                    continue;
                }
                
                if (empty($_POST[$field])) {
                    $field_label = isset($form_fields[$field]['label']) ? $form_fields[$field]['label'] : $field;
                    $this->send_error_response(sprintf(__('Missing required field: %s', 'black-potential-pipeline'), $field_label));
                    return;
                }
            }
            
            // Special validation for resume file upload
            if (in_array('resume', $required_fields)) {
                error_log('Checking resume file upload...');
                
                // More detailed check of $_FILES
                if (!isset($_FILES['resume'])) {
                    error_log('$_FILES["resume"] is not set at all');
                    $this->send_error_response(__('Resume file is missing. Please select a file.', 'black-potential-pipeline'));
                    return;
                }
                
                error_log('Resume upload error code: ' . $_FILES['resume']['error']);
                
                if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
                    $error_message = $this->get_file_upload_error_message($_FILES['resume']['error']);
                    error_log('Resume upload error: ' . $error_message);
                    $this->send_error_response(sprintf(__('Resume upload error: %s', 'black-potential-pipeline'), $error_message));
                    return;
                }
                
                if (empty($_FILES['resume']['tmp_name']) || !is_uploaded_file($_FILES['resume']['tmp_name'])) {
                    error_log('Resume tmp_name is empty or not an uploaded file');
                    $this->send_error_response(__('Resume file upload failed. Please try again.', 'black-potential-pipeline'));
                    return;
                }
                
                error_log('Resume file upload validation passed');
            }

            // Process file uploads
            error_log('Starting to process file uploads');
            
            // Check if WordPress upload functions are available
            if (!function_exists('wp_handle_upload')) {
                error_log('Loading wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            if (!function_exists('wp_generate_attachment_metadata')) {
                error_log('Loading wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
            }
            if (!function_exists('wp_insert_attachment')) {
                error_log('Loading wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
            }
            
            // Check upload directory
            $upload_dir = wp_upload_dir();
            error_log('Upload directory: ' . print_r($upload_dir, true));
            
            if ($upload_dir['error']) {
                error_log('Upload directory error: ' . $upload_dir['error']);
                $this->send_error_response(__('Upload directory error: ' . $upload_dir['error'], 'black-potential-pipeline'));
                return;
            }
            
            // Process resume file directly
            if (!empty($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                error_log('Processing resume file directly: ' . $_FILES['resume']['name']);
                
                // Check file type
                $file_type = wp_check_filetype(basename($_FILES['resume']['name']));
                error_log('Resume file type: ' . print_r($file_type, true));
                
                if (empty($file_type['type'])) {
                    error_log('Invalid resume file type');
                    $this->send_error_response(__('Invalid file type. Please upload a PDF, DOC, or DOCX file.', 'black-potential-pipeline'));
                    return;
                }
                
                // Upload the file
                $upload_overrides = array('test_form' => false);
                error_log('Calling wp_handle_upload for resume');
                $uploaded_file = wp_handle_upload($_FILES['resume'], $upload_overrides);
                
                if (isset($uploaded_file['error'])) {
                    error_log('Resume upload error from wp_handle_upload: ' . $uploaded_file['error']);
                    $this->send_error_response(__('Resume upload error: ' . $uploaded_file['error'], 'black-potential-pipeline'));
                    return;
                }
                
                error_log('Resume uploaded successfully: ' . print_r($uploaded_file, true));
                
                // Create attachment
                $attachment = array(
                    'guid' => $uploaded_file['url'],
                    'post_mime_type' => $uploaded_file['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($_FILES['resume']['name'])),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                
                error_log('Creating resume attachment with data: ' . print_r($attachment, true));
                $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);
                
                if (is_wp_error($attachment_id)) {
                    error_log('Error creating resume attachment: ' . $attachment_id->get_error_message());
                    $this->send_error_response(__('Failed to create resume attachment: ' . $attachment_id->get_error_message(), 'black-potential-pipeline'));
                    return;
                }
                
                error_log('Resume attachment created with ID: ' . $attachment_id);
                
                // Generate metadata
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
                
                $resume_id = $attachment_id;
                error_log('Resume processed successfully with ID: ' . $resume_id);
            } else {
                error_log('No valid resume file found for processing');
                $this->send_error_response(__('Failed to upload resume. Please try again.', 'black-potential-pipeline'));
                return;
            }
            
            // Process photo file if provided
            $photo_id = null;
            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                error_log('Processing photo file directly: ' . $_FILES['photo']['name']);
                
                // Similar processing for photo...
                // (code omitted for brevity)
            }
            
            // Create applicant post
            error_log('Creating applicant post');
            
            // Prepare post data
            $post_data = array(
                'post_title'    => sanitize_text_field($_POST['first_name'] . ' ' . $_POST['last_name']),
                'post_content'  => sanitize_textarea_field($_POST['cover_letter']),
                'post_status'   => 'draft',
                'post_type'     => 'bpp_applicant',
            );
            
            error_log('Applicant post data: ' . print_r($post_data, true));
            $applicant_id = wp_insert_post($post_data);
            
            if (is_wp_error($applicant_id)) {
                error_log('Error creating applicant post: ' . $applicant_id->get_error_message());
                $this->send_error_response(__('Failed to create applicant record: ' . $applicant_id->get_error_message(), 'black-potential-pipeline'));
                return;
            }
            
            if (!$applicant_id) {
                error_log('Failed to create applicant post (no ID returned)');
                $this->send_error_response(__('Failed to create applicant record.', 'black-potential-pipeline'));
                return;
            }
            
            error_log('Applicant post created successfully with ID: ' . $applicant_id);
            
            // Save form fields as post meta
            $meta_fields = array(
                'first_name', 'last_name', 'email', 'phone', 'job_title', 
                'industry', 'years_experience', 'skills', 'website', 'linkedin'
            );
            
            foreach ($meta_fields as $field) {
                if (isset($_POST[$field])) {
                    update_post_meta($applicant_id, 'bpp_' . $field, sanitize_text_field($_POST[$field]));
                }
            }
            
            // Save resume and photo IDs
            if ($resume_id) {
                update_post_meta($applicant_id, 'bpp_resume', $resume_id);
            }
            
            if ($photo_id) {
                update_post_meta($applicant_id, 'bpp_photo', $photo_id);
            }
            
            error_log('Applicant meta data saved');
            
            // Send notification emails
            try {
                $this->send_notification_emails($applicant_id);
                error_log('Notification emails sent successfully');
            } catch (Exception $e) {
                error_log('Error sending notification emails: ' . $e->getMessage());
                // Continue with the form submission process even if emails fail
            }
            
            // Success response
            $response = array(
                'success' => true,
                'message' => __('Thank you! Your application has been submitted successfully and is pending review.', 'black-potential-pipeline')
            );
            
            error_log('Sending success response: ' . print_r($response, true));
            wp_send_json($response);
            
        } catch (Exception $e) {
            error_log('Exception in process_application_submission: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
            $this->send_error_response(__('An unexpected error occurred: ' . $e->getMessage(), 'black-potential-pipeline'));
        }
    }

    /**
     * Handle file uploads for resume and photo.
     *
     * @since    1.0.0
     * @param    int       $applicant_id    The applicant post ID.
     * @param    string    $file_key        The file key in $_FILES array.
     * @param    array     $options         Options for file handling.
     * @return   bool                       True if upload was successful, false otherwise.
     */
    private function handle_file_upload($applicant_id, $file_key, $options = array()) {
        // Default options
        $defaults = array(
            'allowed_types' => array(),
            'max_size' => 0,
            'meta_key' => '',
            'is_featured' => false
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // Check if the upload is valid
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Verify file type
        $file_type = wp_check_filetype($_FILES[$file_key]['name']);
        if (!empty($options['allowed_types']) && !in_array($file_type['type'], $options['allowed_types'])) {
            return false;
        }
        
        // Verify file size
        if ($options['max_size'] > 0 && $_FILES[$file_key]['size'] > $options['max_size']) {
            return false;
        }

        $upload_overrides = array(
            'test_form' => false,
            'mimes' => array_combine(
                array_map(function($mime) { return '.' . explode('/', $mime)[1]; }, $options['allowed_types']),
                $options['allowed_types']
            )
        );
        
        $file = wp_handle_upload($_FILES[$file_key], $upload_overrides);

        if (isset($file['error'])) {
            return false;
        }

        // Create an attachment
        $filename = $_FILES[$file_key]['name'];
        
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment, $file['file'], $applicant_id);

        if (!is_wp_error($attachment_id)) {
            // Update metadata for the attachment
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            // Save the attachment ID as post meta
            if (!empty($options['meta_key'])) {
                update_post_meta($applicant_id, $options['meta_key'], $attachment_id);
            }
            
            // Set as featured image if specified
            if ($options['is_featured']) {
                set_post_thumbnail($applicant_id, $attachment_id);
            }
            
            return true;
        }

        return false;
    }

    /**
     * Send notification email to admin about new application.
     *
     * @since    1.0.0
     * @param    int       $applicant_id    The applicant post ID.
     */
    private function send_admin_notification($applicant_id) {
        $options = get_option('bpp_email_notifications', array());
        
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        $admin_email = isset($options['admin_email']) ? $options['admin_email'] : get_option('admin_email');
        $applicant = get_post($applicant_id);
        
        if (!$applicant) {
            return;
        }
        
        $email = get_post_meta($applicant_id, 'bpp_email', true);
        $industry_terms = wp_get_post_terms($applicant_id, 'bpp_industry', array('fields' => 'names'));
        $industry = '';
        if (!is_wp_error($industry_terms) && !empty($industry_terms)) {
            $industry = $industry_terms[0];
        }
        $job_title = get_post_meta($applicant_id, 'bpp_job_title', true);
        $years_experience = get_post_meta($applicant_id, 'bpp_years_experience', true);
        
        $subject = sprintf(__('New Black Potential Pipeline Application: %s', 'black-potential-pipeline'), $applicant->post_title);
        
        $message = sprintf(
            __('A new application has been submitted to the Black Potential Pipeline:

Name: %s
Email: %s
Industry: %s
Job Title: %s
Years of Experience: %s

To review this application, please visit the admin dashboard:
%s

Thank you,
Black Potential Pipeline Plugin', 'black-potential-pipeline'),
            $applicant->post_title,
            $email,
            $industry,
            $job_title,
            $years_experience,
            admin_url('admin.php?page=bpp-new-applications')
        );
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($admin_email, $subject, nl2br($message), $headers);
    }

    /**
     * Send confirmation email to applicant.
     *
     * @since    1.0.0
     * @param    int       $applicant_id    The applicant post ID.
     */
    private function send_applicant_confirmation($applicant_id) {
        $options = get_option('bpp_email_notifications', array());
        
        if (!isset($options['enabled']) || !$options['enabled'] || !isset($options['applicant_notify']) || !$options['applicant_notify']) {
            return;
        }
        
        $email = get_post_meta($applicant_id, 'bpp_email', true);
        
        if (!$email || !is_email($email)) {
            return;
        }
        
        $applicant = get_post($applicant_id);
        if (!$applicant) {
            return;
        }
        
        $first_name = explode(' ', $applicant->post_title)[0];
        
        $subject = __('Thank You for Your Black Potential Pipeline Application', 'black-potential-pipeline');
        
        $message = sprintf(
            __('Hello %s,

Thank you for submitting your application to the Black Potential Pipeline. We have received your information and will review it shortly.

Your application is currently under review by our team. Once approved, your profile will be visible in our directory of Black professionals in green industries.

If you have any questions or need to update your information, please contact us.

Best regards,
The Black Potential Pipeline Team', 'black-potential-pipeline'),
            $first_name
        );
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, nl2br($message), $headers);
    }

    /**
     * Send error response
     *
     * @since    1.0.0
     * @param    string    $message    The error message
     * @return   void
     */
    private function send_error_response($message) {
        $response = array(
            'success' => false,
            'message' => $message  // Put message directly in the response
        );
        
        error_log('Sending error response: ' . print_r($response, true));
        wp_send_json($response);
        exit;
    }

    /**
     * Send success response as JSON.
     *
     * @since    1.0.0
     * @param    array     $data    Response data.
     */
    private function send_success_response($data) {
        wp_send_json_success($data);
    }

    /**
     * Process application form submission
     *
     * @since    1.0.0
     * @return   void
     */
    public function handle_application_submission() {
        // Check nonce for security
        check_ajax_referer('bpp_form_nonce', 'nonce');
        
        // Initialize response array
        $response = array(
            'success' => false,
            'message' => '',
            'errors' => array()
        );
        
        // Validate fields
        $errors = $this->validate_submission_fields($_POST);
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = __('Please correct the errors below.', 'black-potential-pipeline');
            wp_send_json($response);
            return;
        }
        
        // Process file uploads
        $uploaded_files = $this->process_file_uploads($_FILES);
        
        if (isset($uploaded_files['errors']) && !empty($uploaded_files['errors'])) {
            $response['errors'] = $uploaded_files['errors'];
            $response['message'] = __('There were issues with your file uploads.', 'black-potential-pipeline');
            wp_send_json($response);
            return;
        }
        
        // Create applicant post
        $applicant_id = $this->create_applicant_post($_POST, $uploaded_files);
        
        if (!$applicant_id) {
            $response['message'] = __('Failed to submit your application. Please try again.', 'black-potential-pipeline');
            wp_send_json($response);
            return;
        }
        
        // Send notification emails
        $this->send_notification_emails($applicant_id);
        
        // Success response
        $response['success'] = true;
        $response['message'] = __('Thank you! Your application has been submitted successfully and is pending review.', 'black-potential-pipeline');
        
        wp_send_json($response);
    }

    /**
     * Send notification emails after application submission
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant post ID
     * @return   void
     */
    private function send_notification_emails($applicant_id) {
        try {
            // Initialize email manager
            $email_manager = new BPP_Email_Manager($this->plugin_name, $this->version);
            
            // Send confirmation to applicant
            error_log('Sending application confirmation email');
            $email_manager->send_application_confirmation($applicant_id);
            
            // Send notification to admin
            error_log('Sending admin notification email');
            $email_manager->send_admin_notification($applicant_id);
            
            error_log('Email notifications sent successfully');
        } catch (Exception $e) {
            error_log('Error sending notification emails: ' . $e->getMessage());
            // Continue with the form submission process even if emails fail
        }
    }

    /**
     * Validate the submission fields
     *
     * @since    1.0.0
     * @param    array    $data    The submission data
     * @return   array             Array of errors
     */
    private function validate_submission_fields($data) {
        $errors = array();
        
        // Get form fields configuration
        $form_fields = get_option('bpp_form_fields', array());
        
        // Determine required fields
        $required_fields = array();
        foreach ($form_fields as $field_id => $field) {
            if (isset($field['required']) && $field['required'] && isset($field['enabled']) && $field['enabled']) {
                $required_fields[] = $field_id;
            }
        }
        
        // If no configuration is found, use default required fields
        if (empty($required_fields)) {
            $required_fields = array('first_name', 'last_name', 'email', 'industry', 'cover_letter', 'resume');
        }
        
        // Validate required fields
        foreach ($required_fields as $field) {
            if ($field !== 'resume' && $field !== 'photo' && empty($data[$field])) {
                $field_label = isset($form_fields[$field]['label']) ? $form_fields[$field]['label'] : $field;
                $errors[$field] = sprintf(__('The %s field is required.', 'black-potential-pipeline'), $field_label);
            }
        }
        
        // Validate file uploads separately (they're in $_FILES not $_POST)
        if (in_array('resume', $required_fields)) {
            // Error only if no file was uploaded at all
            if (empty($_FILES['resume']) || 
                !isset($_FILES['resume']['tmp_name']) || 
                empty($_FILES['resume']['tmp_name'])) {
                $errors['resume'] = __('Resume file is required.', 'black-potential-pipeline');
            } 
            // If there was an error other than no file uploaded
            elseif ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
                $errors['resume'] = $this->get_file_upload_error_message($_FILES['resume']['error']);
            }
        }
        
        if (in_array('photo', $required_fields)) {
            // Error only if no file was uploaded at all
            if (empty($_FILES['photo']) || 
                !isset($_FILES['photo']['tmp_name']) || 
                empty($_FILES['photo']['tmp_name'])) {
                $errors['photo'] = __('Photo is required.', 'black-potential-pipeline');
            }
            // If there was an error other than no file uploaded
            elseif ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $errors['photo'] = $this->get_file_upload_error_message($_FILES['photo']['error']);
            }
        }
        
        // Validate email format
        if (!empty($data['email']) && !is_email($data['email'])) {
            $errors['email'] = __('Please provide a valid email address.', 'black-potential-pipeline');
        }
        
        // Check if email already exists
        if (!empty($data['email']) && !isset($errors['email'])) {
            $existing_email_query = new WP_Query(array(
                'post_type' => 'bpp_applicant',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'bpp_email',
                        'value' => $data['email'],
                        'compare' => '='
                    )
                )
            ));
            
            if ($existing_email_query->have_posts()) {
                $errors['email'] = __('An application with this email address already exists.', 'black-potential-pipeline');
            }
        }
        
        return $errors;
    }

    /**
     * Create a new applicant post with the submitted data
     *
     * @since    1.0.0
     * @param    array    $data           The submission form data
     * @param    array    $uploaded_files Array of uploaded files
     * @return   int|bool                 The post ID on success, false on failure
     */
    private function create_applicant_post($data, $uploaded_files = array()) {
        // Sanitize input data
        $first_name = sanitize_text_field($data['first_name']);
        $last_name = sanitize_text_field($data['last_name']);
        $email = sanitize_email($data['email']);
        $phone = isset($data['phone']) ? sanitize_text_field($data['phone']) : '';
        $industry = isset($data['industry']) ? sanitize_text_field($data['industry']) : '';
        $job_title = isset($data['job_title']) ? sanitize_text_field($data['job_title']) : '';
        $years_experience = isset($data['years_experience']) ? sanitize_text_field($data['years_experience']) : '';
        $linkedin = isset($data['linkedin']) ? esc_url_raw($data['linkedin']) : '';
        $website = isset($data['website']) ? esc_url_raw($data['website']) : '';
        $skills = isset($data['skills']) ? sanitize_textarea_field($data['skills']) : '';
        $cover_letter = isset($data['cover_letter']) ? sanitize_textarea_field($data['cover_letter']) : '';
        $job_type = isset($data['job_type']) ? sanitize_text_field($data['job_type']) : '';
        $location = isset($data['location']) ? sanitize_text_field($data['location']) : '';
        $consent = isset($data['consent']) && $data['consent'] === 'yes';
        
        // Prepare post data
        $applicant_data = array(
            'post_title' => $first_name . ' ' . $last_name,
            'post_content' => $cover_letter,
            'post_status' => 'draft',
            'post_type' => 'bpp_applicant',
            'comment_status' => 'closed'
        );
        
        // Insert the post into the database
        $applicant_id = wp_insert_post($applicant_data);
        
        if (!$applicant_id || is_wp_error($applicant_id)) {
            return false;
        }
        
        // Save meta data
        update_post_meta($applicant_id, 'bpp_email', $email);
        update_post_meta($applicant_id, 'bpp_phone', $phone);
        update_post_meta($applicant_id, 'bpp_job_title', $job_title);
        update_post_meta($applicant_id, 'bpp_years_experience', $years_experience);
        update_post_meta($applicant_id, 'bpp_linkedin', $linkedin);
        update_post_meta($applicant_id, 'bpp_website', $website);
        update_post_meta($applicant_id, 'bpp_skills', $skills);
        update_post_meta($applicant_id, 'bpp_job_type', $job_type);
        update_post_meta($applicant_id, 'bpp_location', $location);
        update_post_meta($applicant_id, 'bpp_consent', $consent ? 'yes' : 'no');
        update_post_meta($applicant_id, 'bpp_submission_date', current_time('mysql'));
        
        // Set the industry taxonomy
        if (!empty($industry)) {
            error_log('BPP Debug - Setting industry: ' . print_r($industry, true));
            // Try to resolve the term if it's a term_id
            if (is_numeric($industry)) {
                $term = get_term($industry, 'bpp_industry');
                if (!is_wp_error($term) && !empty($term)) {
                    $industry = $term->slug;
                }
            }
            wp_set_object_terms($applicant_id, $industry, 'bpp_industry', false);
        }
        
        // Handle resume attachment if uploaded
        if (isset($uploaded_files['resume'])) {
            update_post_meta($applicant_id, 'bpp_resume', $uploaded_files['resume']);
        }
        
        // Handle photo attachment if uploaded
        if (isset($uploaded_files['photo'])) {
            update_post_meta($applicant_id, 'bpp_photo', $uploaded_files['photo']);
            
            // Set as featured image if available
            set_post_thumbnail($applicant_id, $uploaded_files['photo']);
        }
        
        return $applicant_id;
    }

    /**
     * Process file uploads for the application
     *
     * @since    1.0.0
     * @param    array    $files    The $_FILES array
     * @return   array              Array of uploaded file IDs and any errors
     */
    private function process_file_uploads($files) {
        $uploaded_files = array();
        $errors = array();
        
        // Debug file uploads processing
        error_log('Processing file uploads: ' . print_r($files, true));
        
        // Process resume upload
        if (!empty($files['resume']) && $files['resume']['error'] === UPLOAD_ERR_OK) {
            error_log('Processing resume file: ' . $files['resume']['name']);
            
            $resume_result = $this->process_single_file_upload('resume', $files['resume'], array(
                'allowed_types' => array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                'max_size' => 5 * 1024 * 1024 // 5MB
            ));
            
            if (is_wp_error($resume_result)) {
                error_log('Resume upload error: ' . $resume_result->get_error_message());
                $errors['resume'] = $resume_result->get_error_message();
            } else {
                error_log('Resume uploaded successfully. Attachment ID: ' . $resume_result);
                $uploaded_files['resume'] = $resume_result;
            }
        } elseif (!empty($files['resume']) && $files['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle other upload errors
            $error_message = $this->get_file_upload_error_message($files['resume']['error']);
            error_log('Resume upload error: ' . $error_message);
            $errors['resume'] = $error_message;
        }
        
        // Process photo upload
        if (!empty($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
            error_log('Processing photo file: ' . $files['photo']['name']);
            
            $photo_result = $this->process_single_file_upload('photo', $files['photo'], array(
                'allowed_types' => array('image/jpeg', 'image/png', 'image/gif'),
                'max_size' => 2 * 1024 * 1024 // 2MB
            ));
            
            if (is_wp_error($photo_result)) {
                error_log('Photo upload error: ' . $photo_result->get_error_message());
                $errors['photo'] = $photo_result->get_error_message();
            } else {
                error_log('Photo uploaded successfully. Attachment ID: ' . $photo_result);
                $uploaded_files['photo'] = $photo_result;
            }
        } elseif (!empty($files['photo']) && $files['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle other upload errors
            $error_message = $this->get_file_upload_error_message($files['photo']['error']);
            error_log('Photo upload error: ' . $error_message);
            $errors['photo'] = $error_message;
        }
        
        // Add errors if found
        if (!empty($errors)) {
            $uploaded_files['errors'] = $errors;
        }
        
        return $uploaded_files;
    }
    
    /**
     * Handle individual file upload
     *
     * @since    1.0.0
     * @param    string    $file_key       The file key (resume or photo)
     * @param    array     $file           The file data from $_FILES
     * @param    array     $options        Options for file upload
     * @return   int|WP_Error              Attachment ID on success, WP_Error on failure
     */
    private function process_single_file_upload($file_key, $file, $options = array()) {
        // Default options
        $defaults = array(
            'allowed_types' => array(),
            'max_size' => 0
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // Check if the upload is valid
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Debug file upload
        error_log("Processing single file upload for {$file_key}");
        error_log("File details: " . print_r($file, true));
        
        // Verify file type
        $file_type = wp_check_filetype(basename($file['name']));
        error_log("File type check: " . print_r($file_type, true));
        
        if (!empty($options['allowed_types']) && !in_array($file['type'], $options['allowed_types'])) {
            error_log("Invalid file type: {$file['type']}. Allowed types: " . implode(', ', $options['allowed_types']));
            return new WP_Error('invalid_type', __('Invalid file type.', 'black-potential-pipeline'));
        }
        
        // Verify file size
        if ($options['max_size'] > 0 && $file['size'] > $options['max_size']) {
            error_log("File too large: {$file['size']} bytes. Max allowed: {$options['max_size']} bytes");
            return new WP_Error('invalid_size', __('File is too large.', 'black-potential-pipeline'));
        }
        
        // Upload the file
        $upload_overrides = array('test_form' => false);
        error_log("Attempting to upload file with wp_handle_upload");
        $uploaded_file = wp_handle_upload($file, $upload_overrides);
        
        if (isset($uploaded_file['error'])) {
            error_log("wp_handle_upload error: " . $uploaded_file['error']);
            return new WP_Error('upload_error', $uploaded_file['error']);
        }
        
        error_log("File uploaded successfully: " . print_r($uploaded_file, true));
        
        // Create attachment
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        $attachment = array(
            'guid' => $uploaded_file['url'],
            'post_mime_type' => $uploaded_file['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($file['name'])),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        error_log("Creating attachment with data: " . print_r($attachment, true));
        $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);
        
        if (is_wp_error($attachment_id)) {
            error_log("Error creating attachment: " . $attachment_id->get_error_message());
            return $attachment_id;
        }
        
        error_log("Attachment created with ID: " . $attachment_id);
        
        // Generate metadata
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        return $attachment_id;
    }

    /**
     * Get the error message for file upload errors
     *
     * @since    1.0.0
     * @param    int    $error_code    The error code from the upload
     * @return   string                 The human-readable error message
     */
    private function get_file_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return __('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'black-potential-pipeline');
            case UPLOAD_ERR_FORM_SIZE:
                return __('The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.', 'black-potential-pipeline');
            case UPLOAD_ERR_PARTIAL:
                return __('The uploaded file was only partially uploaded.', 'black-potential-pipeline');
            case UPLOAD_ERR_NO_FILE:
                return __('No file was uploaded.', 'black-potential-pipeline');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('Missing a temporary folder.', 'black-potential-pipeline');
            case UPLOAD_ERR_CANT_WRITE:
                return __('Failed to write file to disk.', 'black-potential-pipeline');
            case UPLOAD_ERR_EXTENSION:
                return __('A PHP extension stopped the file upload.', 'black-potential-pipeline');
            default:
                return __('Unknown upload error.', 'black-potential-pipeline');
        }
    }
} 