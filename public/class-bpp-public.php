<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://codemygig.com,
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * the public-facing side of the site.
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/public
 * @author     Adedayo Ayomide Samue ayomide@codemygig.com
 */
class BPP_Public {

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
        
        // Add filter to handle single template for bpp_applicant custom post type
        add_filter('single_template', array($this, 'load_applicant_template'));
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Main public styles
        wp_enqueue_style(
            $this->plugin_name,
            BPP_PLUGIN_URL . 'public/css/bpp-public.css',
            array(),
            $this->version,
            'all'
        );

        // Form-specific styles
        wp_register_style(
            'bpp-form-style',
            BPP_PLUGIN_URL . 'public/css/bpp-form.css',
            array($this->plugin_name),
            $this->version,
            'all'
        );

        // Directory-specific styles
        wp_register_style(
            'bpp-directory-style',
            BPP_PLUGIN_URL . 'public/css/bpp-directory.css',
            array($this->plugin_name),
            $this->version,
            'all'
        );

        // Featured candidates styles
        wp_register_style(
            'bpp-featured-style',
            BPP_PLUGIN_URL . 'public/css/bpp-featured.css',
            array($this->plugin_name),
            $this->version,
            'all'
        );

        // Statistics styles
        wp_register_style(
            'bpp-stats-style',
            BPP_PLUGIN_URL . 'public/css/bpp-stats.css',
            array($this->plugin_name),
            $this->version,
            'all'
        );
        
        // Register Bootstrap for forms
        wp_register_style(
            'bpp-bootstrap-style',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            array(),
            '5.3.0',
            'all'
        );
        
        // Register custom Bootstrap styles
        wp_register_style(
            'bpp-bootstrap-custom-style',
            BPP_PLUGIN_URL . 'public/css/bpp-bootstrap.css',
            array('bpp-bootstrap-style'),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Main public scripts
        wp_enqueue_script(
            $this->plugin_name,
            BPP_PLUGIN_URL . 'public/js/bpp-public.js',
            array('jquery'),
            $this->version,
            false
        );

        // Form-specific scripts
        wp_register_script(
            'bpp-form-script',
            BPP_PLUGIN_URL . 'public/js/bpp-form.js',
            array('jquery', $this->plugin_name),
            $this->version,
            true
        );
        
        // Register Bootstrap JS for forms
        wp_register_script(
            'bpp-bootstrap-script',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            '5.3.0',
            true
        );

        // Localize the form script with data needed for AJAX
        wp_localize_script(
            'bpp-form-script',
            'bpp_form_obj',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bpp_form_nonce'),
                'i18n' => array(
                    'submit_success' => __('Thank you for your submission! We will review your application shortly.', 'black-potential-pipeline'),
                    'submit_error' => __('Error occurred. Please try again.', 'black-potential-pipeline'),
                    'file_size_error' => __('File size exceeds the maximum limit.', 'black-potential-pipeline'),
                    'file_type_error' => __('File type not allowed.', 'black-potential-pipeline'),
                    'required_field' => __('This field is required.', 'black-potential-pipeline'),
                    'invalid_email' => __('Please enter a valid email address.', 'black-potential-pipeline'),
                ),
            )
        );

        // Directory-specific scripts
        wp_register_script(
            'bpp-directory-script',
            BPP_PLUGIN_URL . 'public/js/bpp-directory.js',
            array('jquery', $this->plugin_name),
            $this->version,
            true
        );

        // Featured candidates scripts
        wp_register_script(
            'bpp-featured-script',
            BPP_PLUGIN_URL . 'public/js/bpp-featured.js',
            array('jquery', $this->plugin_name),
            $this->version,
            true
        );

        // Statistics scripts
        wp_register_script(
            'bpp-stats-script',
            BPP_PLUGIN_URL . 'public/js/bpp-stats.js',
            array('jquery', $this->plugin_name),
            $this->version,
            true
        );
    }

    /**
     * Process the application submission from the form.
     *
     * This method is no longer used as form handling is now done by BPP_Form_Handler.
     * 
     * @since    1.0.0
     * @deprecated
     */
    /*
    public function process_application_submission() {
        // This method is no longer used
        // Form submissions are now handled by BPP_Form_Handler
    }
    */

    /**
     * Handle resume file upload.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant post ID.
     */
    private function handle_resume_upload($applicant_id) {
        // Check if the upload is valid
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_overrides = array('test_form' => false);
        $file = wp_handle_upload($_FILES['resume'], $upload_overrides);

        if (isset($file['error'])) {
            return false;
        }

        // Create an attachment
        $filename = $_FILES['resume']['name'];
        $wp_filetype = wp_check_filetype($filename);

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment, $file['file'], $applicant_id);

        if (!is_wp_error($attachment_id)) {
            // Update metadata for the attachment
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            // Save the resume attachment ID as post meta
            update_post_meta($applicant_id, 'bpp_resume', $attachment_id);
            return true;
        }

        return false;
    }

    /**
     * Handle photo file upload and set as featured image.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant post ID.
     */
    private function handle_photo_upload($applicant_id) {
        // Check if the upload is valid
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $upload_overrides = array('test_form' => false);
        $file = wp_handle_upload($_FILES['photo'], $upload_overrides);

        if (isset($file['error'])) {
            return false;
        }

        // Create an attachment
        $filename = $_FILES['photo']['name'];
        $wp_filetype = wp_check_filetype($filename);

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name($filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attachment_id = wp_insert_attachment($attachment, $file['file'], $applicant_id);

        if (!is_wp_error($attachment_id)) {
            // Update metadata for the attachment
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            // Set as featured image
            set_post_thumbnail($applicant_id, $attachment_id);
            return true;
        }

        return false;
    }

    /**
     * Send notification email to admin about new application.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant post ID.
     */
    private function send_admin_notification($applicant_id) {
        $options = get_option('bpp_email_notifications');
        
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        $admin_email = isset($options['admin_email']) ? $options['admin_email'] : get_option('admin_email');
        $applicant = get_post($applicant_id);
        
        $subject = sprintf(__('New Black Potential Pipeline Application: %s', 'black-potential-pipeline'), get_the_title($applicant));
        
        $message = sprintf(
            __('A new application has been submitted to the Black Potential Pipeline:

Name: %s
Email: %s
Industry: %s
Job Title: %s
Years of Experience: %s

You can review this application in the WordPress admin area:
%s

Thank you,
Black Potential Pipeline Plugin', 'black-potential-pipeline'),
            get_the_title($applicant),
            get_post_meta($applicant_id, 'bpp_email', true),
            function_exists('is_wp_error') && function_exists('wp_get_post_terms') ? 
                (($terms = wp_get_post_terms($applicant_id, 'bpp_industry', array('fields' => 'names'))) && !is_wp_error($terms) && !empty($terms) ? $terms[0] : '') : '',
            get_post_meta($applicant_id, 'bpp_job_title', true),
            get_post_meta($applicant_id, 'bpp_years_experience', true),
            admin_url('admin.php?page=bpp-new-applications')
        );
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($admin_email, $subject, $message, $headers);
    }

    /**
     * Send confirmation email to applicant.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant post ID.
     */
    private function send_applicant_confirmation($applicant_id) {
        $options = get_option('bpp_email_notifications');
        
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        $email = get_post_meta($applicant_id, 'bpp_email', true);
        
        if (!$email || !is_email($email)) {
            return;
        }
        
        $applicant = get_post($applicant_id);
        $first_name = explode(' ', get_the_title($applicant))[0];
        
        $subject = __('Thank You for Your Black Potential Pipeline Application', 'black-potential-pipeline');
        
        $message = sprintf(
            __('Hello %s,

Thank you for submitting your application to the Black Potential Pipeline. We have received your information and will review it shortly.

What happens next?
- Our team will review your application
- If approved, your profile will be featured in our Black Potential Pipeline directory
- You will receive an email notification about the status of your application

If you have any questions, please feel free to contact us.

Best regards,
The Black Potential Pipeline Team', 'black-potential-pipeline'),
            $first_name
        );
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, $message, $headers);
    }

    /**
     * Display the submission form via shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display the form.
     */
    public function display_submission_form($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Join the Black Potential Pipeline', 'black-potential-pipeline'),
                'success_message' => __('Thank you for your submission! We will review your application shortly.', 'black-potential-pipeline'),
                'use_bootstrap' => 'yes',
            ),
            $atts,
            'black_potential_pipeline_form'
        );

        // Check if Bootstrap styling is enabled
        if (isset($atts['use_bootstrap']) && $atts['use_bootstrap'] === 'yes') {
            wp_enqueue_style('bpp-bootstrap-style');
            wp_enqueue_style('bpp-bootstrap-custom-style');
            wp_enqueue_script('bpp-bootstrap-script');
        } else {
            // Enqueue regular form-specific styles
            wp_enqueue_style('bpp-form-style');
        }
        
        // Enqueue form script
        wp_enqueue_script('bpp-form-script');

        // Start output buffering
        ob_start();

        // Include the form template
        include(plugin_dir_path(dirname(__FILE__)) . 'public/partials/bpp-submission-form.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Display the directory of all approved applicants via shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display the directory.
     */
    public function display_directory($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Black Potential Pipeline Directory', 'black-potential-pipeline'),
                'per_page' => 12,
                'layout' => 'grid', // grid or list
                'use_bootstrap' => 'yes', // Enable Bootstrap styling by default
            ),
            $atts,
            'black_potential_pipeline_directory'
        );

        // Check if Bootstrap styling is enabled
        if (isset($atts['use_bootstrap']) && $atts['use_bootstrap'] === 'yes') {
            wp_enqueue_style('bpp-bootstrap-style');
            wp_enqueue_style('bpp-bootstrap-custom-style');
            wp_enqueue_script('bpp-bootstrap-script');
        } else {
            // Enqueue regular directory-specific styles
            wp_enqueue_style('bpp-directory-style');
        }
        
        // Enqueue directory-specific scripts
        wp_enqueue_script('bpp-directory-script');

        // Start output buffering
        ob_start();

        // Include the directory template
        include(plugin_dir_path(dirname(__FILE__)) . 'public/partials/bpp-directory.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Display the directory of applicants in a specific category via shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display the category directory.
     */
    public function display_category_directory($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'category' => '',
                'title' => '',
                'per_page' => 12,
                'layout' => 'grid',
                'use_bootstrap' => 'no',
            ),
            $atts,
            'black_potential_pipeline_category'
        );
        
        // Check if category is provided
        if (empty($atts['category'])) {
            return '<p>' . __('Error: No category specified.', 'black-potential-pipeline') . '</p>';
        }
        
        // Try to get the term by slug first, then by name if slug fails
        $term = get_term_by('slug', $atts['category'], 'bpp_industry');
        if (!$term || is_wp_error($term)) {
            // Try to get by name
            $term = get_term_by('name', $atts['category'], 'bpp_industry');
        }
        
        // If we found a valid term, use it for the title if not explicitly provided
        if ($term && !is_wp_error($term) && empty($atts['title'])) {
            $atts['title'] = $term->name . ' ' . __('Professionals', 'black-potential-pipeline');
        } elseif (empty($atts['title'])) {
            // Default title if no term found and no title provided
            $atts['title'] = ucfirst($atts['category']) . ' ' . __('Professionals', 'black-potential-pipeline');
        }
        
        // Enqueue required assets
        wp_enqueue_style('bpp-directory-style');
        wp_enqueue_script('bpp-directory-script');
        
        // Check if Bootstrap styling is enabled
        if (isset($atts['use_bootstrap']) && $atts['use_bootstrap'] === 'yes') {
            wp_enqueue_style('bpp-bootstrap-style');
            wp_enqueue_style('bpp-bootstrap-custom-style');
            wp_enqueue_script('bpp-bootstrap-script');
        }
        
        // Start output buffering
        ob_start();
        
        // Include the template
        include BPP_PLUGIN_DIR . 'public/partials/bpp-category-directory.php';
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Display featured applicants via shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display featured candidates.
     */
    public function display_featured_applicants($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Featured Black Professionals', 'black-potential-pipeline'),
                'count' => 4,
                'layout' => 'carousel', // carousel, grid, or list
            ),
            $atts,
            'black_potential_pipeline_featured'
        );

        // Enqueue featured-specific scripts and styles
        wp_enqueue_style('bpp-featured-style');
        wp_enqueue_script('bpp-featured-script');

        // Start output buffering
        ob_start();

        // Include the featured candidates template
        include(plugin_dir_path(dirname(__FILE__)) . 'public/partials/bpp-featured.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Display featured applicants from a specific category in a slider/carousel format.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display category featured candidates.
     */
    public function display_category_featured($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'category'       => '',
                'title'          => '',
                'count'          => 12,
                'items_per_slide' => 4,
                'use_bootstrap'  => 'yes',
            ),
            $atts,
            'black_potential_pipeline_category_featured'
        );
        
        // Check if category is provided
        if (empty($atts['category'])) {
            return '<p>' . __('Error: No category specified for featured display.', 'black-potential-pipeline') . '</p>';
        }
        
        // Try to get the term by slug first, then by name if slug fails
        $term = get_term_by('slug', $atts['category'], 'bpp_industry');
        if (!$term || is_wp_error($term)) {
            // Try to get by name
            $term = get_term_by('name', $atts['category'], 'bpp_industry');
        }
        
        // If we found a valid term, use it for the title if not explicitly provided
        if ($term && !is_wp_error($term) && empty($atts['title'])) {
            $atts['title'] = $term->name . ' ' . __('Professionals', 'black-potential-pipeline');
        } elseif (empty($atts['title'])) {
            // Default title if no term found and no title provided
            $atts['title'] = ucfirst($atts['category']) . ' ' . __('Professionals', 'black-potential-pipeline');
        }
        
        // Enqueue required assets
        wp_enqueue_style('bpp-featured-style');
        wp_enqueue_script('bpp-featured-script');
        
        // Check if Bootstrap styling is enabled
        if (isset($atts['use_bootstrap']) && $atts['use_bootstrap'] === 'yes') {
            wp_enqueue_style('bpp-bootstrap-style');
            wp_enqueue_style('bpp-bootstrap-custom-style');
            wp_enqueue_script('bpp-bootstrap-script');
        }
        
        // Start output buffering
        ob_start();
        
        // Include the template
        include BPP_PLUGIN_DIR . 'public/partials/bpp-category-featured.php';
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Display statistics via shortcode.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display statistics.
     */
    public function display_statistics($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Black Potential Pipeline Statistics', 'black-potential-pipeline'),
                'show_categories' => 'yes',
                'show_total' => 'yes',
            ),
            $atts,
            'black_potential_pipeline_stats'
        );

        // Enqueue statistics-specific scripts and styles
        wp_enqueue_style('bpp-stats-style');
        wp_enqueue_script('bpp-stats-script');

        // Start output buffering
        ob_start();

        // Include the statistics template
        include(plugin_dir_path(dirname(__FILE__)) . 'public/partials/bpp-statistics.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Load custom template for single applicant view.
     *
     * @since    1.0.0
     * @param    string    $template    The current template path.
     * @return   string    Modified template path if needed.
     */
    public function load_applicant_template($template) {
        global $post;
        
        // Check if this is post ID 120 or a post with type bpp_applicant
        if ((isset($post->ID) && $post->ID == 120) || (isset($post->post_type) && $post->post_type === 'bpp_applicant')) {
            $custom_template = BPP_PLUGIN_DIR . 'public/partials/bpp-single-profile.php';
            
            // Use custom template if it exists
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
} 