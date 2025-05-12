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
     * - BPP_Form_Handler. Handles form submissions.
     * - BPP_Admin_Ajax. Handles admin AJAX requests.
     * - BPP_Email_Manager. Handles email notifications.
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
         * The class responsible for handling form submissions.
         */
        require_once BPP_PLUGIN_DIR . 'includes/class-bpp-form-handler.php';

        /**
         * The class responsible for handling admin AJAX requests.
         */
        require_once BPP_PLUGIN_DIR . 'includes/class-bpp-admin-ajax.php';

        /**
         * The class responsible for handling email notifications.
         */
        require_once BPP_PLUGIN_DIR . 'includes/class-bpp-email-manager.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once BPP_PLUGIN_DIR . 'admin/class-bpp-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once BPP_PLUGIN_DIR . 'public/class-bpp-public.php';

        /**
         * WordPress functions compatibility file.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-bpp-functions.php';

        /**
         * The email manager class handles all email notifications for the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-bpp-email-manager.php';

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
        $admin_ajax = new BPP_Admin_Ajax($this->get_plugin_name(), $this->get_version());
        $email_manager = new BPP_Email_Manager($this->get_plugin_name(), $this->get_version());

        // Admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Admin menu pages
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');

        // Custom admin actions
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        $this->loader->add_action('admin_notices', $plugin_admin, 'admin_notices');

        // Initialize admin AJAX handler
        $this->loader->add_action('wp_ajax_bpp_approve_applicant', $admin_ajax, 'approve_applicant');
        $this->loader->add_action('wp_ajax_bpp_reject_applicant', $admin_ajax, 'reject_applicant');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new BPP_Public( $this->get_plugin_name(), $this->get_version() );
        $form_handler = new BPP_Form_Handler( $this->get_plugin_name(), $this->get_version() );
        $email_manager = new BPP_Email_Manager( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        
        // Form handler hooks
        $this->loader->add_action( 'wp_ajax_bpp_submit_application', $form_handler, 'handle_application_submission' );
        $this->loader->add_action( 'wp_ajax_nopriv_bpp_submit_application', $form_handler, 'handle_application_submission' );
        
        // Register shortcodes
        $this->loader->add_action( 'init', $this, 'register_shortcodes' );
    }

    /**
     * Register all shortcodes used by the plugin.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        $plugin_public = new BPP_Public( $this->get_plugin_name(), $this->get_version() );
        
        add_shortcode( 'bpp_submission_form', array( $plugin_public, 'display_submission_form' ) );
        add_shortcode( 'bpp_directory', array( $plugin_public, 'display_directory' ) );
        add_shortcode( 'bpp_category_directory', array( $plugin_public, 'display_category_directory' ) );
        add_shortcode( 'bpp_featured', array( $plugin_public, 'display_featured_applicants' ) );
        add_shortcode( 'bpp_stats', array( $plugin_public, 'display_statistics' ) );
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