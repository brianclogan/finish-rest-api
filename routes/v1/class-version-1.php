<?php

/**
 * Require the base files for Version 1 of the API
 */

require_once 'endpoints/class-wp-rest-plugins-controller.php';
require_once 'endpoints/class-wp-rest-themes-controller.php';
require_once 'fields/class-wp-rest-plugin-meta-fields.php';
require_once 'fields/class-wp-rest-theme-meta-fields.php';


class Version1 {

    /**
     * Register Routes
     *
     * Handles registering routes for version 1
     * of the extended API.
     *
     * @since 1.0.0
     */
    public static function register_routes() {
        (new WP_REST_Plugins_Controller())->register_routes();
        (new WP_REST_Themes_Controller())->register_routes();
    }
}