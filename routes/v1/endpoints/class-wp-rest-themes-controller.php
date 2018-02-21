<?php

class WP_REST_Themes_Controller extends WP_REST_Controller {


    /**
     * Instance of a user meta fields object.
     *
     * @since 4.7.0
     * @var WP_REST_Theme_Meta_Fields
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
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<theme>[A-Za-z0-9\-\_]+)', array(
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
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/switch', array(
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'switch_theme' ),
                'permission_callback' => array( $this, 'switch_themes_permission_check' ),
                'args'            => array(

                ),
            ),
        ) );
    }

    /**
     * Get Themes
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
                'screen_shot' => $theme->get_screenshot()
            ];
        }
        try {
            return FinishRestApi_Response::respond($response);
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     * Get Theme
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
            $response = [
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
                'screen_shot' => $theme->get_screenshot()
            ];
            return FinishRestApi_Response::respond($response, true);
        } else {
            return FinishRestApi_Response::error('theme-not-found', 'Unable to find the theme.');
        }
    }

    /**
     * Install Theme
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

        if(!function_exists('themes_api')) {
            include_once ABSPATH . 'wp-admin/includes/theme-install.php';
        }

        $theme = $request->get_param('theme');

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

        $upgrader = new Theme_Upgrader( new FinishRestApi_Skin() );

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
     * Delete Theme
     *
     * Delete a theme on the site.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return Exception|WP_Error|WP_REST_Response
     */
    public function delete_theme(WP_REST_Request $request) {

        $theme = $request->get_param('theme');

        if($this->check_theme_status($theme)) {
            $error = new WP_Error('theme-is-active', 'Your theme is active, you must deactivate it before deleting it.', ['theme' => $theme]);
            return $error;
        }

        $delete = delete_theme($theme);

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
     * Switch Theme
     *
     * Switch a theme on the site.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request
     * @return null|WP_Error|WP_REST_Response
     */
    public function switch_theme(WP_REST_Request $request) {
        $theme = $request->get_param('theme');
        if(!$this->check_theme_status($theme)) {
            switch_theme($theme);
            return FinishRestApi_Response::respond([
                'theme' => $theme,
                'activated' => true
            ], true);
        } else {
            return FinishRestApi_Response::error('theme-already-active', 'Theme is already active.', ['theme' => $theme]);
        }
    }

    /**
     * Check Theme Status
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
        $active_theme = wp_get_theme();
        if($active_theme->template_dir == $theme) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Themes Permission Check
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
     * Get Theme Permission Check
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
     * Install Theme Permission Check
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
     * Delete Theme Permission Check
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
     * Switch Themes Permission Check
     *
     * Check permissions to make sure
     * the user can switch a theme.
     *
     * @since 1.0.0
     *
     * @param $request
     * @return bool
     */
    public function switch_themes_permission_check($request) {
        return current_user_can('switch_themes');
    }

    /**
     * Update Theme Permission Check
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