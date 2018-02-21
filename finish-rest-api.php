<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://twitter.com/darkgoldblade
 * @since             1.0.0
 * @package           Finish Rest API
 *
 * @wordpress-plugin
 * Plugin Name:       Finish Rest API
 * Plugin URI:        https://twitter.com/darkgoldblade
 * Description:       A plugin that adds the rest of the functionality we want from the REST API.
 * Version:           1.0.0
 * Author:            darkgoldblade
 * Author URI:        https://twitter.com/darkgoldblade
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       darkgoldblade
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FINISH_REST_API_VERSION', '1.0.0' );

/**
 * Require the core files required
 */
require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
require_once ABSPATH . 'wp-includes/rest-api/endpoints/class-wp-rest-controller.php';
include_once ABSPATH . 'wp-admin/includes/theme-install.php';

/**
 * Require the base files for all versions
 */
require_once 'includes/class_finish-rest-api-response.php';
require_once 'includes/class_finish-rest-api-skin.php';

/**
 * Require the plugin files
 */
require_once 'routes/v1/class-version-1.php';

class FinishRestApi {

    /**
     * Initialize
     *
     * Runs the initialize command and
     * sets up the full plugin.
     *
     * @since 1.0.0
     *
     * @returns void
     *
     */
    public function init() {
        $this->register_api_routes();
    }

    /**
     * Register API Routes
     *
     * Registers all the api routes that are needed
     * to complete the REST API.
     *
     * @since 1.0.0
     *
     * @returns void
     *
     */
    protected function register_api_routes() {
        Version1::register_routes();
    }

    public static function toSnakeCase($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

}
add_action('init','register_finish_api_routes');
function register_finish_api_routes()
{
    $api = new FinishRestApi();
    $api->init();
}


if(FINSIH_REST_API_BASIC_AUTH) {
    function json_basic_auth_handler( $user ) {
        global $wp_json_basic_auth_error;
        $wp_json_basic_auth_error = null;
        // Don't authenticate twice
        if ( ! empty( $user ) ) {
            return $user;
        }
        // Check that we're trying to authenticate
        if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
            return $user;
        }
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        /**
         * In multi-site, wp_authenticate_spam_check filter is run on authentication. This filter calls
         * get_currentuserinfo which in turn calls the determine_current_user filter. This leads to infinite
         * recursion and a stack overflow unless the current function is removed from the determine_current_user
         * filter during authentication.
         */
        remove_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );
        $user = wp_authenticate( $username, $password );
        add_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );
        if ( is_wp_error( $user ) ) {
            $wp_json_basic_auth_error = $user;
            return null;
        }
        $wp_json_basic_auth_error = true;
        return $user->ID;
    }
    add_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );
}