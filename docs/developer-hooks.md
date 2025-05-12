# Developer Hooks and Filters

The Black Potential Pipeline plugin provides various hooks and filters that developers can use to customize or extend the plugin's functionality.

## Email Notification System

The email notification system can be customized using the following hooks and filters:

### Email Content Filter

The `bpp_email_content` filter allows you to modify the content of any email before it's sent.

```php
/**
 * Modify email content before sending
 *
 * @param string $content       The email content HTML
 * @param string $template_name The name of the template being used
 * @param array  $args          The arguments passed to the template
 *
 * @return string Modified email content
 */
function my_custom_email_content($content, $template_name, $args) {
    // Modify $content based on template name or arguments
    if ($template_name === 'application-approved') {
        // Add a special promotional message to approval emails
        $content = str_replace('</div></body>', '<div class="promo">Check out our upcoming webinar!</div></div></body>', $content);
    }
    
    return $content;
}
add_filter('bpp_email_content', 'my_custom_email_content', 10, 3);
```

### Email Template Arguments Filter

The `bpp_email_template_args` filter allows you to modify the arguments passed to an email template.

```php
/**
 * Modify email template arguments
 *
 * @param array  $args          The arguments passed to the template
 * @param string $template_name The name of the template being used
 *
 * @return array Modified template arguments
 */
function my_custom_email_args($args, $template_name) {
    // Add additional variables to all templates
    $args['company_phone'] = '(555) 123-4567';
    
    // Add template-specific variables
    if ($template_name === 'application-confirmation') {
        $args['expected_response_time'] = '3-5 business days';
    }
    
    return $args;
}
add_filter('bpp_email_template_args', 'my_custom_email_args', 10, 2);
```

### Email Headers Filter

The `bpp_email_headers` filter allows you to modify the email headers.

```php
/**
 * Modify email headers
 *
 * @param array  $headers       The email headers
 * @param string $template_name The name of the template being used
 *
 * @return array Modified email headers
 */
function my_custom_email_headers($headers, $subject) {
    // Add CC or BCC headers
    $headers[] = 'Cc: records@example.com';
    
    // Or add reply-to header
    $headers[] = 'Reply-To: support@example.com';
    
    return $headers;
}
add_filter('bpp_email_headers', 'my_custom_email_headers', 10, 2);
```

### After Email Sent Action

The `bpp_after_email_sent` action allows you to perform additional actions after an email is sent.

```php
/**
 * Perform actions after an email is sent
 *
 * @param string $to            The recipient email address
 * @param string $subject       The email subject
 * @param string $message       The email message
 */
function my_after_email_action($to, $subject, $message) {
    // Log emails to a file
    $log = date('[Y-m-d H:i:s]') . " Email sent to: $to with subject: $subject\n";
    file_put_contents(WP_CONTENT_DIR . '/email-log.txt', $log, FILE_APPEND);
    
    // Or trigger another notification
    if (strpos($subject, 'Approved') !== false) {
        // Notify the team about an approval
        wp_mail('team@example.com', 'New approval notification', 'A new applicant was approved.');
    }
}
add_action('bpp_after_email_sent', 'my_after_email_action', 10, 3);
```

## Email Template Customization

You can override the default email templates by creating custom templates in your theme:

1. Create a `bpp-templates/emails/` directory in your theme
2. Copy the desired template file from the plugin's `includes/templates/emails/` directory
3. Modify the template file as needed

The plugin will automatically use your custom template instead of the default one.

Available templates:
- `application-confirmation.php` - Sent to applicants after they submit their application
- `application-approved.php` - Sent to applicants when their application is approved
- `application-rejected.php` - Sent to applicants when their application is rejected
- `admin-notification.php` - Sent to administrators when a new application is submitted
- `default.php` - Used as a fallback when a specific template is not found

## Additional Customization

For more advanced customization, you can extend the `BPP_Email_Manager` class and override its methods:

```php
class My_Custom_Email_Manager extends BPP_Email_Manager {
    /**
     * Override the get_from_name method to use a different name
     */
    protected function get_from_name() {
        return 'Custom Pipeline Team';
    }
    
    /**
     * Override the get_primary_color method to use a different color
     */
    protected function get_primary_color() {
        return '#007bff'; // Use a blue color instead of green
    }
}

// Replace the default email manager with our custom one
function my_custom_email_manager($plugin_name, $version) {
    return new My_Custom_Email_Manager($plugin_name, $version);
}
add_filter('bpp_email_manager_instance', 'my_custom_email_manager', 10, 2);
```

Note: The `bpp_email_manager_instance` filter needs to be added to the plugin by implementing it in the main plugin class.

## Example: Adding Custom Email Templates

To add a completely new email template:

1. Create a new template file in your theme's `bpp-templates/emails/` directory
2. Add a method to send the email using the new template:

```php
function send_custom_notification($user_id) {
    $email_manager = new BPP_Email_Manager('black-potential-pipeline', '1.0.0');
    
    // Get user data
    $user = get_user_by('id', $user_id);
    if (!$user) return false;
    
    // Get template content
    $subject = 'Custom Notification';
    $message = $email_manager->get_email_template('my-custom-template', array(
        'user_name' => $user->display_name,
        'custom_data' => 'Your custom data here',
    ));
    
    // Send email
    return $email_manager->send_email($user->user_email, $subject, $message);
}
```

This level of customization allows for complete control over the email notification system while maintaining compatibility with future plugin updates. 