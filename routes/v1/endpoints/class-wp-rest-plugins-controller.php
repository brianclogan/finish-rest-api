<?php

class WP_REST_Plugins_Controller extends WP_REST_Controller {


    /**
     * Instance of a user meta fields object.
     *
     * @since 4.7.0
     * @var WP_REST_Plugin_Meta_Fields
     */
    protected $meta;

    /**
     * Constructor.
     *
     * @since 4.7.0
     */
    public function __construct() {
        $this->namespace = 'api/v1';
        $this->rest_base = 'plugins';

        $this->meta = new WP_REST_Plugin_Meta_Fields();
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
                'callback'        => array( $this, 'get_plugins' ),
                'permission_callback' => array( $this, 'get_plugins_permission_check' ),
                'args'            => array(

                ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'install_plugin' ),
                'permission_callback' => array( $this, 'install_plugin_permission_check' ),
                'args'            => array(

                )
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<slug>[A-Za-z0-9\-\_]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_plugin' ),
                'permission_callback' => array( $this, 'get_plugin_permission_check' ),
                'args'            => array(

                ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/delete', array(
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_plugin' ),
                'permission_callback' => array( $this, 'delete_plugin_permission_check' ),
                'args'            => array(

                )
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/activate', array(
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'activate_plugin' ),
                'permission_callback' => array( $this, 'activate_plugin_permission_check' ),
                'args'            => array(

                ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/deactivate', array(
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'deactivate_plugin' ),
                'permission_callback' => array( $this, 'deactivate_plugin_permission_check' ),
                'args'            => array(

                )
            ),
        ) );
    }

    /**
     * Get Plugins
     *
     * Get the installed plugins for the site
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_REST_Response
     */
    public function get_plugins(WP_REST_Request $request) {
        /**
         * Need to create a response array
         */
        $response = [];

        /**
         * Run through each plugin, this will allow us to
         * better name everything, and include the file in the
         * array.
         */
        foreach(get_plugins() AS $file => $info) {
            $plugin = [];
            $plug = array_merge([
                'file' => $file,
                'slug' => explode($file, '/')[0]
            ], $info);
            foreach($plug AS $item=> $value) {
                $plugin[FinishRestApi::toSnakeCase($item)] = $value;
            }
            $response[] = $plugin;
        }
        try {
            return FinishRestApi_Response::respond($response);
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Get Plugin
     *
     * Get the installed plugins for the site
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_Error|WP_REST_Response
     */
    public function get_plugin(WP_REST_Request $request) {
        $plugins = get_plugins();
        foreach($plugins AS $f => $i) {
            if(explode('/', $f)[0] == $request->get_param('slug')) {
                $plugin = [];
                $plugin['file'] = $f;
                foreach($i AS $item=> $value) {
                    $plugin[FinishRestApi::toSnakeCase($item)] = $value;
                }
                return FinishRestApi_Response::respond($plugin, true);
            }
        }
        return FinishRestApi_Response::error('plugin-not-found', 'The plugin was not found on the site.');
    }

    /**
     * Install Plugin
     *
     * Install a plugin on the site
     * via the full path of the plugin.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_Error|WP_REST_Response
     */
    public function install_plugin(WP_REST_Request $request) {
        $plugin = $request->get_param('slug');
        $api = plugins_api( 'plugin_information', array(
            'slug' => $plugin,
            'fields' => array(
                'short_description' => false,
                'sections' => false,
                'requires' => false,
                'rating' => false,
                'ratings' => false,
                'downloaded' => false,
                'last_updated' => false,
                'added' => false,
                'tags' => false,
                'compatibility' => false,
                'homepage' => false,
                'donate_link' => false,
            ),
        ) );
        if(is_wp_error($api)) {
            return FinishRestApi_Response::error('plugin-not-found', 'The plugin was not found in the WordPress repository.', ['plugin' => $plugin]);
        }
        $upgrader = new Plugin_Upgrader( new FinishRestApi_Skin() );
        $install = $upgrader->install($api->download_link);
        if($install) {
            return FinishRestApi_Response::respond([
                'plugin' => $plugin,
                'installed' => true
            ], true);
        } else {
            return FinishRestApi_Response::error('plugin-not-installed', 'Unable to install the plugin. It may already be installed on the site.', ['plugin' => $plugin]);
        }
    }

    /**
     * Delete Plugin
     *
     * Delete a plugin on the site.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_Error|WP_REST_Response
     */
    public function delete_plugin(WP_REST_Request $request) {
        $plugin = $request->get_param('slug');
        if($this->check_plugin_status($plugin)) {
            $error = new WP_Error('plugin-is-active', 'Your plugin is active, you must deactivate it before deleting it.', ['plugin' => $plugin]);
            return $error;
        }
        uninstall_plugin($plugin);
        $delete = delete_plugins([$plugin]);
        if ( is_wp_error( $delete ) ) {
            return $delete;
        } else {
            return FinishRestApi_Response::respond([
                'plugin' => $plugin,
                'deleted' => true
            ], true);
        }
    }

    /**
     * Activate Plugin
     *
     * Activate a plugin on the site.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return null|WP_Error|WP_REST_Response
     */
    public function activate_plugin(WP_REST_Request $request) {
        $plugin = $request->get_param('slug');
        if(!$this->check_plugin_status($plugin)) {
            $activate = activate_plugin($plugin);
            if ( is_wp_error( $activate ) ) {
                return $activate;
            } else {
                return FinishRestApi_Response::respond([
                    'plugin' => $plugin,
                    'activated' => true
                ], true);
            }
        } else {
            return FinishRestApi_Response::error('plugin-already-active', 'Plugin is already active.', ['plugin' => $plugin]);
        }
    }

    /**
     * Deactivate Plugin
     *
     * Deactivate a plugin on the site.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function deactivate_plugin(WP_REST_Request $request) {
        $plugin = $request->get_param('slug');
        if($this->check_plugin_status($plugin)) {
            deactivate_plugins($plugin);
            return FinishRestApi_Response::respond([
                'plugin' => $plugin,
                'deactivated' => true
            ], true);
        } else {
            return FinishRestApi_Response::error('plugin-already-deactivated', 'Plugin is already deactivated.', ['plugin' => $plugin]);
        }
    }

    /**
     * Check Plugin Status
     *
     * Checks the plugin status
     * to see if a plugin is active.
     *
     * @since 1.0.0
     *
     * @param string $plugin
     * @return bool
     *
     */
    protected function check_plugin_status($plugin) {
        return is_plugin_active($plugin);
    }

    /**
     * Check Plugin Installed
     *
     * Checks if a plugin
     * is installed on the site.
     *
     * @since 1.0.0
     *
     * @param $check
     * @return bool
     */
    protected function check_plugin_installed($check) {
        $plugins = get_plugins();
        foreach($plugins AS $f => $i) {
            if($f == $check) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get Plugins Permission Check
     *
     * Check permissions to make sure
     * the user can view plugins.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function get_plugins_permission_check($request) {
        return current_user_can('edit_plugins');
    }

    /**
     * Get Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can view a plugin.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function get_plugin_permission_check($request) {
        return $this->get_plugins_permission_check($request);
    }

    /**
     * Install Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can install a plugin.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function install_plugin_permission_check($request) {
        return current_user_can('install_plugins');
    }

    /**
     * Delete Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can delete a plugin.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function delete_plugin_permission_check($request) {
        return current_user_can('delete_plugins');
    }

    /**
     * Activate Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can activate a plugin.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function activate_plugin_permission_check($request) {
        return current_user_can('activate_plugins');
    }

    /**
     * Deactivate Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can activate a plugin.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function deactivate_plugin_permission_check($request) {
        return $this->activate_plugin_permission_check($request);
    }

    /**
     * Update Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can update a plugin.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function update_plugin_permission_check($request) {
        return current_user_can('update_plugins');
    }

}