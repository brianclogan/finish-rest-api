<?php

class WP_REST_Themes_Controller {


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
        $this->rest_base = 'themes';

        $this->meta = new WP_REST_Theme_Meta_Fields();
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_themes' ),
                'permission_callback' => array( $this, 'get_themes_permission_check' ),
                'args'            => array(

                ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'install_theme' ),
                'permission_callback' => array( $this, 'install_theme_permission_check' ),
                'args'            => array(

                )
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<slug>[A-Za-z0-9\-\_]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_theme' ),
                'permission_callback' => array( $this, 'get_theme_permission_check' ),
                'args'            => array(

                ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/delete', array(
            array(
                'methods'         => WP_REST_Server::DELETABLE,
                'callback'        => array( $this, 'delete_theme' ),
                'permission_callback' => array( $this, 'delete_theme_permission_check' ),
                'args'            => array(

                )
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/activate', array(
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'activate_theme' ),
                'permission_callback' => array( $this, 'activate_theme_permission_check' ),
                'args'            => array(

                ),
            ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/deactivate', array(
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'deactivate_theme' ),
                'permission_callback' => array( $this, 'deactivate_theme_permission_check' ),
                'args'            => array(

                )
            ),
        ) );
    }

    /**
     * Get Plugins
     *
     * Get the installed themes for the site
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_REST_Response
     */
    public function get_themes(WP_REST_Request $request) {
        $response = [];
        foreach(wp_get_themes(['errors' => null, 'allowed' => null]) AS $theme) {
            $response[] = [
                'name' => $theme->Name,
                'theme_uri' => $theme->ThemeURI,
                'description' => $theme->Description,
                'author' => $theme->Author,
                'author_uri' => $theme->AuthorURI,
                'version' => $theme->Version,
                'template' => $theme->Template,
                'status' => $theme->Status,
                'tags' => $theme->Tags,
                'text_domain' => $theme->TextDomain,
                'domain_path' => $theme->DomainPath,
                'update' => $theme->update,
                'screenshot' => $theme->get_screenshot()
            ];
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
     * Get the installed themes for the site
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_Error|WP_REST_Response
     */
    public function get_theme(WP_REST_Request $request) {
        $theme = wp_get_theme($request->get_param('theme'));
        if($theme->exists()) {
            return FinishRestApi_Response::respond($theme, true);
        } else {
            return FinishRestApi_Response::error('theme-not-found', 'Unable to find the theme.');
        }
    }

    /**
     * Install Plugin
     *
     * Install a theme on the site
     * via the full path of the theme.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_Error|WP_REST_Response
     */
    public function install_theme(WP_REST_Request $request) {
        $theme = $request->get_param('slug');
        $api = themes_api( 'theme_information', array(
            'slug' => $theme,
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
            return FinishRestApi_Response::error('theme-not-found', 'The theme was not found in the WordPress repository.', ['theme' => $theme]);
        }
        $upgrader = new Plugin_Upgrader( new FinishRestApi_Skin() );
        $install = $upgrader->install($api->download_link);
        if($install) {
            return FinishRestApi_Response::respond([
                'theme' => $theme,
                'installed' => true
            ], true);
        } else {
            return FinishRestApi_Response::error('theme-not-installed', 'Unable to install the theme. It may already be installed on the site.', ['theme' => $theme]);
        }
    }

    /**
     * Delete Plugin
     *
     * Delete a theme on the site.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_Error|WP_REST_Response
     */
    public function delete_theme(WP_REST_Request $request) {
        $theme = $request->get_param('slug');
        if($this->check_theme_status($theme)) {
            $error = new WP_Error('theme-is-active', 'Your theme is active, you must deactivate it before deleting it.', ['theme' => $theme]);
            return $error;
        }
        uninstall_theme($theme);
        $delete = delete_themes([$theme]);
        if ( is_wp_error( $delete ) ) {
            return $delete;
        } else {
            return FinishRestApi_Response::respond([
                'theme' => $theme,
                'deleted' => true
            ], true);
        }
    }

    /**
     * Activate Plugin
     *
     * Activate a theme on the site.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return null|WP_Error|WP_REST_Response
     */
    public function activate_theme(WP_REST_Request $request) {
        $theme = $request->get_param('slug');
        if(!$this->check_theme_status($theme)) {
            $activate = activate_theme($theme);
            if ( is_wp_error( $activate ) ) {
                return $activate;
            } else {
                return FinishRestApi_Response::respond([
                    'theme' => $theme,
                    'activated' => true
                ], true);
            }
        } else {
            return FinishRestApi_Response::error('theme-already-active', 'Plugin is already active.', ['theme' => $theme]);
        }
    }

    /**
     * Deactivate Plugin
     *
     * Deactivate a theme on the site.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function deactivate_theme(WP_REST_Request $request) {
        $theme = $request->get_param('slug');
        if($this->check_theme_status($theme)) {
            deactivate_themes($theme);
            return FinishRestApi_Response::respond([
                'theme' => $theme,
                'deactivated' => true
            ], true);
        } else {
            return FinishRestApi_Response::error('theme-already-deactivated', 'Plugin is already deactivated.', ['theme' => $theme]);
        }
    }

    /**
     * Check Plugin Status
     *
     * Checks the theme status
     * to see if a theme is active.
     *
     * @since 1.0.0
     *
     * @param string $theme
     * @return bool
     *
     */
    protected function check_theme_status($theme) {
        return is_theme_active($theme);
    }

    /**
     * Check Plugin Installed
     *
     * Checks if a theme
     * is installed on the site.
     *
     * @since 1.0.0
     *
     * @param $check
     * @return bool
     */
    protected function check_theme_installed($check) {
        $themes = get_themes();
        foreach($themes AS $f => $i) {
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
     * the user can view themes.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function get_themes_permission_check($request) {
        return current_user_can('edit_themes');
    }

    /**
     * Get Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can view a theme.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function get_theme_permission_check($request) {
        return $this->get_themes_permission_check($request);
    }

    /**
     * Install Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can install a theme.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function install_theme_permission_check($request) {
        return current_user_can('install_themes');
    }

    /**
     * Delete Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can delete a theme.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function delete_theme_permission_check($request) {
        return current_user_can('delete_themes');
    }

    /**
     * Activate Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can activate a theme.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function activate_theme_permission_check($request) {
        return current_user_can('activate_themes');
    }

    /**
     * Deactivate Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can activate a theme.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function deactivate_theme_permission_check($request) {
        return $this->activate_theme_permission_check($request);
    }

    /**
     * Update Plugin Permission Check
     *
     * Check permissions to make sure
     * the user can update a theme.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function update_theme_permission_check($request) {
        return current_user_can('update_themes');
    }

}