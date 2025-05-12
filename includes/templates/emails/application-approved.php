<?php
/**
 * Application approved email template
 *
 * This template is used when an application is approved.
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
// $first_name - Applicant's first name
// $profile_url - URL to the applicant's public profile
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $site_name; ?> - Application Approved</title>
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
            <h2>Congratulations! Your Profile is Now Live</h2>
            
            <p>Dear <?php echo $first_name; ?>,</p>
            
            <p>Great news! Your application to the Black Potential Pipeline has been <strong>approved</strong>, and your professional profile is now live in our public directory.</p>
            
            <p>What this means for you:</p>
            
            <ul>
                <li>Your profile is now visible to organizations looking for talented Black professionals in green sectors</li>
                <li>You're now part of an exclusive network focused on increasing diversity in environmental fields</li>
                <li>You may be contacted directly by employers interested in your skills and experience</li>
            </ul>
            
            <p><a href="<?php echo $profile_url; ?>" class="button" style="color: #ffffff;">View Your Public Profile</a></p>
            
            <p>We encourage you to share your profile link with your network and on your social media channels to maximize your visibility to potential employers.</p>
            
            <p>If you need to update any information on your profile, please contact us at <?php echo $admin_email; ?>.</p>
            
            <p>We're excited to have you as part of our community!</p>
            
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