# Email Notification System

The Black Potential Pipeline plugin includes a comprehensive email notification system that keeps both administrators and applicants informed throughout the application process.

## Notification Types

The system sends emails for the following events:

1. **Application Submission**: When an applicant submits their application:
   - The applicant receives a confirmation email thanking them for their submission
   - The administrator receives a notification about the new application

2. **Application Approval**: When an administrator approves an application:
   - The applicant receives a congratulatory email with a link to their public profile

3. **Application Rejection**: When an administrator rejects an application:
   - The applicant receives a notification explaining that their application was not accepted
   - The email can include a personalized reason for the rejection (if provided by the admin)

## Configuration Options

The email notification system can be configured through the plugin settings page. The following options are available:

- **Enable/Disable Notifications**: Turn on or off all email notifications
- **Admin Email**: Set the email address where admin notifications are sent
- **Enable/Disable Applicant Notifications**: Choose whether to send emails to applicants
- **From Name**: Customize the sender name for all emails
- **From Email**: Set the sender email address
- **Header Image**: Add a custom logo/header image to all emails
- **Footer Text**: Customize the footer text displayed in all emails
- **Primary Color**: Set the primary color used in email templates
- **Email Subjects**: Customize the subject lines for different email types

## Email Templates

The plugin includes customizable email templates for each notification type:

1. **Application Confirmation Template** (`application-confirmation.php`):
   - Thanks the applicant for their submission
   - Explains the review process and expected timeline
   - Provides contact information for inquiries

2. **Application Approved Template** (`application-approved.php`):
   - Congratulates the applicant on their approval
   - Provides a link to their public profile
   - Explains the benefits of being in the directory
   - Encourages sharing their profile link

3. **Application Rejected Template** (`application-rejected.php`):
   - Tactfully informs the applicant that their application was not accepted
   - Includes the rejection reason if provided
   - Lists common reasons for rejection
   - Invites the applicant to reapply in the future

4. **Admin Notification Template** (`admin-notification.php`):
   - Alerts the administrator about a new application
   - Includes key applicant information (name, email, industry, etc.)
   - Provides a direct link to review the application

## Customizing Email Templates

You can customize the email templates by:

1. Copying the template files from `includes/templates/emails/` to your theme in a directory called `bpp-templates/emails/`
2. Editing the copied files to match your desired styling and content

The plugin will first look for templates in your theme directory before using the default templates.

## Technical Implementation

The email functionality is managed by the `BPP_Email_Manager` class, which:

- Handles all email sending operations
- Loads and parses the appropriate templates
- Populates templates with dynamic data
- Manages email settings
- Ensures proper email formatting

The email manager is integrated with:

- The form handler (to send notifications on submission)
- The admin AJAX handler (to send notifications on approval/rejection)

## Hooks and Filters

Developers can customize the email functionality using the following hooks:

```php
// Change email content before sending
add_filter('bpp_email_content', 'my_custom_email_content', 10, 3);
function my_custom_email_content($content, $template_name, $args) {
    // Modify $content based on template name or arguments
    return $content;
}

// Change email headers
add_filter('bpp_email_headers', 'my_custom_email_headers', 10, 2);
function my_custom_email_headers($headers, $template_name) {
    // Add or modify headers
    return $headers;
}

// Trigger additional actions when emails are sent
add_action('bpp_after_email_sent', 'my_custom_email_action', 10, 3);
function my_custom_email_action($to, $subject, $template_name) {
    // Perform additional actions after email is sent
}
```

## Troubleshooting

If emails are not being delivered:

1. Verify that the email notifications are enabled in the plugin settings
2. Check that WordPress is properly configured to send emails
3. Consider using an SMTP plugin to improve email deliverability
4. Check your server's mail logs for any errors
5. Test with a different email address

For more advanced customization or issues, please refer to the developer documentation or contact support. 