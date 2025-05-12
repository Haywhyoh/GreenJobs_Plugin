<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 */

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 * @author     Your Name <email@example.com>
 */
class BPP {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      BPP_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('BPP_VERSION')) {
            $this->version = BPP_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'black-potential-pipeline';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_shortcodes();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - BPP_Loader. Orchestrates the hooks of the plugin.
     * - BPP_i18n. Defines internationalization functionality.
     * - BPP_Admin. Defines all hooks for the admin area.
     * - BPP_Public. Defines all hooks for the public side of the site.
     * - BPP_Post_Types. Registers custom post types and taxonomies.
     * - BPP_Shortcodes. Registers shortcodes.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once BPP_PLUGIN_DIR . 'includes/class-bpp-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once BPP_PLUGIN_DIR . 'includes/class-bpp-i18n.php';

        /**
         * The class responsible for registering custom post types and taxonomies.
         */
        require_once BPP_PLUGIN_DIR . 'includes/class-bpp-post-types.php';

        /**
         * The class responsible for registering shortcodes.
         */
        require_once BPP_PLUGIN_DIR . 'includes/class-bpp-shortcodes.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once BPP_PLUGIN_DIR . 'admin/class-bpp-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once BPP_PLUGIN_DIR . 'public/class-bpp-public.php';

        $this->loader = new BPP_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new BPP_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new BPP_Admin($this->get_plugin_name(), $this->get_version());

        // Admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Admin menu pages
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');

        // Custom admin actions
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        $this->loader->add_action('admin_notices', $plugin_admin, 'admin_notices');

        // Ajax handlers for admin
        $this->loader->add_action('wp_ajax_bpp_approve_applicant', $plugin_admin, 'ajax_approve_applicant');
        $this->loader->add_action('wp_ajax_bpp_reject_applicant', $plugin_admin, 'ajax_reject_applicant');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new BPP_Public($this->get_plugin_name(), $this->get_version());

        // Public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Form submission handling
        $this->loader->add_action('wp_ajax_bpp_submit_application', $plugin_public, 'process_application_submission');
        $this->loader->add_action('wp_ajax_nopriv_bpp_submit_application', $plugin_public, 'process_application_submission');
    }

    /**
     * Register the shortcodes.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_shortcodes() {
        $plugin_shortcodes = new BPP_Shortcodes($this->get_plugin_name(), $this->get_version());
        
        // Register shortcodes with WordPress
        add_shortcode('black_potential_pipeline_form', array($plugin_shortcodes, 'render_submission_form'));
        add_shortcode('black_potential_pipeline_directory', array($plugin_shortcodes, 'render_directory'));
        add_shortcode('black_potential_pipeline_category', array($plugin_shortcodes, 'render_category_directory'));
        add_shortcode('black_potential_pipeline_featured', array($plugin_shortcodes, 'render_featured_candidates'));
        add_shortcode('black_potential_pipeline_stats', array($plugin_shortcodes, 'render_statistics'));
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    BPP_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
} 