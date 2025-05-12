<?php
/**
 * Register all shortcodes for the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 */

/**
 * Register all shortcodes for the plugin.
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 * @author     Your Name <email@example.com>
 */
class BPP_Shortcodes {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Render the submission form.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display the form.
     */
    public function render_submission_form($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Join the Black Potential Pipeline', 'black-potential-pipeline'),
                'success_message' => __('Thank you for your submission! We will review your application shortly.', 'black-potential-pipeline'),
            ),
            $atts,
            'black_potential_pipeline_form'
        );

        // Enqueue form-specific scripts and styles
        wp_enqueue_style('bpp-form-style');
        wp_enqueue_script('bpp-form-script');

        // Start output buffering
        ob_start();

        // Include the form template
        include(BPP_PLUGIN_DIR . 'public/partials/bpp-submission-form.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Render the directory of all approved applicants.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display the directory.
     */
    public function render_directory($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Black Potential Pipeline Directory', 'black-potential-pipeline'),
                'per_page' => 12,
                'layout' => 'grid', // grid or list
            ),
            $atts,
            'black_potential_pipeline_directory'
        );

        // Enqueue directory-specific scripts and styles
        wp_enqueue_style('bpp-directory-style');
        wp_enqueue_script('bpp-directory-script');

        // Start output buffering
        ob_start();

        // Include the directory template
        include(BPP_PLUGIN_DIR . 'public/partials/bpp-directory.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Render the directory of applicants in a specific category.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display the category directory.
     */
    public function render_category_directory($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'category' => '',
                'title' => '',
                'per_page' => 12,
                'layout' => 'grid', // grid or list
            ),
            $atts,
            'black_potential_pipeline_category'
        );

        // Validate category
        if (empty($atts['category'])) {
            return '<p>' . __('Error: No category specified.', 'black-potential-pipeline') . '</p>';
        }

        // Set default title based on category
        if (empty($atts['title'])) {
            $category_term = get_term_by('slug', $atts['category'], 'bpp_industry');
            $atts['title'] = sprintf(__('Black Professionals in %s', 'black-potential-pipeline'), $category_term ? $category_term->name : $atts['category']);
        }

        // Enqueue directory-specific scripts and styles
        wp_enqueue_style('bpp-directory-style');
        wp_enqueue_script('bpp-directory-script');

        // Start output buffering
        ob_start();

        // Include the category directory template
        include(BPP_PLUGIN_DIR . 'public/partials/bpp-category-directory.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Render featured candidates.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display featured candidates.
     */
    public function render_featured_candidates($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Featured Black Professionals', 'black-potential-pipeline'),
                'count' => 4,
                'layout' => 'carousel', // carousel, grid, or list
            ),
            $atts,
            'black_potential_pipeline_featured'
        );

        // Enqueue featured-specific scripts and styles
        wp_enqueue_style('bpp-featured-style');
        wp_enqueue_script('bpp-featured-script');

        // Start output buffering
        ob_start();

        // Include the featured candidates template
        include(BPP_PLUGIN_DIR . 'public/partials/bpp-featured.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Render statistics about the pipeline.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string    HTML content to display statistics.
     */
    public function render_statistics($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => __('Pipeline Statistics', 'black-potential-pipeline'),
                'show_categories' => 'yes',
                'show_total' => 'yes',
            ),
            $atts,
            'black_potential_pipeline_stats'
        );

        // Enqueue stats-specific scripts and styles
        wp_enqueue_style('bpp-stats-style');
        wp_enqueue_script('bpp-stats-script');

        // Start output buffering
        ob_start();

        // Include the statistics template
        include(BPP_PLUGIN_DIR . 'public/partials/bpp-statistics.php');

        // Get the buffered content
        $output = ob_get_clean();

        return $output;
    }
} 