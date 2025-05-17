<?php
/**
 * WordPress functions compatibility file.
 *
 * This file includes compatibility functions to handle the linter errors related to WordPress functions.
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 * @subpackage Black_Potential_Pipeline/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Define WordPress functions if they don't exist to prevent linter errors
 */

if (!function_exists('get_option')) {
    /**
     * Retrieves an option value based on an option name.
     *
     * @param string $option  Name of the option to retrieve.
     * @param mixed  $default Default value to return if the option does not exist.
     * @return mixed Value of the option or default if not found.
     */
    function get_option($option, $default = false) {
        // This is a placeholder for linting purposes
        return $default;
    }
}

if (!function_exists('update_option')) {
    /**
     * Updates the value of an option that was already added.
     *
     * @param string $option   Name of the option to update.
     * @param mixed  $value    Option value.
     * @param bool   $autoload Whether to load the option when WordPress starts up.
     * @return bool True if updated successfully, false otherwise.
     */
    function update_option($option, $value, $autoload = null) {
        // This is a placeholder for linting purposes
        return true;
    }
}

if (!function_exists('get_bloginfo')) {
    /**
     * Retrieves information about the current site.
     *
     * @param string $show What to show.
     * @param string $filter How to filter what is shown.
     * @return string Information about the current site.
     */
    function get_bloginfo($show = '', $filter = 'raw') {
        // This is a placeholder for linting purposes
        return 'Black Potential Pipeline';
    }
}

if (!function_exists('__')) {
    /**
     * Translate function, placeholder for linting purposes.
     *
     * @param string $text   Text to translate.
     * @param string $domain Text domain.
     * @return string Translated text.
     */
    function __($text, $domain = 'default') {
        // This is a placeholder for linting purposes
        return $text;
    }
}

if (!function_exists('plugin_dir_path')) {
    /**
     * Get the filesystem directory path (with trailing slash) for the plugin.
     *
     * @param string $file The plugin file.
     * @return string The filesystem path of the directory that contains the plugin.
     */
    function plugin_dir_path($file) {
        // This is a placeholder for linting purposes
        return dirname($file) . '/';
    }
}

if (!function_exists('locate_template')) {
    /**
     * Locate a template file in theme or plugin directories.
     *
     * @param string|array $template_names Template file(s) to search for.
     * @param bool         $load           If true, the template file will be loaded.
     * @param bool         $require_once   Whether to require_once or require.
     * @return string The template filename if found, empty string if not.
     */
    function locate_template($template_names, $load = false, $require_once = true) {
        // This is a placeholder for linting purposes
        return '';
    }
}

if (!function_exists('wp_mail')) {
    /**
     * Send mail, similar to PHP's mail().
     *
     * @param string|string[] $to          Array or comma-separated list of email addresses to send message.
     * @param string          $subject     Email subject.
     * @param string          $message     Message contents.
     * @param string|string[] $headers     Optional. Additional headers.
     * @param string|string[] $attachments Optional. Files to attach.
     * @return bool Whether the email was sent successfully.
     */
    function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
        // This is a placeholder for linting purposes
        return true;
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Hook a function or method to a specific filter action.
     *
     * @param string $tag     The name of the filter to hook the $function_to_add callback to.
     * @param mixed  $value   The value to filter.
     * @param mixed  ...$args Additional parameters to pass to the callback function.
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    function apply_filters($tag, $value, ...$args) {
        // This is a placeholder for linting purposes
        return $value;
    }
}

if (!function_exists('do_action')) {
    /**
     * Execute functions hooked on a specific action hook.
     *
     * @param string $tag     The name of the action to be executed.
     * @param mixed  ...$args Additional arguments which are passed on to the functions hooked to the action.
     */
    function do_action($tag, ...$args) {
        // This is a placeholder for linting purposes
    }
}

if (!function_exists('get_post')) {
    /**
     * Retrieves post data.
     *
     * @param int|WP_Post|null $post   Post ID or post object.
     * @param string           $output Optional. The required return type.
     * @param string           $filter Optional. Type of filter to apply.
     * @return WP_Post|array|null WP_Post on success or null on failure.
     */
    function get_post($post = null, $output = 'OBJECT', $filter = 'raw') {
        // This is a placeholder for linting purposes
        return (object) array(
            'post_title' => 'Sample Post',
        );
    }
}

if (!function_exists('get_post_meta')) {
    /**
     * Retrieve post meta field for a post.
     *
     * @param int    $post_id    Post ID.
     * @param string $key        Optional. The meta key to retrieve.
     * @param bool   $single     Optional. Whether to return a single value.
     * @return mixed Single metadata value, or array of values.
     */
    function get_post_meta($post_id, $key = '', $single = false) {
        // This is a placeholder for linting purposes
        return '';
    }
}

if (!function_exists('wp_get_post_terms')) {
    /**
     * Retrieve the terms of a taxonomy or taxonomies for a post.
     *
     * @param int          $post_id  Post ID.
     * @param string|array $taxonomy Taxonomy name or array of taxonomy names.
     * @param array        $args     Optional. Arguments for retrieving terms.
     * @return array|WP_Error Array of terms or empty array if no terms found.
     */
    function wp_get_post_terms($post_id, $taxonomy, $args = array()) {
        // This is a placeholder for linting purposes
        return array();
    }
}

if (!function_exists('is_email')) {
    /**
     * Verifies that an email is valid.
     *
     * @param string $email      Email address to verify.
     * @param bool   $deprecated Deprecated.
     * @return string|false The valid email address, or false on failure.
     */
    function is_email($email, $deprecated = false) {
        // This is a placeholder for linting purposes
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('get_permalink')) {
    /**
     * Retrieves the full permalink for the current post or post ID.
     *
     * @param int|WP_Post $post      Optional. Post ID or post object.
     * @param bool        $leavename Optional. Whether to keep post name.
     * @return string The permalink URL or false on failure.
     */
    function get_permalink($post = 0, $leavename = false) {
        // This is a placeholder for linting purposes
        return 'https://codemygig.com,/sample-post/';
    }
}

if (!function_exists('admin_url')) {
    /**
     * Retrieves the URL to the admin area for the current site.
     *
     * @param string $path   Optional. Path relative to the admin URL.
     * @param string $scheme Optional. The scheme to use.
     * @return string Admin URL link with optional path appended.
     */
    function admin_url($path = '', $scheme = 'admin') {
        // This is a placeholder for linting purposes
        return 'https://codemygig.com,/wp-admin/' . ltrim($path, '/');
    }
}

if (!function_exists('current_time')) {
    /**
     * Retrieves the current time based on specified type.
     *
     * @param string   $type    Type of time to retrieve.
     * @param int|bool $gmt     Optional. Whether to use GMT timezone.
     * @return int|string Current time in seconds or a date string.
     */
    function current_time($type, $gmt = 0) {
        // This is a placeholder for linting purposes
        if ($type === 'mysql') {
            return date('Y-m-d H:i:s');
        }
        return time();
    }
}

if (!function_exists('add_shortcode')) {
    /**
     * Register a new shortcode.
     *
     * @param string   $tag      Shortcode tag to be searched in post content.
     * @param callable $callback The callback function to run when the shortcode is found.
     * @return bool True on success, false on failure.
     */
    function add_shortcode($tag, $callback) {
        // This is a placeholder for linting purposes
        return true;
    }
} 