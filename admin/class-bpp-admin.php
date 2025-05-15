<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/admin
 * @author     Your Name <email@example.com>
 */
class BPP_Admin {

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

        // Add these in your admin class constructor or init method
        add_action('wp_ajax_bpp_approve_applicant', array($this, 'approve_applicant'));
        add_action('wp_ajax_bpp_reject_applicant', array($this, 'reject_applicant'));
        add_action('wp_ajax_bpp_toggle_featured', array($this, 'toggle_featured'));
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            BPP_PLUGIN_URL . 'admin/css/bpp-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            BPP_PLUGIN_URL . 'admin/js/bpp-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        // Localize the script with data needed for AJAX
        wp_localize_script(
            $this->plugin_name,
            'bpp_admin_obj',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bpp_admin_nonce'),
                'i18n' => array(
                    'approve_confirm' => __('Are you sure you want to approve this application?', 'black-potential-pipeline'),
                    'error' => __('An error occurred. Please try again.', 'black-potential-pipeline'),
                    'no_applications' => __('No new applications at this time.', 'black-potential-pipeline'),
                    'no_professionals' => __('No approved professionals found.', 'black-potential-pipeline'),
                    'feature_text' => __('Feature', 'black-potential-pipeline'),
                    'unfeature_text' => __('Unfeature', 'black-potential-pipeline'),
                    'featured_text' => __('Featured', 'black-potential-pipeline'),
                    'approved_text' => __('Approved', 'black-potential-pipeline'),
                ),
            )
        );
    }

    /**
     * Add menu pages for the plugin.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Add main menu page
        add_menu_page(
            __('Black Potential Pipeline', 'black-potential-pipeline'),
            __('Black Potential', 'black-potential-pipeline'),
            'manage_options',
            'bpp-dashboard',
            array($this, 'display_dashboard_page'),
            'dashicons-groups',
            30
        );

        // Add submenu pages
        add_submenu_page(
            'bpp-dashboard',
            __('Dashboard', 'black-potential-pipeline'),
            __('Dashboard', 'black-potential-pipeline'),
            'manage_options',
            'bpp-dashboard',
            array($this, 'display_dashboard_page')
        );

        add_submenu_page(
            'bpp-dashboard',
            __('New Applications', 'black-potential-pipeline'),
            __('New Applications', 'black-potential-pipeline'),
            'manage_options',
            'bpp-new-applications',
            array($this, 'display_new_applications_page')
        );

        add_submenu_page(
            'bpp-dashboard',
            __('Approved Professionals', 'black-potential-pipeline'),
            __('Approved', 'black-potential-pipeline'),
            'manage_options',
            'bpp-approved',
            array($this, 'display_approved_page')
        );

        add_submenu_page(
            'bpp-dashboard',
            __('Rejected Applications', 'black-potential-pipeline'),
            __('Rejected', 'black-potential-pipeline'),
            'manage_options',
            'bpp-rejected',
            array($this, 'display_rejected_page')
        );

        add_submenu_page(
            'bpp-dashboard',
            __('Settings', 'black-potential-pipeline'),
            __('Settings', 'black-potential-pipeline'),
            'manage_options',
            'bpp-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register settings
        register_setting('bpp_options', 'bpp_email_notifications');
        register_setting('bpp_options', 'bpp_form_fields');
        register_setting('bpp_options', 'bpp_directory_settings');
        register_setting('bpp_options', 'bpp_approval_workflow');

        // Add settings sections
        add_settings_section(
            'bpp_email_section',
            __('Email Notification Settings', 'black-potential-pipeline'),
            array($this, 'email_section_callback'),
            'bpp-settings'
        );

        add_settings_section(
            'bpp_form_section',
            __('Form Field Settings', 'black-potential-pipeline'),
            array($this, 'form_section_callback'),
            'bpp-settings'
        );

        add_settings_section(
            'bpp_directory_section',
            __('Directory Display Settings', 'black-potential-pipeline'),
            array($this, 'directory_section_callback'),
            'bpp-settings'
        );

        add_settings_section(
            'bpp_workflow_section',
            __('Approval Workflow Settings', 'black-potential-pipeline'),
            array($this, 'workflow_section_callback'),
            'bpp-settings'
        );

        // Add settings fields
        add_settings_field(
            'bpp_admin_email',
            __('Admin Email', 'black-potential-pipeline'),
            array($this, 'admin_email_callback'),
            'bpp-settings',
            'bpp_email_section'
        );

        add_settings_field(
            'bpp_notification_enabled',
            __('Enable Email Notifications', 'black-potential-pipeline'),
            array($this, 'notification_enabled_callback'),
            'bpp-settings',
            'bpp_email_section'
        );

        // Add more settings fields as needed
    }

    /**
     * Callback for email settings section.
     *
     * @since    1.0.0
     */
    public function email_section_callback() {
        echo '<p>' . __('Configure email notification settings for the Black Potential Pipeline.', 'black-potential-pipeline') . '</p>';
    }

    /**
     * Callback for form settings section.
     *
     * @since    1.0.0
     */
    public function form_section_callback() {
        echo '<p>' . __('Configure submission form fields and requirements.', 'black-potential-pipeline') . '</p>';
    }

    /**
     * Callback for directory settings section.
     *
     * @since    1.0.0
     */
    public function directory_section_callback() {
        echo '<p>' . __('Configure how the professional directory displays on your site.', 'black-potential-pipeline') . '</p>';
    }

    /**
     * Callback for workflow settings section.
     *
     * @since    1.0.0
     */
    public function workflow_section_callback() {
        echo '<p>' . __('Configure the approval workflow and notifications.', 'black-potential-pipeline') . '</p>';
    }

    /**
     * Callback for admin email field.
     *
     * @since    1.0.0
     */
    public function admin_email_callback() {
        $options = get_option('bpp_email_notifications');
        $email = isset($options['admin_email']) ? $options['admin_email'] : get_option('admin_email');
        echo '<input type="email" id="bpp_admin_email" name="bpp_email_notifications[admin_email]" value="' . esc_attr($email) . '" class="regular-text" />';
    }

    /**
     * Callback for notification enabled field.
     *
     * @since    1.0.0
     */
    public function notification_enabled_callback() {
        $options = get_option('bpp_email_notifications');
        $enabled = isset($options['enabled']) ? $options['enabled'] : 1;
        echo '<input type="checkbox" id="bpp_notification_enabled" name="bpp_email_notifications[enabled]" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="bpp_notification_enabled">' . __('Send email notifications for new submissions', 'black-potential-pipeline') . '</label>';
    }

    /**
     * Display the dashboard page.
     *
     * @since    1.0.0
     */
    public function display_dashboard_page() {
        include_once BPP_PLUGIN_DIR . 'admin/partials/bpp-admin-dashboard.php';
    }

    /**
     * Display the new applications page.
     *
     * @since    1.0.0
     */
    public function display_new_applications_page() {
        include_once BPP_PLUGIN_DIR . 'admin/partials/bpp-admin-new-applications.php';
    }

    /**
     * Display the approved professionals page.
     *
     * @since    1.0.0
     */
    public function display_approved_page() {
        include_once BPP_PLUGIN_DIR . 'admin/partials/bpp-admin-approved.php';
    }

    /**
     * Display the rejected applications page.
     *
     * @since    1.0.0
     */
    public function display_rejected_page() {
        include_once BPP_PLUGIN_DIR . 'admin/partials/bpp-admin-rejected.php';
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        include_once BPP_PLUGIN_DIR . 'admin/partials/bpp-admin-settings.php';
    }

    /**
     * Display admin notices.
     *
     * @since    1.0.0
     */
    public function admin_notices() {
        if (isset($_GET['page']) && strpos($_GET['page'], 'bpp-') === 0) {
            if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'black-potential-pipeline') . '</p></div>';
            }
            
            if (isset($_GET['approved']) && $_GET['approved'] === 'true') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Applicant approved successfully!', 'black-potential-pipeline') . '</p></div>';
            }
            
            if (isset($_GET['rejected']) && $_GET['rejected'] === 'true') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Applicant rejected successfully!', 'black-potential-pipeline') . '</p></div>';
            }
        }
    }

    /**
     * AJAX handler for approving an applicant.
     *
     * @since    1.0.0
     */
    public function approve_applicant() {
        // Check nonce for security
        check_ajax_referer('bpp_admin_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'black-potential-pipeline')));
        }
        
        // Get applicant ID
        $applicant_id = isset($_POST['applicant_id']) ? intval($_POST['applicant_id']) : 0;
        
        if (!$applicant_id) {
            wp_send_json_error('Invalid applicant ID');
        }

        // Update the post status to 'publish' and set appropriate meta
        $result = wp_update_post(array(
            'ID' => $applicant_id,
            'post_status' => 'publish',
        ));

        if ($result) {
            update_post_meta($applicant_id, 'bpp_approved', 1);
            update_post_meta($applicant_id, 'bpp_approval_date', current_time('mysql'));
            
            // Send notification email if enabled
            $this->send_approval_notification($applicant_id);
            
            wp_send_json_success('Applicant approved successfully');
        } else {
            wp_send_json_error('Failed to approve applicant');
        }
    }

    /**
     * AJAX handler for rejecting an applicant.
     *
     * @since    1.0.0
     */
    public function reject_applicant() {
        // Check nonce
        check_ajax_referer('bpp_admin_nonce', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Get applicant ID
        $applicant_id = isset($_POST['applicant_id']) ? intval($_POST['applicant_id']) : 0;
        
        if (!$applicant_id) {
            wp_send_json_error('Invalid applicant ID');
        }

        // Update the post status to 'private' and set appropriate meta
        $result = wp_update_post(array(
            'ID' => $applicant_id,
            'post_status' => 'private',
        ));

        if ($result) {
            update_post_meta($applicant_id, 'bpp_rejected', 1);
            update_post_meta($applicant_id, 'bpp_rejection_date', current_time('mysql'));
            update_post_meta($applicant_id, 'bpp_rejection_reason', sanitize_text_field($_POST['reason'] ?? ''));
            
            // Send notification email if enabled
            $this->send_rejection_notification($applicant_id);
            
            wp_send_json_success('Applicant rejected successfully');
        } else {
            wp_send_json_error('Failed to reject applicant');
        }
    }

    /**
     * Toggle featured status for an applicant.
     *
     * @since    1.0.0
     */
    public function toggle_featured() {
        // Check nonce
        check_ajax_referer('bpp_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'black-potential-pipeline'));
            return;
        }
        
        // Get applicant ID
        $applicant_id = isset($_POST['applicant_id']) ? intval($_POST['applicant_id']) : 0;
        
        // Check if we have a valid applicant ID
        if (!$applicant_id || get_post_type($applicant_id) !== 'bpp_applicant') {
            wp_send_json_error(__('Invalid applicant ID.', 'black-potential-pipeline'));
            return;
        }
        
        // Get featured status (1 to feature, 0 to unfeature)
        $featured = isset($_POST['featured']) ? intval($_POST['featured']) : 0;
        
        // Update the meta field
        $result = update_post_meta($applicant_id, 'bpp_featured', $featured);
        
        if ($result) {
            // Send success response
            wp_send_json_success(array(
                'message' => $featured 
                    ? __('Professional marked as featured.', 'black-potential-pipeline') 
                    : __('Professional removed from featured.', 'black-potential-pipeline'),
                'featured' => $featured
            ));
        } else {
            // Send error response
            wp_send_json_error(__('Failed to update featured status.', 'black-potential-pipeline'));
        }
        
        wp_die();
    }

    /**
     * Send approval notification email to the applicant.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant post ID.
     */
    private function send_approval_notification($applicant_id) {
        $options = get_option('bpp_email_notifications');
        
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        $applicant = get_post($applicant_id);
        $email = get_post_meta($applicant_id, 'bpp_email', true);
        
        if (!$email || !is_email($email)) {
            return;
        }
        
        $subject = __('Congratulations! Your Black Potential Pipeline Application Has Been Approved', 'black-potential-pipeline');
        
        $message = sprintf(
            __('Hello %s,

Congratulations! We are pleased to inform you that your application to join the Black Potential Pipeline has been approved.

Your profile is now live on our website and visible to potential employers looking for talented Black professionals in the green jobs sector.

You can view your public profile here: %s

Thank you for joining the Black Potential Pipeline!

Best regards,
The Black Potential Pipeline Team', 'black-potential-pipeline'),
            get_the_title($applicant),
            get_permalink($applicant_id)
        );
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, $message, $headers);
    }

    /**
     * Send rejection notification email to the applicant.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant post ID.
     */
    private function send_rejection_notification($applicant_id) {
        $options = get_option('bpp_email_notifications');
        
        if (!isset($options['enabled']) || !$options['enabled']) {
            return;
        }
        
        $applicant = get_post($applicant_id);
        $email = get_post_meta($applicant_id, 'bpp_email', true);
        
        if (!$email || !is_email($email)) {
            return;
        }
        
        $subject = __('Update on Your Black Potential Pipeline Application', 'black-potential-pipeline');
        
        $reason = get_post_meta($applicant_id, 'bpp_rejection_reason', true);
        $reason_text = '';
        
        if ($reason) {
            $reason_text = sprintf(
                __("The reviewer provided the following feedback: \"%s\"", 'black-potential-pipeline'),
                $reason
            );
        }
        
        $message = sprintf(
            __('Hello %s,

Thank you for your interest in joining the Black Potential Pipeline.

After careful review of your application, we regret to inform you that we are unable to include your profile in our pipeline at this time.

%s

We encourage you to consider applying again in the future with updated information.

Best regards,
The Black Potential Pipeline Team', 'black-potential-pipeline'),
            get_the_title($applicant),
            $reason_text
        );
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($email, $subject, $message, $headers);
    }
} 