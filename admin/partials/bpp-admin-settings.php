<?php
/**
 * Provide a admin area view for managing plugin settings
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap bpp-admin-settings">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-generic"></span>
        <?php echo esc_html__('Black Potential Pipeline Settings', 'black-potential-pipeline'); ?>
    </h1>
    
    <div class="bpp-admin-header">
        <p class="bpp-description">
            <?php echo esc_html__('Configure the settings for the Black Potential Pipeline plugin.', 'black-potential-pipeline'); ?>
        </p>
    </div>
    
    <div class="bpp-settings-container">
        <div class="bpp-settings-tabs">
            <a href="#email-settings" class="active"><?php echo esc_html__('Email Notifications', 'black-potential-pipeline'); ?></a>
            <a href="#form-settings"><?php echo esc_html__('Form Fields', 'black-potential-pipeline'); ?></a>
            <a href="#directory-settings"><?php echo esc_html__('Directory Display', 'black-potential-pipeline'); ?></a>
            <a href="#workflow-settings"><?php echo esc_html__('Approval Workflow', 'black-potential-pipeline'); ?></a>
        </div>
        
        <form method="post" action="options.php" class="bpp-settings-form">
            <?php settings_fields('bpp_options'); ?>
            
            <!-- Email Notification Settings -->
            <div id="email-settings" class="bpp-settings-tab-content active">
                <h2><?php echo esc_html__('Email Notification Settings', 'black-potential-pipeline'); ?></h2>
                <p class="bpp-settings-description">
                    <?php echo esc_html__('Configure email notification settings for the Black Potential Pipeline.', 'black-potential-pipeline'); ?>
                </p>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Admin Email', 'black-potential-pipeline'); ?></th>
                        <td>
                            <?php
                            $options = get_option('bpp_email_notifications');
                            $email = isset($options['admin_email']) ? $options['admin_email'] : get_option('admin_email');
                            ?>
                            <input type="email" id="bpp_admin_email" name="bpp_email_notifications[admin_email]" value="<?php echo esc_attr($email); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html__('Email address for receiving notifications about new applications.', 'black-potential-pipeline'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Enable Notifications', 'black-potential-pipeline'); ?></th>
                        <td>
                            <?php $enabled = isset($options['enabled']) ? $options['enabled'] : 1; ?>
                            <label>
                                <input type="checkbox" id="bpp_notification_enabled" name="bpp_email_notifications[enabled]" value="1" <?php checked(1, $enabled); ?> />
                                <?php echo esc_html__('Send email notifications for new submissions', 'black-potential-pipeline'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Applicant Notifications', 'black-potential-pipeline'); ?></th>
                        <td>
                            <?php 
                            $applicant_notify = isset($options['applicant_notify']) ? $options['applicant_notify'] : 1;
                            ?>
                            <label>
                                <input type="checkbox" id="bpp_applicant_notify" name="bpp_email_notifications[applicant_notify]" value="1" <?php checked(1, $applicant_notify); ?> />
                                <?php echo esc_html__('Send email notifications to applicants about application status', 'black-potential-pipeline'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Form Field Settings -->
            <div id="form-settings" class="bpp-settings-tab-content">
                <h2><?php echo esc_html__('Form Field Settings', 'black-potential-pipeline'); ?></h2>
                <p class="bpp-settings-description">
                    <?php echo esc_html__('Configure submission form fields and requirements.', 'black-potential-pipeline'); ?>
                </p>
                
                <?php 
                $form_fields = get_option('bpp_form_fields', array());
                $default_fields = array(
                    'first_name' => array('label' => __('First Name', 'black-potential-pipeline'), 'required' => true, 'enabled' => true),
                    'last_name' => array('label' => __('Last Name', 'black-potential-pipeline'), 'required' => true, 'enabled' => true),
                    'email' => array('label' => __('Email Address', 'black-potential-pipeline'), 'required' => true, 'enabled' => true),
                    'phone' => array('label' => __('Phone Number', 'black-potential-pipeline'), 'required' => false, 'enabled' => true),
                    'linkedin' => array('label' => __('LinkedIn Profile', 'black-potential-pipeline'), 'required' => false, 'enabled' => true),
                    'location' => array('label' => __('Location', 'black-potential-pipeline'), 'required' => false, 'enabled' => true),
                    'industry' => array('label' => __('Industry Category', 'black-potential-pipeline'), 'required' => true, 'enabled' => true),
                    'job_title' => array('label' => __('Current Job Title', 'black-potential-pipeline'), 'required' => false, 'enabled' => true),
                    'years_experience' => array('label' => __('Years of Experience', 'black-potential-pipeline'), 'required' => false, 'enabled' => true),
                    'job_type' => array('label' => __('Preferred Job Type', 'black-potential-pipeline'), 'required' => false, 'enabled' => true),
                    'skills' => array('label' => __('Skills & Expertise', 'black-potential-pipeline'), 'required' => false, 'enabled' => true),
                    'cover_letter' => array('label' => __('Cover Letter', 'black-potential-pipeline'), 'required' => true, 'enabled' => true),
                    'resume' => array('label' => __('Resume/CV', 'black-potential-pipeline'), 'required' => true, 'enabled' => true),
                    'photo' => array('label' => __('Professional Photo', 'black-potential-pipeline'), 'required' => false, 'enabled' => true),
                    'consent' => array('label' => __('Consent for Public Display', 'black-potential-pipeline'), 'required' => true, 'enabled' => true),
                );
                
                // Merge default fields with saved fields
                $fields = array_merge($default_fields, $form_fields);
                ?>
                
                <div class="bpp-form-fields-container">
                    <input type="hidden" id="bpp-field-order" name="bpp_form_fields[field_order]" value="<?php echo esc_attr(isset($form_fields['field_order']) ? $form_fields['field_order'] : ''); ?>" />
                    
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th width="10"><?php echo esc_html__('Order', 'black-potential-pipeline'); ?></th>
                                <th><?php echo esc_html__('Field', 'black-potential-pipeline'); ?></th>
                                <th width="100"><?php echo esc_html__('Required', 'black-potential-pipeline'); ?></th>
                                <th width="100"><?php echo esc_html__('Display', 'black-potential-pipeline'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fields as $field_id => $field) : 
                                if ($field_id === 'field_order') continue;
                                
                                $enabled = isset($field['enabled']) ? $field['enabled'] : true;
                                $required = isset($field['required']) ? $field['required'] : false;
                                $label = isset($field['label']) ? $field['label'] : $field_id;
                            ?>
                                <tr class="bpp-form-field-row <?php echo !$enabled ? 'bpp-field-disabled' : ''; ?>" data-field-id="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>-row">
                                    <td>
                                        <span class="bpp-drag-handle dashicons dashicons-menu"></span>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($label); ?></strong>
                                        <input type="hidden" name="bpp_form_fields[<?php echo esc_attr($field_id); ?>][label]" value="<?php echo esc_attr($label); ?>" />
                                        <br>
                                        <span id="<?php echo esc_attr($field_id); ?>-required-status" class="bpp-field-status <?php echo $required ? 'bpp-required' : ''; ?>">
                                            <?php echo $required ? esc_html__('Required', 'black-potential-pipeline') : esc_html__('Optional', 'black-potential-pipeline'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="checkbox" 
                                               id="<?php echo esc_attr($field_id); ?>-required" 
                                               name="bpp_form_fields[<?php echo esc_attr($field_id); ?>][required]" 
                                               value="1" 
                                               <?php checked(true, $required); ?> 
                                               <?php disabled(false, $enabled); ?>
                                               class="bpp-toggle-required"
                                               data-field="<?php echo esc_attr($field_id); ?>" />
                                    </td>
                                    <td>
                                        <input type="checkbox" 
                                               id="<?php echo esc_attr($field_id); ?>-enabled"
                                               name="bpp_form_fields[<?php echo esc_attr($field_id); ?>][enabled]" 
                                               value="1" 
                                               <?php checked(true, $enabled); ?> 
                                               class="bpp-toggle-field"
                                               data-field="<?php echo esc_attr($field_id); ?>" />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Directory Display Settings -->
            <div id="directory-settings" class="bpp-settings-tab-content">
                <h2><?php echo esc_html__('Directory Display Settings', 'black-potential-pipeline'); ?></h2>
                <p class="bpp-settings-description">
                    <?php echo esc_html__('Configure how the professional directory displays on your site.', 'black-potential-pipeline'); ?>
                </p>
                
                <?php 
                $directory_settings = get_option('bpp_directory_settings', array());
                $default_per_page = isset($directory_settings['per_page']) ? $directory_settings['per_page'] : 12;
                $default_layout = isset($directory_settings['default_layout']) ? $directory_settings['default_layout'] : 'grid';
                $featured_count = isset($directory_settings['featured_count']) ? $directory_settings['featured_count'] : 4;
                ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Profiles Per Page', 'black-potential-pipeline'); ?></th>
                        <td>
                            <input type="number" name="bpp_directory_settings[per_page]" value="<?php echo esc_attr($default_per_page); ?>" min="4" max="48" step="4" />
                            <p class="description"><?php echo esc_html__('Number of profiles to display per page in the directory.', 'black-potential-pipeline'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Default Layout', 'black-potential-pipeline'); ?></th>
                        <td>
                            <select name="bpp_directory_settings[default_layout]">
                                <option value="grid" <?php selected('grid', $default_layout); ?>><?php echo esc_html__('Grid', 'black-potential-pipeline'); ?></option>
                                <option value="list" <?php selected('list', $default_layout); ?>><?php echo esc_html__('List', 'black-potential-pipeline'); ?></option>
                            </select>
                            <p class="description"><?php echo esc_html__('The default layout for displaying professionals in the directory.', 'black-potential-pipeline'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Featured Professionals Count', 'black-potential-pipeline'); ?></th>
                        <td>
                            <input type="number" name="bpp_directory_settings[featured_count]" value="<?php echo esc_attr($featured_count); ?>" min="1" max="12" />
                            <p class="description"><?php echo esc_html__('Number of professionals to display in the featured section.', 'black-potential-pipeline'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h3><?php echo esc_html__('Profile Visibility Settings', 'black-potential-pipeline'); ?></h3>
                <p class="bpp-settings-description">
                    <?php echo esc_html__('Control which information is visible on public profile pages.', 'black-potential-pipeline'); ?>
                </p>
                
                <?php
                // Get profile visibility settings with defaults
                $visibility_settings = isset($directory_settings['profile_visibility']) ? $directory_settings['profile_visibility'] : array();
                $default_visibility = array(
                    'photo' => true,
                    'job_title' => true,
                    'industry' => true,
                    'location' => true,
                    'years_experience' => true,
                    'skills' => true,
                    'bio' => true,
                    'website' => true,
                    'linkedin' => true,
                    'email' => true,
                    'phone' => false,
                    'resume' => true
                );
                // Merge with defaults
                $visibility = array_merge($default_visibility, $visibility_settings);
                ?>
                
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Field', 'black-potential-pipeline'); ?></th>
                            <th width="100"><?php echo esc_html__('Publicly Visible', 'black-potential-pipeline'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php echo esc_html__('Profile Photo', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_photo" name="bpp_directory_settings[profile_visibility][photo]" value="1" <?php checked(true, $visibility['photo']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Job Title', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_job_title" name="bpp_directory_settings[profile_visibility][job_title]" value="1" <?php checked(true, $visibility['job_title']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Industry', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_industry" name="bpp_directory_settings[profile_visibility][industry]" value="1" <?php checked(true, $visibility['industry']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Location', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_location" name="bpp_directory_settings[profile_visibility][location]" value="1" <?php checked(true, $visibility['location']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Years of Experience', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_years_experience" name="bpp_directory_settings[profile_visibility][years_experience]" value="1" <?php checked(true, $visibility['years_experience']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Skills & Expertise', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_skills" name="bpp_directory_settings[profile_visibility][skills]" value="1" <?php checked(true, $visibility['skills']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Professional Bio', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_bio" name="bpp_directory_settings[profile_visibility][bio]" value="1" <?php checked(true, $visibility['bio']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Website', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_website" name="bpp_directory_settings[profile_visibility][website]" value="1" <?php checked(true, $visibility['website']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('LinkedIn Profile', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_linkedin" name="bpp_directory_settings[profile_visibility][linkedin]" value="1" <?php checked(true, $visibility['linkedin']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Email Address', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_email" name="bpp_directory_settings[profile_visibility][email]" value="1" <?php checked(true, $visibility['email']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Phone Number', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_phone" name="bpp_directory_settings[profile_visibility][phone]" value="1" <?php checked(true, $visibility['phone']); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo esc_html__('Resume/CV Download', 'black-potential-pipeline'); ?></strong></td>
                            <td>
                                <input type="checkbox" id="visibility_resume" name="bpp_directory_settings[profile_visibility][resume]" value="1" <?php checked(true, $visibility['resume']); ?>>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Approval Workflow Settings -->
            <div id="workflow-settings" class="bpp-settings-tab-content">
                <h2><?php echo esc_html__('Approval Workflow Settings', 'black-potential-pipeline'); ?></h2>
                <p class="bpp-settings-description">
                    <?php echo esc_html__('Configure the approval workflow and notifications.', 'black-potential-pipeline'); ?>
                </p>
                
                <?php 
                $workflow_settings = get_option('bpp_approval_workflow', array());
                $auto_approve = isset($workflow_settings['auto_approve']) ? $workflow_settings['auto_approve'] : false;
                $approval_roles = isset($workflow_settings['roles']) ? $workflow_settings['roles'] : array('administrator');
                ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Auto-Approve Applications', 'black-potential-pipeline'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="bpp_approval_workflow[auto_approve]" value="1" <?php checked(true, $auto_approve); ?> />
                                <?php echo esc_html__('Automatically approve all new applications (not recommended)', 'black-potential-pipeline'); ?>
                            </label>
                            <p class="description"><?php echo esc_html__('If enabled, new applications will automatically be approved and displayed in the directory.', 'black-potential-pipeline'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Approval Roles', 'black-potential-pipeline'); ?></th>
                        <td>
                            <?php 
                            $roles = get_editable_roles();
                            foreach ($roles as $role_id => $role) : 
                                $checked = in_array($role_id, $approval_roles);
                            ?>
                                <label>
                                    <input type="checkbox" name="bpp_approval_workflow[roles][]" value="<?php echo esc_attr($role_id); ?>" <?php checked(true, $checked); ?> />
                                    <?php echo esc_html($role['name']); ?>
                                </label>
                                <br>
                            <?php endforeach; ?>
                            <p class="description"><?php echo esc_html__('User roles that can approve or reject applications.', 'black-potential-pipeline'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php submit_button(); ?>
        </form>
    </div>
</div>

<style>
/* Settings Tabs */
.bpp-settings-tabs {
    margin: 20px 0;
    border-bottom: 1px solid #ccc;
    padding-bottom: 0;
}

.bpp-settings-tabs a {
    display: inline-block;
    padding: 10px 15px;
    margin-right: 5px;
    border: 1px solid #ccc;
    border-bottom: none;
    background: #f1f1f1;
    text-decoration: none;
    border-radius: 3px 3px 0 0;
    color: #444;
    font-weight: 500;
}

.bpp-settings-tabs a:hover {
    background: #fff;
}

.bpp-settings-tabs a.active {
    background: #fff;
    border-bottom: 1px solid #fff;
    margin-bottom: -1px;
    color: #000;
}

.bpp-settings-tab-content {
    display: none;
    padding: 20px;
    background: #fff;
    border: 1px solid #ccc;
    border-top: none;
    margin-bottom: 20px;
}

.bpp-settings-tab-content.active {
    display: block;
}

/* Form field table */
.bpp-drag-handle {
    cursor: move;
    color: #aaa;
}

.bpp-field-status {
    font-size: 11px;
    border-radius: 3px;
    padding: 2px 5px;
    background: #f1f1f1;
    color: #666;
}

.bpp-field-status.bpp-required {
    background: #d54e21;
    color: #fff;
}

.bpp-field-disabled {
    opacity: 0.5;
}

/* Settings descriptions */
.bpp-settings-description {
    margin-bottom: 20px;
    font-style: italic;
}
</style> 