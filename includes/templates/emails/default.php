<?php
/**
 * Default email template
 *
 * This template is used when a specific email template is not found.
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
// $message - Message to display (if set)
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $site_name; ?> - Black Potential Pipeline</title>
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
            <?php if (isset($message)) : ?>
                <?php echo $message; ?>
            <?php else : ?>
                <p>Thank you for your interest in Black Potential Pipeline.</p>
                <p>This is an automated email from our system.</p>
            <?php endif; ?>
        </div>
        <div class="footer">
            <p><?php echo $footer_text; ?></p>
            <p>If you have any questions, please contact us at <?php echo $admin_email; ?></p>
        </div>
    </div>
</body>
</html> 