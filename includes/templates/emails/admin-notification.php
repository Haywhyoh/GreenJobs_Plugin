<?php
/**
 * Admin notification email template
 *
 * This template is used to notify admins of new applications.
 *
 * @link       https://example.com
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
// $name - Applicant's full name
// $email - Applicant's email
// $industry - Applicant's industry
// $job_title - Applicant's job title
// $years_experience - Applicant's years of experience
// $admin_url - URL to the admin applications page
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $site_name; ?> - New Application</title>
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
        .applicant-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
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
            <h2>New Applicant Submission</h2>
            
            <p>A new application has been submitted to the Black Potential Pipeline directory and is awaiting your review.</p>
            
            <div class="applicant-info">
                <p><strong>Name:</strong> <?php echo $name; ?></p>
                <p><strong>Email:</strong> <?php echo $email; ?></p>
                <p><strong>Industry:</strong> <?php echo $industry; ?></p>
                <p><strong>Job Title:</strong> <?php echo $job_title; ?></p>
                <p><strong>Years of Experience:</strong> <?php echo $years_experience; ?></p>
            </div>
            
            <p>Please review this application at your earliest convenience to either approve or reject it.</p>
            
            <p><a href="<?php echo $admin_url; ?>" class="button" style="color: #ffffff;">Review Application</a></p>
            
            <p>This is an automated email notification. Please do not reply directly to this message.</p>
        </div>
        <div class="footer">
            <p><?php echo $footer_text; ?></p>
        </div>
    </div>
</body>
</html> 