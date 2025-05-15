<?php
/**
 * Handle email notifications for the Black Potential Pipeline plugin.
 *
 * This class manages all email templates and sends notifications to both
 * administrators and applicants at various stages of the application process.
 *
 * @link       https://example.com
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
 * The email manager class.
 *
 * Handles all email notifications for the plugin.
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 */
class BPP_Email_Manager {

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
     * Email options.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $options    Email options.
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of this plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = get_option('bpp_email_notifications', array());
        
        // Set default options if not set
        if (empty($this->options)) {
            $this->options = array(
                'enabled' => true,
                'admin_email' => get_option('admin_email'),
                'applicant_notify' => true,
                'from_name' => get_bloginfo('name'),
                'from_email' => get_option('admin_email'),
                'email_header_image' => '',
                'email_footer_text' => sprintf(__('© %s Black Potential Pipeline', 'black-potential-pipeline'), date('Y')),
                'email_primary_color' => '#1e8449',
                'submission_subject' => __('Thank You for Your Black Potential Pipeline Application', 'black-potential-pipeline'),
                'approval_subject' => __('Congratulations! Your Black Potential Pipeline Application Has Been Approved', 'black-potential-pipeline'),
                'rejection_subject' => __('Update on Your Black Potential Pipeline Application', 'black-potential-pipeline'),
                'admin_notification_subject' => __('New Black Potential Pipeline Application', 'black-potential-pipeline'),
            );
            update_option('bpp_email_notifications', $this->options);
        }
    }

    /**
     * Get the templates directory path.
     *
     * @since    1.0.0
     * @return   string    The templates directory path.
     */
    private function get_templates_dir() {
        return plugin_dir_path(dirname(__FILE__)) . 'includes/templates/emails/';
    }

    /**
     * Send notification to admin about new application.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant ID.
     * @return   bool                    True if email was sent, false otherwise.
     */
    public function send_admin_notification($applicant_id) {
        if (!$this->is_notification_enabled()) {
            return false;
        }
        
        $applicant = get_post($applicant_id);
        if (!$applicant) {
            return false;
        }

        // Get applicant data
        $data = $this->get_applicant_data($applicant_id);
        
        // Get template content
        $subject = isset($this->options['admin_notification_subject']) 
            ? sprintf($this->options['admin_notification_subject'] . ': %s', $data['name'])
            : sprintf(__('New Black Potential Pipeline Application: %s', 'black-potential-pipeline'), $data['name']);
        
        $message = $this->get_email_template('admin-notification', array(
            'name' => $data['name'],
            'email' => $data['email'],
            'industry' => $data['industry'],
            'job_title' => $data['job_title'],
            'years_experience' => $data['years_experience'],
            'admin_url' => admin_url('admin.php?page=bpp-new-applications'),
        ));
        
        // Send email
        return $this->send_email($this->get_admin_email(), $subject, $message);
    }

    /**
     * Send confirmation to applicant after submission.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant ID.
     * @return   bool                    True if email was sent, false otherwise.
     */
    public function send_application_confirmation($applicant_id) {
        if (!$this->is_notification_enabled() || !$this->is_applicant_notification_enabled()) {
            return false;
        }
        
        // Get applicant information
        $data = $this->get_applicant_data($applicant_id);
        if (empty($data['email']) || !is_email($data['email'])) {
            return false;
        }
        
        // Get template content
        $subject = isset($this->options['submission_subject']) 
            ? $this->options['submission_subject']
            : __('Thank You for Your Black Potential Pipeline Application', 'black-potential-pipeline');
        
        $message = $this->get_email_template('application-confirmation', array(
            'first_name' => $data['first_name'],
        ));
        
        // Send email
        return $this->send_email($data['email'], $subject, $message);
    }

    /**
     * Send approval notification to applicant.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant ID.
     * @return   bool                    True if email was sent, false otherwise.
     */
    public function send_approval_notification($applicant_id) {
        if (!$this->is_notification_enabled() || !$this->is_applicant_notification_enabled()) {
            return false;
        }
        
        // Get applicant information
        $data = $this->get_applicant_data($applicant_id);
        if (empty($data['email']) || !is_email($data['email'])) {
            return false;
        }
        
        // Get template content
        $subject = isset($this->options['approval_subject']) 
            ? $this->options['approval_subject']
            : __('Congratulations! Your Black Potential Pipeline Application Has Been Approved', 'black-potential-pipeline');
        
        $message = $this->get_email_template('application-approved', array(
            'first_name' => $data['first_name'],
            'profile_url' => get_permalink($applicant_id),
        ));
        
        // Send email
        return $this->send_email($data['email'], $subject, $message);
    }

    /**
     * Send rejection notification to applicant.
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant ID.
     * @return   bool                    True if email was sent, false otherwise.
     */
    public function send_rejection_notification($applicant_id) {
        if (!$this->is_notification_enabled() || !$this->is_applicant_notification_enabled()) {
            return false;
        }
        
        // Get applicant information
        $data = $this->get_applicant_data($applicant_id);
        if (empty($data['email']) || !is_email($data['email'])) {
            return false;
        }
        
        // Get rejection reason if provided
        $reason = get_post_meta($applicant_id, 'bpp_rejection_reason', true);
        
        // Get template content
        $subject = isset($this->options['rejection_subject']) 
            ? $this->options['rejection_subject']
            : __('Update on Your Black Potential Pipeline Application', 'black-potential-pipeline');
        
        $message = $this->get_email_template('application-rejected', array(
            'first_name' => $data['first_name'],
            'rejection_reason' => $reason,
        ));
        
        // Send email
        return $this->send_email($data['email'], $subject, $message);
    }

    /**
     * Get email template.
     *
     * @since    1.0.0
     * @param    string    $template_name    The template name.
     * @param    array     $args             Template arguments.
     * @return   string                      The email template content.
     */
    private function get_email_template($template_name, $args = array()) {
        // Look for template in theme directory first
        $theme_template = locate_template('bpp-templates/emails/' . $template_name . '.php');
        $template_file = $theme_template ? $theme_template : $this->get_templates_dir() . $template_name . '.php';
        
        // Default template if the specific one doesn't exist
        if (!file_exists($template_file)) {
            $template_file = $this->get_templates_dir() . 'default.php';
        }
        
        // Get template content
        ob_start();
        
        // Add common template variables
        $args = array_merge($args, array(
            'site_name' => get_bloginfo('name'),
            'admin_email' => $this->get_admin_email(),
            'header_image' => $this->get_header_image(),
            'footer_text' => $this->get_footer_text(),
            'primary_color' => $this->get_primary_color(),
        ));
        
        // Allow filtering of template arguments
        $args = apply_filters('bpp_email_template_args', $args, $template_name);
        
        // Extract variables for use in template
        extract($args);
        
        // Include template file
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            // Fallback if template file doesn't exist
            echo '<html><body><p>';
            echo isset($message) ? $message : __('Email content', 'black-potential-pipeline');
            echo '</p></body></html>';
        }
        
        $content = ob_get_clean();
        
        // Allow filtering of email content
        return apply_filters('bpp_email_content', $content, $template_name, $args);
    }

    /**
     * Send email with proper headers.
     *
     * @since    1.0.0
     * @param    string    $to         Recipient email address.
     * @param    string    $subject    Email subject.
     * @param    string    $message    Email message.
     * @return   bool                  True if email was sent, false otherwise.
     */
    private function send_email($to, $subject, $message) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->get_from_name() . ' <' . $this->get_from_email() . '>',
        );
        
        // Allow filtering of email headers
        $headers = apply_filters('bpp_email_headers', $headers, $subject);
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Fire action after email is sent
        if ($sent) {
            do_action('bpp_after_email_sent', $to, $subject, $message);
        }
        
        return $sent;
    }

    /**
     * Check if notifications are enabled.
     *
     * @since    1.0.0
     * @return   bool    True if enabled, false otherwise.
     */
    private function is_notification_enabled() {
        return isset($this->options['enabled']) && $this->options['enabled'];
    }

    /**
     * Check if applicant notifications are enabled.
     *
     * @since    1.0.0
     * @return   bool    True if enabled, false otherwise.
     */
    private function is_applicant_notification_enabled() {
        return isset($this->options['applicant_notify']) && $this->options['applicant_notify'];
    }

    /**
     * Get admin email.
     *
     * @since    1.0.0
     * @return   string    The admin email.
     */
    private function get_admin_email() {
        return isset($this->options['admin_email']) ? $this->options['admin_email'] : get_option('admin_email');
    }

    /**
     * Get from name.
     *
     * @since    1.0.0
     * @return   string    The from name.
     */
    private function get_from_name() {
        return isset($this->options['from_name']) ? $this->options['from_name'] : get_bloginfo('name');
    }

    /**
     * Get from email.
     *
     * @since    1.0.0
     * @return   string    The from email.
     */
    private function get_from_email() {
        return isset($this->options['from_email']) ? $this->options['from_email'] : get_option('admin_email');
    }

    /**
     * Get header image.
     *
     * @since    1.0.0
     * @return   string    The header image URL.
     */
    private function get_header_image() {
        return isset($this->options['email_header_image']) ? $this->options['email_header_image'] : '';
    }

    /**
     * Get footer text.
     *
     * @since    1.0.0
     * @return   string    The footer text.
     */
    private function get_footer_text() {
        return isset($this->options['email_footer_text']) 
            ? $this->options['email_footer_text'] 
            : sprintf(__('© %s Black Potential Pipeline', 'black-potential-pipeline'), date('Y'));
    }

    /**
     * Get primary color.
     *
     * @since    1.0.0
     * @return   string    The primary color.
     */
    private function get_primary_color() {
        return isset($this->options['email_primary_color']) ? $this->options['email_primary_color'] : '#1e8449';
    }

    /**
     * Get applicant data for email templates
     *
     * @since    1.0.0
     * @param    int    $applicant_id    The applicant post ID
     * @return   array                   Applicant data
     */
    private function get_applicant_data($applicant_id) {
        // Get applicant post
        $applicant = get_post($applicant_id);
        
        if (!$applicant) {
            error_log('Email Manager: Applicant post not found for ID: ' . $applicant_id);
            return array();
        }
        
        // Get applicant meta
        $first_name = get_post_meta($applicant_id, 'first_name', true);
        $last_name = get_post_meta($applicant_id, 'last_name', true);
        $email = get_post_meta($applicant_id, 'email', true);
        $phone = get_post_meta($applicant_id, 'phone', true);
        $job_title = get_post_meta($applicant_id, 'job_title', true);
        $industry = get_post_meta($applicant_id, 'industry', true);
        $years_experience = get_post_meta($applicant_id, 'years_experience', true);
        $skills = get_post_meta($applicant_id, 'skills', true);
        $website = get_post_meta($applicant_id, 'website', true);
        $linkedin = get_post_meta($applicant_id, 'linkedin', true);
        $cover_letter = get_post_meta($applicant_id, 'cover_letter', true);
        
        // Get resume attachment
        $resume_id = get_post_meta($applicant_id, 'resume_id', true);
        $resume_url = '';
        $resume_filename = '';
        
        if ($resume_id) {
            // Check if resume_id is a WP_Error object
            if (is_wp_error($resume_id)) {
                error_log('Email Manager: Resume ID is a WP_Error: ' . $resume_id->get_error_message());
                $resume_url = '';
                $resume_filename = '';
            } else {
                $resume_url = wp_get_attachment_url($resume_id);
                $resume_filename = basename(get_attached_file($resume_id));
                
                // Check if we got valid results
                if (!$resume_url) {
                    error_log('Email Manager: Could not get resume URL for attachment ID: ' . $resume_id);
                    $resume_url = '';
                }
                
                if (!$resume_filename) {
                    error_log('Email Manager: Could not get resume filename for attachment ID: ' . $resume_id);
                    $resume_filename = '';
                }
            }
        }
        
        // Get photo attachment
        $photo_id = get_post_meta($applicant_id, 'photo_id', true);
        $photo_url = '';
        
        if ($photo_id) {
            // Check if photo_id is a WP_Error object
            if (is_wp_error($photo_id)) {
                error_log('Email Manager: Photo ID is a WP_Error: ' . $photo_id->get_error_message());
                $photo_url = '';
            } else {
                $photo_url = wp_get_attachment_url($photo_id);
                
                // Check if we got a valid URL
                if (!$photo_url) {
                    error_log('Email Manager: Could not get photo URL for attachment ID: ' . $photo_id);
                    $photo_url = '';
                }
            }
        }
        
        // Get industry label
        $industry_label = $industry;
        $industries = array(
            'climate-science' => __('Climate Science', 'black-potential-pipeline'),
            'environmental-policy' => __('Environmental Policy', 'black-potential-pipeline'),
            'renewable-energy' => __('Renewable Energy', 'black-potential-pipeline'),
            'sustainable-agriculture' => __('Sustainable Agriculture', 'black-potential-pipeline'),
            'conservation' => __('Conservation', 'black-potential-pipeline'),
            'environmental-justice' => __('Environmental Justice', 'black-potential-pipeline'),
            'green-building' => __('Green Building', 'black-potential-pipeline'),
            'waste-management' => __('Waste Management', 'black-potential-pipeline'),
            'water-resources' => __('Water Resources', 'black-potential-pipeline'),
            'nature-based-work' => __('Nature-Based Work', 'black-potential-pipeline'),
            'other' => __('Other', 'black-potential-pipeline')
        );
        
        if (isset($industries[$industry])) {
            $industry_label = $industries[$industry];
        }
        
        // Build applicant data array
        $applicant_data = array(
            'id' => $applicant_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'full_name' => $first_name . ' ' . $last_name,
            'email' => $email,
            'phone' => $phone,
            'job_title' => $job_title,
            'industry' => $industry,
            'industry_label' => $industry_label,
            'years_experience' => $years_experience,
            'skills' => $skills,
            'website' => $website,
            'linkedin' => $linkedin,
            'cover_letter' => $cover_letter,
            'resume_url' => $resume_url,
            'resume_filename' => $resume_filename,
            'photo_url' => $photo_url,
            'submission_date' => get_the_date('F j, Y', $applicant_id),
            'admin_url' => admin_url('post.php?post=' . $applicant_id . '&action=edit')
        );
        
        return $applicant_data;
    }
} 