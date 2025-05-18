<?php
/**
 * Handle admin AJAX requests for the Black Potential Pipeline plugin.
 *
 * This class processes admin AJAX requests such as application approval and rejection.
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
 * The admin AJAX handler class.
 *
 * This class handles all admin AJAX requests for the Black Potential Pipeline plugin,
 * including application approval and rejection.
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 * @author     Adedayo Ayomide Samue ayomide@codemygig.com
 */
class BPP_Admin_Ajax {

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
        add_action('wp_ajax_bpp_approve_applicant', array($this, 'approve_applicant'));
        add_action('wp_ajax_bpp_reject_applicant', array($this, 'reject_applicant'));
        add_action('wp_ajax_bpp_handle_profile_submission', array($this, 'handle_profile_submission'));
    }

    /**
     * Handle Ajax request to approve an applicant
     *
     * @since 1.0.0
     * @return void
     */
    public function approve_applicant() {
        // Check nonce for security
        check_ajax_referer('bpp_admin_nonce', 'security');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'black-potential-pipeline')));
        }
        
        // Get applicant ID
        $applicant_id = isset($_POST['applicant_id']) ? intval($_POST['applicant_id']) : 0;
        
        if ($applicant_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid applicant ID.', 'black-potential-pipeline')));
        }
        
        // Update post status to publish
        $update = wp_update_post(array(
            'ID' => $applicant_id,
            'post_status' => 'publish'
        ));
        
        if (is_wp_error($update) || $update === 0) {
            wp_send_json_error(array('message' => __('Failed to approve applicant.', 'black-potential-pipeline')));
        }
        
        // Update meta field with approval time
        update_post_meta($applicant_id, 'bpp_approved_date', current_time('mysql'));
        update_post_meta($applicant_id, 'bpp_application_status', 'approved');
        
        // Send approval notification email
        $email_manager = new BPP_Email_Manager($this->plugin_name, $this->version);
        $email_manager->send_approval_notification($applicant_id);
        
        wp_send_json_success(array(
            'message' => __('Applicant successfully approved.', 'black-potential-pipeline'),
            'redirect' => admin_url('admin.php?page=bpp-new-applications')
        ));
    }

    /**
     * Handle Ajax request to reject an applicant
     *
     * @since 1.0.0
     * @return void
     */
    public function reject_applicant() {
        // Check nonce for security
        check_ajax_referer('bpp_admin_nonce', 'security');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'black-potential-pipeline')));
        }
        
        // Get applicant ID and rejection reason
        $applicant_id = isset($_POST['applicant_id']) ? intval($_POST['applicant_id']) : 0;
        $rejection_reason = isset($_POST['rejection_reason']) ? sanitize_textarea_field($_POST['rejection_reason']) : '';
        
        if ($applicant_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid applicant ID.', 'black-potential-pipeline')));
        }
        
        // Update post status to trash
        $update = wp_update_post(array(
            'ID' => $applicant_id,
            'post_status' => 'trash'
        ));
        
        if (is_wp_error($update) || $update === 0) {
            wp_send_json_error(array('message' => __('Failed to reject applicant.', 'black-potential-pipeline')));
        }
        
        // Update meta fields
        update_post_meta($applicant_id, 'bpp_rejected_date', current_time('mysql'));
        update_post_meta($applicant_id, 'bpp_rejection_reason', $rejection_reason);
        update_post_meta($applicant_id, 'bpp_application_status', 'rejected');
        
        // Send rejection notification email
        $email_manager = new BPP_Email_Manager($this->plugin_name, $this->version);
        $email_manager->send_rejection_notification($applicant_id);
        
        wp_send_json_success(array(
            'message' => __('Applicant successfully rejected.', 'black-potential-pipeline'),
            'redirect' => admin_url('admin.php?page=bpp-new-applications')
        ));
    }

    /**
     * Log approval/rejection actions for tracking.
     *
     * @since    1.0.0
     * @param    int       $applicant_id    The applicant post ID.
     * @param    string    $action          The action taken (approved/rejected).
     * @param    string    $reason          Optional reason for rejection.
     */
    private function log_action($applicant_id, $action, $reason = '') {
        $log_entry = array(
            'time' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'action' => $action,
            'reason' => $reason
        );
        
        $log = get_post_meta($applicant_id, 'bpp_action_log', true);
        if (!is_array($log)) {
            $log = array();
        }
        
        $log[] = $log_entry;
        update_post_meta($applicant_id, 'bpp_action_log', $log);
    }

    /**
     * Send error response as JSON.
     *
     * @since    1.0.0
     * @param    string    $message    Error message.
     */
    private function send_error_response($message) {
        wp_send_json_error(array(
            'message' => $message
        ));
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

    public function handle_profile_submission() {
        // Check nonce for security
        check_ajax_referer('bpp_form_nonce', 'security');
        
        // Initialize the form handler if not already done
        $form_handler = new BPP_Form_Handler();
        
        // Process the submission
        $result = $form_handler->process_form_submission();
        
        if ($result['success']) {
            wp_send_json_success(array('message' => $result['message']));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
        
        wp_die();
    }
} 