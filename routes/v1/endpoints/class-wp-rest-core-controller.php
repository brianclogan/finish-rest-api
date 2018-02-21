<?php

class WP_REST_Core_Controller extends WP_REST_Controller {


    /**
     * Instance of a user meta fields object.
     *
     * @since 4.7.0
     * @var WP_REST_Core_Meta_Fields
     */
    protected $meta;

    /**
     * Constructor.
     *
     * @since 4.7.0
     */
    public function __construct() {
        $this->namespace = 'api/v1';
        $this->rest_base = 'core';

        $this->meta = new WP_REST_Core_Meta_Fields();

        require_once ABSPATH . 'wp-admin/includes/class-core-upgrader.php';

    }

    /**
     * Registers the routes for the objects of the controller.
     *
     * @since 1.0.0
     *
     * @see register_rest_route()
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_core' ),
                'permission_callback' => array( $this, 'core_permission_check' ),
                'args'            => array(

                ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/upgrade', array(
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'update_core' ),
                'permission_callback' => array( $this, 'core_permission_check' ),
                'args'            => array(

                )
            ),
        ) );
    }

    /**
     * Get Core
     *
     * Get the installed core for the site
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_REST_Response
     */
    public function get_core(WP_REST_Request $request) {
        $response = [];
        try {
            return FinishRestApi_Response::respond($response);
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Update Core
     *
     * Update the installed core for the site
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return false|object
     */
    public function update_core(WP_REST_Request $request) {
        if(!function_exists('find_core_update')) {
            require_once ABSPATH . '/wp-admin/includes/update.php';
        }
        $update_available = find_core_update(get_bloginfo('version'), get_bloginfo('language'));
        if(!$update_available) {
            return FinishRestApi_Response::respond([
                'updated' => false,
                'version' => get_bloginfo('version'),
            ], true);
        } else {
            $upgrader = new Core_Upgrader();
            $update = $upgrader->upgrade($update_available);
            if(is_wp_error($update)) {
                return $update;
            } else {
                return FinishRestApi_Response::respond([
                    'updated' => true,
                    'version' => get_bloginfo('version')
                ], true);
            }
        }
    }

    /**
     * Get Core Permission Check
     *
     * Check permissions to make sure
     * the user can view core.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function core_permission_check($request) {
        return current_user_can('update_core');
    }

}