<?php
/**
 * Register custom post types and taxonomies.
 *
 * @link       https://codemygig.com,
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 */

/**
 * Register custom post types and taxonomies.
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 * @author     Adedayo Ayomide Samue ayomide@codemygig.com
 */
class BPP_Post_Types {

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

        // Register post types and taxonomies
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
    }

    /**
     * Register the custom post type for applicant profiles.
     *
     * @since    1.0.0
     */
    public function register_post_types() {
        // Custom Post Type: Applicant
        $labels = array(
            'name'                  => _x('Applicants', 'Post type general name', 'black-potential-pipeline'),
            'singular_name'         => _x('Applicant', 'Post type singular name', 'black-potential-pipeline'),
            'menu_name'             => _x('Applicants', 'Admin Menu text', 'black-potential-pipeline'),
            'name_admin_bar'        => _x('Applicant', 'Add New on Toolbar', 'black-potential-pipeline'),
            'add_new'               => __('Add New', 'black-potential-pipeline'),
            'add_new_item'          => __('Add New Applicant', 'black-potential-pipeline'),
            'new_item'              => __('New Applicant', 'black-potential-pipeline'),
            'edit_item'             => __('Edit Applicant', 'black-potential-pipeline'),
            'view_item'             => __('View Applicant', 'black-potential-pipeline'),
            'all_items'             => __('All Applicants', 'black-potential-pipeline'),
            'search_items'          => __('Search Applicants', 'black-potential-pipeline'),
            'parent_item_colon'     => __('Parent Applicants:', 'black-potential-pipeline'),
            'not_found'             => __('No applicants found.', 'black-potential-pipeline'),
            'not_found_in_trash'    => __('No applicants found in Trash.', 'black-potential-pipeline'),
            'featured_image'        => _x('Applicant Photo', 'Overrides the "Featured Image" phrase', 'black-potential-pipeline'),
            'set_featured_image'    => _x('Set applicant photo', 'Overrides the "Set featured image" phrase', 'black-potential-pipeline'),
            'remove_featured_image' => _x('Remove applicant photo', 'Overrides the "Remove featured image" phrase', 'black-potential-pipeline'),
            'use_featured_image'    => _x('Use as applicant photo', 'Overrides the "Use as featured image" phrase', 'black-potential-pipeline'),
            'archives'              => _x('Applicant archives', 'The post type archive label used in nav menus', 'black-potential-pipeline'),
            'insert_into_item'      => _x('Insert into applicant', 'Overrides the "Insert into post" phrase', 'black-potential-pipeline'),
            'uploaded_to_this_item' => _x('Uploaded to this applicant', 'Overrides the "Uploaded to this post" phrase', 'black-potential-pipeline'),
            'filter_items_list'     => _x('Filter applicants list', 'Screen reader text for the filter links heading on the post type listing screen', 'black-potential-pipeline'),
            'items_list_navigation' => _x('Applicants list navigation', 'Screen reader text for the pagination heading on the post type listing screen', 'black-potential-pipeline'),
            'items_list'            => _x('Applicants list', 'Screen reader text for the items list heading on the post type listing screen', 'black-potential-pipeline'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'bpp-dashboard', // Custom parent menu slug
            'query_var'          => true,
            'rewrite'            => array(
                'slug' => 'applicant',
                'with_front' => true,
                'pages' => true,
                'feeds' => true,
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'menu_icon'          => 'dashicons-id',
            'show_in_rest'       => true,
        );

        register_post_type('bpp_applicant', $args);
    }

    /**
     * Register the taxonomy for industry categories.
     *
     * @since    1.0.0
     */
    public function register_taxonomies() {
        // Taxonomy: Industry
        $labels = array(
            'name'              => _x('Industries', 'taxonomy general name', 'black-potential-pipeline'),
            'singular_name'     => _x('Industry', 'taxonomy singular name', 'black-potential-pipeline'),
            'search_items'      => __('Search Industries', 'black-potential-pipeline'),
            'all_items'         => __('All Industries', 'black-potential-pipeline'),
            'parent_item'       => __('Parent Industry', 'black-potential-pipeline'),
            'parent_item_colon' => __('Parent Industry:', 'black-potential-pipeline'),
            'edit_item'         => __('Edit Industry', 'black-potential-pipeline'),
            'update_item'       => __('Update Industry', 'black-potential-pipeline'),
            'add_new_item'      => __('Add New Industry', 'black-potential-pipeline'),
            'new_item_name'     => __('New Industry Name', 'black-potential-pipeline'),
            'menu_name'         => __('Industries', 'black-potential-pipeline'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'industry'),
            'show_in_rest'      => true,
        );

        register_taxonomy('bpp_industry', array('bpp_applicant'), $args);

        // Add default industry terms
        $this->add_default_terms();
    }

    /**
     * Flush rewrite rules to make permalinks work properly.
     * Call this when activating the plugin or when making changes to rewrite rules.
     *
     * @since    1.0.0
     * @access   public
     */
    public function flush_rewrite_rules() {
        // This should only be called on plugin activation or settings change
        flush_rewrite_rules();
    }

    /**
     * Add default industry terms.
     *
     * @since    1.0.0
     */
    private function add_default_terms() {
        $industries = array(
            'nature-based-work' => __('Nature-based work', 'black-potential-pipeline'),
            'environmental-policy' => __('Environmental policy', 'black-potential-pipeline'),
            'climate-science' => __('Climate science', 'black-potential-pipeline'),
            'green-construction' => __('Green construction & infrastructure', 'black-potential-pipeline'),
        );

        foreach ($industries as $slug => $name) {
            if (!term_exists($name, 'bpp_industry')) {
                wp_insert_term($name, 'bpp_industry', array(
                    'slug' => $slug,
                    'description' => sprintf(__('Applicants in the %s industry', 'black-potential-pipeline'), $name),
                ));
            }
        }
    }
} 