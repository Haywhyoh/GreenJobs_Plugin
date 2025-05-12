<?php
/**
 * Define the internationalization functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 * @author     Your Name <email@example.com>
 */
class BPP_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'black-potential-pipeline',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
} 