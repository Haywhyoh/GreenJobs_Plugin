<?php
/**
 * Provide a public-facing view for statistics display
 *
 * This file displays aggregated statistics about the professionals in the database
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/public/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Extract shortcode attributes
$title = isset($atts['title']) ? sanitize_text_field($atts['title']) : __('Black Potential Pipeline Statistics', 'black-potential-pipeline');
$description = isset($atts['description']) ? sanitize_text_field($atts['description']) : __('At a glance metrics about our talent database.', 'black-potential-pipeline');
$style = isset($atts['style']) ? sanitize_text_field($atts['style']) : 'cards'; // cards or bars
$show = isset($atts['show']) ? sanitize_text_field($atts['show']) : 'all'; // all, industries, experience, growth

// Get all published applicants count
$total_professionals = wp_count_posts('bpp_applicant')->publish;

// Get all industry terms and their counts
$industries = get_terms(array(
    'taxonomy' => 'bpp_industry',
    'hide_empty' => true,
));

// Setup industry data array
$industry_data = array();
foreach ($industries as $industry) {
    $industry_data[$industry->name] = $industry->count;
}

// Get experience levels counts
$experience_levels = array(
    '0-2' => 0,
    '3-5' => 0,
    '6-10' => 0,
    '10+' => 0
);

$experience_query = new WP_Query(array(
    'post_type' => 'bpp_applicant',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'fields' => 'ids',
));

if ($experience_query->have_posts()) {
    foreach ($experience_query->posts as $post_id) {
        $experience = get_post_meta($post_id, 'bpp_years_experience', true);
        if (!empty($experience) && isset($experience_levels[$experience])) {
            $experience_levels[$experience]++;
        }
    }
}

// Get monthly growth data for the past 6 months
$growth_data = array();
$current_month = date('n');
$current_year = date('Y');

for ($i = 5; $i >= 0; $i--) {
    $month = $current_month - $i;
    $year = $current_year;
    
    if ($month <= 0) {
        $month += 12;
        $year -= 1;
    }
    
    $month_name = date('M', mktime(0, 0, 0, $month, 1, $year));
    
    $args = array(
        'post_type' => 'bpp_applicant',
        'post_status' => 'publish',
        'date_query' => array(
            array(
                'year' => $year,
                'month' => $month,
            ),
        ),
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    
    $monthly_query = new WP_Query($args);
    $growth_data[$month_name] = $monthly_query->found_posts;
}

// Get fields to display based on the 'show' attribute
$show_industries = ($show === 'all' || $show === 'industries');
$show_experience = ($show === 'all' || $show === 'experience');
$show_growth = ($show === 'all' || $show === 'growth');

// Find max values for percentage calculations
$max_industry = !empty($industry_data) ? max($industry_data) : 1;
$max_experience = !empty($experience_levels) ? max($experience_levels) : 1;
$max_growth = !empty($growth_data) ? max($growth_data) : 1;
?>

<div class="bpp-statistics-container">
    <div class="bpp-statistics-header">
        <h2 class="bpp-statistics-title"><?php echo esc_html($title); ?></h2>
        <p class="bpp-statistics-description"><?php echo esc_html($description); ?></p>
    </div>
    
    <div class="bpp-statistics-summary">
        <div class="bpp-total-professionals">
            <span class="bpp-stat-number"><?php echo esc_html($total_professionals); ?></span>
            <span class="bpp-stat-label"><?php _e('Total Professionals', 'black-potential-pipeline'); ?></span>
        </div>
    </div>
    
    <?php if ($style === 'cards') : ?>
        <div class="bpp-statistics-grid">
            <!-- Industries Distribution -->
            <?php if ($show_industries && !empty($industry_data)) : ?>
                <div class="bpp-stat-card bpp-industries-card">
                    <h3><?php _e('Industries', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-stat-content">
                        <?php foreach ($industry_data as $industry => $count) : ?>
                            <div class="bpp-stat-item">
                                <div class="bpp-stat-item-header">
                                    <span class="bpp-stat-item-label"><?php echo esc_html($industry); ?></span>
                                    <span class="bpp-stat-item-value"><?php echo esc_html($count); ?></span>
                                </div>
                                <div class="bpp-stat-progress">
                                    <div class="bpp-stat-bar" style="width: <?php echo esc_attr(($count / $max_industry) * 100); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Experience Levels -->
            <?php if ($show_experience && !empty($experience_levels)) : ?>
                <div class="bpp-stat-card bpp-experience-card">
                    <h3><?php _e('Experience Levels', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-stat-content">
                        <?php foreach ($experience_levels as $level => $count) : ?>
                            <div class="bpp-stat-item">
                                <div class="bpp-stat-item-header">
                                    <span class="bpp-stat-item-label">
                                        <?php 
                                        switch ($level) {
                                            case '0-2':
                                                _e('0-2 years', 'black-potential-pipeline');
                                                break;
                                            case '3-5':
                                                _e('3-5 years', 'black-potential-pipeline');
                                                break;
                                            case '6-10':
                                                _e('6-10 years', 'black-potential-pipeline');
                                                break;
                                            case '10+':
                                                _e('10+ years', 'black-potential-pipeline');
                                                break;
                                        }
                                        ?>
                                    </span>
                                    <span class="bpp-stat-item-value"><?php echo esc_html($count); ?></span>
                                </div>
                                <div class="bpp-stat-progress">
                                    <div class="bpp-stat-bar" style="width: <?php echo esc_attr(($count / $max_experience) * 100); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Growth Over Time -->
            <?php if ($show_growth && !empty($growth_data)) : ?>
                <div class="bpp-stat-card bpp-growth-card">
                    <h3><?php _e('Monthly Growth', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-stat-content">
                        <?php foreach ($growth_data as $month => $count) : ?>
                            <div class="bpp-stat-item">
                                <div class="bpp-stat-item-header">
                                    <span class="bpp-stat-item-label"><?php echo esc_html($month); ?></span>
                                    <span class="bpp-stat-item-value"><?php echo esc_html($count); ?></span>
                                </div>
                                <div class="bpp-stat-progress">
                                    <div class="bpp-stat-bar" style="width: <?php echo esc_attr(($count / $max_growth) * 100); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <!-- Bar Chart Style -->
        <div class="bpp-statistics-charts">
            <!-- Industries Distribution -->
            <?php if ($show_industries && !empty($industry_data)) : ?>
                <div class="bpp-chart-section">
                    <h3><?php _e('Industries', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-bar-chart">
                        <?php foreach ($industry_data as $industry => $count) : ?>
                            <div class="bpp-bar-container">
                                <div class="bpp-bar-label"><?php echo esc_html($industry); ?></div>
                                <div class="bpp-bar-wrapper">
                                    <div class="bpp-bar" style="height: <?php echo esc_attr(($count / $max_industry) * 100); ?>%">
                                        <span class="bpp-bar-value"><?php echo esc_html($count); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Experience Levels -->
            <?php if ($show_experience && !empty($experience_levels)) : ?>
                <div class="bpp-chart-section">
                    <h3><?php _e('Experience Levels', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-bar-chart">
                        <?php foreach ($experience_levels as $level => $count) : ?>
                            <div class="bpp-bar-container">
                                <div class="bpp-bar-label">
                                    <?php 
                                    switch ($level) {
                                        case '0-2':
                                            _e('0-2 yrs', 'black-potential-pipeline');
                                            break;
                                        case '3-5':
                                            _e('3-5 yrs', 'black-potential-pipeline');
                                            break;
                                        case '6-10':
                                            _e('6-10 yrs', 'black-potential-pipeline');
                                            break;
                                        case '10+':
                                            _e('10+ yrs', 'black-potential-pipeline');
                                            break;
                                    }
                                    ?>
                                </div>
                                <div class="bpp-bar-wrapper">
                                    <div class="bpp-bar" style="height: <?php echo esc_attr(($count / $max_experience) * 100); ?>%">
                                        <span class="bpp-bar-value"><?php echo esc_html($count); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Growth Over Time -->
            <?php if ($show_growth && !empty($growth_data)) : ?>
                <div class="bpp-chart-section">
                    <h3><?php _e('Monthly Growth', 'black-potential-pipeline'); ?></h3>
                    <div class="bpp-bar-chart">
                        <?php foreach ($growth_data as $month => $count) : ?>
                            <div class="bpp-bar-container">
                                <div class="bpp-bar-label"><?php echo esc_html($month); ?></div>
                                <div class="bpp-bar-wrapper">
                                    <div class="bpp-bar" style="height: <?php echo esc_attr(($count / $max_growth) * 100); ?>%">
                                        <span class="bpp-bar-value"><?php echo esc_html($count); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Animate statistics on view
    function animateStats() {
        $('.bpp-stat-bar').each(function() {
            const $bar = $(this);
            const width = $bar.css('width');
            
            $bar.css('width', 0).animate({
                width: width
            }, 1000);
        });
        
        $('.bpp-bar').each(function() {
            const $bar = $(this);
            const height = $bar.css('height');
            
            $bar.css('height', 0).animate({
                height: height
            }, 1000);
        });
    }
    
    // Check if element is in viewport
    function isInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // Trigger animation when stats container is in viewport
    $(window).on('scroll', function() {
        const $statsContainer = $('.bpp-statistics-container');
        
        if ($statsContainer.length && isInViewport($statsContainer[0]) && !$statsContainer.hasClass('animated')) {
            $statsContainer.addClass('animated');
            animateStats();
        }
    });
    
    // Initial check on page load
    $(window).trigger('scroll');
});
</script> 