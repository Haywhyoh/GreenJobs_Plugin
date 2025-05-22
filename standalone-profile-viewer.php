<?php
/**
 * Standalone Professional Profile Viewer
 * 
 * This file provides a direct way to view professional profiles even when the post type isn't registered.
 * It accesses the database directly, bypassing WordPress post type registration.
 * 
 * Usage: Place this file in your WordPress root and access it with:
 * /standalone-profile-viewer.php?id=109
 */

// Bootstrap WordPress
require_once('wp-load.php');

// Get the post ID from the URL
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$post_id) {
    echo 'No post ID specified. Please use ?id=XXX in the URL.';
    exit;
}

// Get post directly from the database
global $wpdb;
$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $post_id));

if (!$post) {
    echo 'Post not found.';
    exit;
}

// Get post meta directly from the database
$post_meta = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", $post_id), ARRAY_A);
$meta = array();
foreach ($post_meta as $row) {
    $meta[$row['meta_key']] = $row['meta_value'];
}

// Set up variables from meta
$job_title = isset($meta['bpp_job_title']) ? $meta['bpp_job_title'] : '';
$location = isset($meta['bpp_location']) ? $meta['bpp_location'] : '';
$years_experience = isset($meta['bpp_years_experience']) ? $meta['bpp_years_experience'] : '';
$skills = isset($meta['bpp_skills']) ? $meta['bpp_skills'] : '';
$skills_array = !empty($skills) ? explode(',', $skills) : array();
$bio = isset($meta['bpp_bio']) ? $meta['bpp_bio'] : $post->post_content;
$website = isset($meta['bpp_website']) ? $meta['bpp_website'] : '';
$linkedin = isset($meta['bpp_linkedin']) ? $meta['bpp_linkedin'] : '';
$email = isset($meta['bpp_email']) ? $meta['bpp_email'] : '';
$phone = isset($meta['bpp_phone']) ? $meta['bpp_phone'] : '';

// Try to get thumbnail URL
$thumb_url = '';
if (isset($meta['_thumbnail_id'])) {
    $attachment = $wpdb->get_row($wpdb->prepare("SELECT guid FROM $wpdb->posts WHERE ID = %d", $meta['_thumbnail_id']));
    if ($attachment) {
        $thumb_url = $attachment->guid;
    }
}

// Basic sanitization
function safe_output($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_output($post->post_title); ?> - Professional Profile</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-header { 
            display: flex; 
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .profile-photo { 
            width: 200px; 
            height: 200px; 
            margin-right: 30px; 
            background: #f0f0f0;
            margin-bottom: 20px;
        }
        .profile-photo img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        .profile-info { 
            flex: 1;
            min-width: 300px;
        }
        .profile-name { 
            font-size: 24px; 
            margin: 0 0 10px 0; 
        }
        .profile-job { 
            font-size: 18px; 
            margin: 0 0 15px 0; 
            color: #555; 
        }
        .profile-meta { 
            display: flex; 
            flex-wrap: wrap; 
            margin-bottom: 15px; 
        }
        .meta-item { 
            margin-right: 20px; 
            margin-bottom: 10px; 
        }
        .meta-label { 
            font-weight: bold; 
        }
        .profile-content { 
            margin-bottom: 30px; 
        }
        .profile-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .skills-list { 
            display: flex; 
            flex-wrap: wrap; 
        }
        .skill-tag { 
            background: #f0f0f0; 
            padding: 5px 10px; 
            margin-right: 10px; 
            margin-bottom: 10px; 
            border-radius: 3px; 
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 15px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 3px;
        }
        .back-button:hover {
            background: #e0e0e0;
        }
        h3 {
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 5px;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <div class="profile-photo">
                <?php if ($thumb_url): ?>
                    <img src="<?php echo safe_output($thumb_url); ?>" alt="<?php echo safe_output($post->post_title); ?>">
                <?php else: ?>
                    <div style="text-align: center; line-height: 200px;">No Photo</div>
                <?php endif; ?>
            </div>
            
            <div class="profile-info">
                <h1 class="profile-name"><?php echo safe_output($post->post_title); ?></h1>
                
                <?php if ($job_title): ?>
                    <h2 class="profile-job"><?php echo safe_output($job_title); ?></h2>
                <?php endif; ?>
                
                <div class="profile-meta">
                    <?php if ($location): ?>
                        <div class="meta-item">
                            <span class="meta-label">Location:</span> 
                            <?php echo safe_output($location); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($years_experience): ?>
                        <div class="meta-item">
                            <span class="meta-label">Experience:</span> 
                            <?php echo safe_output($years_experience); ?> years
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($website || $linkedin): ?>
                    <div class="profile-links">
                        <?php if ($website): ?>
                            <a href="<?php echo safe_output($website); ?>" target="_blank" style="margin-right: 15px;">Website</a>
                        <?php endif; ?>
                        
                        <?php if ($linkedin): ?>
                            <a href="<?php echo safe_output($linkedin); ?>" target="_blank">LinkedIn</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($bio): ?>
            <div class="profile-section profile-content">
                <h3>Professional Bio</h3>
                <div><?php echo nl2br(safe_output($bio)); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($skills_array)): ?>
            <div class="profile-section profile-skills">
                <h3>Skills & Expertise</h3>
                <div class="skills-list">
                    <?php foreach ($skills_array as $skill): ?>
                        <span class="skill-tag"><?php echo safe_output(trim($skill)); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($email || $phone): ?>
            <div class="profile-section profile-contact">
                <h3>Contact Information</h3>
                <?php if ($email): ?>
                    <p>
                        <strong>Email:</strong> 
                        <a href="mailto:<?php echo safe_output($email); ?>"><?php echo safe_output($email); ?></a>
                    </p>
                <?php endif; ?>
                
                <?php if ($phone): ?>
                    <p><strong>Phone:</strong> <?php echo safe_output($phone); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <a href="javascript:history.back();" class="back-button">&laquo; Back</a>
    </div>
</body>
</html> 