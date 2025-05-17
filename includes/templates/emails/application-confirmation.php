<?php
/**
 * Application confirmation email template
 *
 * This template is used when a new applicant submits an application.
 *
 * @link       https://codemygig.com,
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes/templates/emails
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Variables available:
// $site_name - The name of the site
// $header_image - Header image URL
// $admin_email - Admin email address
// $footer_text - Footer text
// $primary_color - Primary color
// $first_name - Applicant's first name
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $site_name; ?> - Application Received</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid <?php echo $primary_color; ?>;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 30px 20px;
            background-color: #ffffff;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666666;
            border-top: 1px solid #dddddd;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            background-color: <?php echo $primary_color; ?>;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .highlight {
            color: <?php echo $primary_color; ?>;
            font-weight: bold;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
            }
            .content {
                padding: 20px 10px !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <?php if (!empty($header_image)) : ?>
                <img src="<?php echo $header_image; ?>" alt="<?php echo $site_name; ?>" />
            <?php else : ?>
                <h1 style="color: <?php echo $primary_color; ?>;">Black Potential Pipeline</h1>
            <?php endif; ?>
        </div>
        <div class="content">
            <h2>Application Received</h2>
            
            <p>Dear <?php echo !empty($first_name) ? $first_name : 'Applicant'; ?>,</p>
            
            <p>Thank you for submitting your application to the <span class="highlight">Black Potential Pipeline</span>. We've received your information and it's now in our review queue.</p>
            
            <p>What happens next:</p>
            
            <ol>
                <li><strong>Review Process:</strong> Our team will carefully review your application within the next 5-7 business days.</li>
                <li><strong>Approval Decision:</strong> You'll receive an email notification about the status of your application.</li>
                <li><strong>Public Directory:</strong> If approved, your profile will be featured in our directory, making you discoverable by organizations seeking diverse talent in green industries.</li>
            </ol>
            
            <p>While you wait, we encourage you to:</p>
            
            <ul>
                <li>Make sure your resume and LinkedIn profile are up to date</li>
                <li>Explore our resources for professionals in green industries</li>
                <li>Connect with us on social media for updates and opportunities</li>
            </ul>
            
            <p>If you need to update any information in your application or have any questions, please don't hesitate to contact us at <a href="mailto:<?php echo $admin_email; ?>"><?php echo $admin_email; ?></a>.</p>
            
            <p>We appreciate your interest in joining the Black Potential Pipeline and are excited about the possibility of featuring your profile in our directory.</p>
            
            <p>Best regards,<br>
            The Black Potential Pipeline Team</p>
        </div>
        <div class="footer">
            <p><?php echo $footer_text; ?></p>
            <p>If you have any questions, please contact us at <a href="mailto:<?php echo $admin_email; ?>"><?php echo $admin_email; ?></a></p>
        </div>
    </div>
</body>
</html> 