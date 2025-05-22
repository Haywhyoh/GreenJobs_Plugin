<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://codemygig.com,
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
 * @author     Adedayo Ayomide Samue ayomide@codemygig.com
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
        add_action('wp_ajax_bpp_delete_applicant', array($this, 'delete_applicant'));
        
        // Add action for profile update form submission
        add_action('admin_post_bpp_update_profile', array($this, 'handle_profile_update'));
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
                    'confirm_approve'     => __('Are you sure you want to approve this application?', 'black-potential-pipeline'),
                    'confirm_reject'      => __('Are you sure you want to reject this application?', 'black-potential-pipeline'),
                    'confirm_remove'      => __('Are you sure you want to remove this professional from the approved list?', 'black-potential-pipeline'),
                    'no_applications'     => __('No new applications found.', 'black-potential-pipeline'),
                    'no_professionals'    => __('No approved professionals found.', 'black-potential-pipeline'),
                    'no_rejected_applications' => __('No rejected applications found.', 'black-potential-pipeline'),
                    'success'             => __('Success!', 'black-potential-pipeline'),
                    'error'               => __('Error occurred. Please try again.', 'black-potential-pipeline'),
                    'copied'              => __('Copied to clipboard!', 'black-potential-pipeline'),
                    'copy_fail'           => __('Failed to copy. Please try again.', 'black-potential-pipeline'),
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
        
        // Add a hidden submenu for the applicant profile view
        add_submenu_page(
            null, // No parent, so it won't appear in the menu
            __('Applicant Profile', 'black-potential-pipeline'),
            __('Applicant Profile', 'black-potential-pipeline'),
            'manage_options',
            'bpp-applicant-profile',
            array($this, 'display_applicant_profile_page')
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

        add_settings_section(
            'bpp_profile_privacy_section',
            __('Profile Privacy Settings', 'black-potential-pipeline'),
            array($this, 'profile_privacy_section_callback'),
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
     * Callback for profile privacy settings section.
     *
     * @since    1.0.0
     */
    public function profile_privacy_section_callback() {
        echo '<p>' . __('Control which information is visible on public professional profiles.', 'black-potential-pipeline') . '</p>';
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
     * Display the applicant profile page.
     * 
     * @since    1.0.0
     */
    public function display_applicant_profile_page() {
        include_once BPP_PLUGIN_DIR . 'admin/partials/bpp-admin-profile-view.php';
    }
    
    /**
     * Handle profile update form submission.
     * 
     * @since    1.0.0
     */
    public function handle_profile_update() {
        // Check nonce
        if (!isset($_POST['bpp_profile_nonce']) || !wp_verify_nonce($_POST['bpp_profile_nonce'], 'bpp_update_profile')) {
            wp_die(__('Security check failed.', 'black-potential-pipeline'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'black-potential-pipeline'));
        }
        
        // Get applicant ID
        $applicant_id = isset($_POST['applicant_id']) ? intval($_POST['applicant_id']) : 0;
        
        if (!$applicant_id) {
            wp_die(__('Invalid applicant ID.', 'black-potential-pipeline'));
        }
        
        // Get form data
        $name = isset($_POST['bpp_name']) ? sanitize_text_field($_POST['bpp_name']) : '';
        $job_title = isset($_POST['bpp_job_title']) ? sanitize_text_field($_POST['bpp_job_title']) : '';
        $industry = isset($_POST['bpp_industry']) ? sanitize_text_field($_POST['bpp_industry']) : '';
        $location = isset($_POST['bpp_location']) ? sanitize_text_field($_POST['bpp_location']) : '';
        $years_experience = isset($_POST['bpp_years_experience']) ? intval($_POST['bpp_years_experience']) : 0;
        $status = isset($_POST['bpp_status']) ? sanitize_text_field($_POST['bpp_status']) : 'publish';
        $featured = isset($_POST['bpp_featured']) ? 1 : 0;
        $email = isset($_POST['bpp_email']) ? sanitize_email($_POST['bpp_email']) : '';
        $phone = isset($_POST['bpp_phone']) ? sanitize_text_field($_POST['bpp_phone']) : '';
        $linkedin = isset($_POST['bpp_linkedin']) ? esc_url_raw($_POST['bpp_linkedin']) : '';
        $website = isset($_POST['bpp_website']) ? esc_url_raw($_POST['bpp_website']) : '';
        $skills = isset($_POST['bpp_skills']) ? sanitize_text_field($_POST['bpp_skills']) : '';
        $bio = isset($_POST['bpp_bio']) ? sanitize_textarea_field($_POST['bpp_bio']) : '';
        
        // Update the applicant post
        $post_data = array(
            'ID' => $applicant_id,
            'post_title' => $name,
            'post_content' => $bio,
            'post_status' => $status,
        );
        
        $updated = wp_update_post($post_data);
        
        if (!$updated) {
            wp_die(__('Failed to update profile.', 'black-potential-pipeline'));
        }
        
        // Update post meta
        update_post_meta($applicant_id, 'bpp_job_title', $job_title);
        update_post_meta($applicant_id, 'bpp_location', $location);
        update_post_meta($applicant_id, 'bpp_years_experience', $years_experience);
        update_post_meta($applicant_id, 'bpp_featured', $featured);
        update_post_meta($applicant_id, 'bpp_email', $email);
        update_post_meta($applicant_id, 'bpp_phone', $phone);
        update_post_meta($applicant_id, 'bpp_linkedin', $linkedin);
        update_post_meta($applicant_id, 'bpp_website', $website);
        update_post_meta($applicant_id, 'bpp_skills', $skills);
        update_post_meta($applicant_id, 'bpp_bio', $bio);
        
        // If status changed to publish, update approval date
        if ($status === 'publish') {
            // Only update approval date if it doesn't exist or status was previously not published
            $current_post = get_post($applicant_id);
            if ($current_post->post_status !== 'publish' || !get_post_meta($applicant_id, 'bpp_approval_date', true)) {
                update_post_meta($applicant_id, 'bpp_approval_date', current_time('mysql'));
            }
        }
        
        // Add/update industry if provided
        if (!empty($industry)) {
            wp_set_object_terms($applicant_id, $industry, 'bpp_industry', false);
        }
        
        // Redirect back to the profile view with a success message
        wp_redirect(admin_url('admin.php?page=bpp-applicant-profile&id=' . $applicant_id . '&updated=true'));
        exit;
    }

    /**
     * Display admin notices.
     *
     * @since    1.0.0
     */
    public function admin_notices() {
        // Check if we're on a plugin admin page
        if (isset($_GET['page']) && strpos($_GET['page'], 'bpp-') === 0) {
            // Show success messages for various actions
            if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'black-potential-pipeline') . '</p></div>';
            }
            
            if (isset($_GET['approved']) && $_GET['approved'] === 'true') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Applicant approved successfully!', 'black-potential-pipeline') . '</p></div>';
            }
            
            if (isset($_GET['rejected']) && $_GET['rejected'] === 'true') {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Applicant rejected successfully!', 'black-potential-pipeline') . '</p></div>';
            }
            
            // Check if permalinks are properly set up
            $permalink_structure = get_option('permalink_structure');
            if (empty($permalink_structure)) {
                // If permalinks are using default structure, show notice
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong><?php _e('Green Jobs Plugin: Permalink Structure Issue', 'black-potential-pipeline'); ?></strong>
                        <br>
                        <?php _e('You are currently using default permalinks, which will display profile URLs as "?p=123". For better SEO and user experience, please go to', 'black-potential-pipeline'); ?>
                        <a href="<?php echo admin_url('options-permalink.php'); ?>"><?php _e('Settings > Permalinks', 'black-potential-pipeline'); ?></a>
                        <?php _e('and choose a permalink structure (recommended: Post name).', 'black-potential-pipeline'); ?>
                    </p>
                </div>
                <?php
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

    /**
     * Permanently delete an applicant from the system
     *
     * @since 1.0.0
     */
    public function delete_applicant() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bpp_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'black-potential-pipeline'));
            return;
        }
        
        // Check for required data
        if (!isset($_POST['applicant_id']) || empty($_POST['applicant_id'])) {
            wp_send_json_error(__('No applicant specified', 'black-potential-pipeline'));
            return;
        }
        
        $applicant_id = intval($_POST['applicant_id']);
        
        // Delete any uploaded files (resume, photo)
        $resume_id = get_post_meta($applicant_id, 'bpp_resume', true);
        if (!empty($resume_id)) {
            wp_delete_attachment($resume_id, true);
        }
        
        $photo_id = get_post_meta($applicant_id, 'bpp_photo', true);
        if (!empty($photo_id)) {
            wp_delete_attachment($photo_id, true);
        }
        
        // Alternative photo ID field
        $professional_photo_id = get_post_meta($applicant_id, 'bpp_professional_photo', true);
        if (!empty($professional_photo_id) && $professional_photo_id != $photo_id) {
            wp_delete_attachment($professional_photo_id, true);
        }
        
        // Delete the applicant post (permanently, not to trash)
        $result = wp_delete_post($applicant_id, true);
        
        if ($result) {
            // Success
            wp_send_json_success(array(
                'message' => __('Applicant deleted successfully', 'black-potential-pipeline')
            ));
        } else {
            // Error
            wp_send_json_error(__('Failed to delete applicant', 'black-potential-pipeline'));
        }
    }
} 