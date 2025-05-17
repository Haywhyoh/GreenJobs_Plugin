<?php
/**
 * Application rejected email template
 *
 * This template is used when an application is rejected.
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
// $rejection_reason - Reason for rejection (if provided)
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $site_name; ?> - Application Update</title>
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
            <h2>Update on Your Application</h2>
            
            <p>Dear <?php echo $first_name; ?>,</p>
            
            <p>Thank you for your interest in being listed in the Black Potential Pipeline directory. After careful review, we regret to inform you that we are unable to include your profile in our directory at this time.</p>
            
            <?php if (!empty($rejection_reason)): ?>
            <p><strong>Feedback:</strong> <?php echo $rejection_reason; ?></p>
            <?php endif; ?>
            
            <p>Some common reasons applications may not be approved include:</p>
            <ul>
                <li>Missing or incomplete required information</li>
                <li>Application does not meet our current industry focus criteria</li>
                <li>Experience level does not meet minimum requirements</li>
                <li>Issues with uploaded documents (format, quality, etc.)</li>
            </ul>
            
            <p>You are welcome to submit a new application in the future with updated information. If you believe there has been an error in our decision, please don't hesitate to contact us at <?php echo $admin_email; ?> for clarification.</p>
            
            <p>We appreciate your understanding and wish you all the best in your professional endeavors.</p>
            
            <p>Best regards,<br>
            The <?php echo $site_name; ?> Team</p>
        </div>
        <div class="footer">
            <p><?php echo $footer_text; ?></p>
            <p>If you have any questions, please contact us at <?php echo $admin_email; ?></p>
        </div>
    </div>
</body>
</html> 