<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once ABSPATH . '/wp-admin/includes/class-wp-upgrader-skin.php';
require_once ABSPATH . '/wp-admin/includes/misc.php';

class FinishRestApi_Skin extends WP_Upgrader_Skin {
    public $upgrader;
    public $done_header = false;
    public $done_footer = false;

    /**
     * Holds the result of an upgrade.
     *
     * @since 2.8.0
     * @access public
     * @var string|bool|WP_Error
     */
    public $result = [];
    public $options = array();

    /**
     *
     * @param array $args
     */
    public function __construct($args = array()) {
        $defaults = array( 'url' => '', 'nonce' => '', 'title' => '', 'context' => false );
        $this->options = wp_parse_args($args, $defaults);
    }

    /**
     * @param WP_Upgrader $upgrader
     */
    public function set_upgrader(&$upgrader) {
        if ( is_object($upgrader) )
            $this->upgrader =& $upgrader;
        $this->add_strings();
    }


    /**
     * @access public
     */
    public function header() {

    }

    /**
     * @access public
     */
    public function footer() {

    }

    /**
     *
     * @param string|WP_Error $errors
     */
    public function error($errors) {
        $this->result['error'] = $errors;
    }

    /**
     *
     * @param string $string
     */
    public function set_result($string) {
        $this->result['success'] = $string;
    }

    /**
     *
     * @param string $string
     */
    public function feedback($string) {

    }

    /**
     * @access public
     */
    public function before() {}

    /**
     * @access public
     */
    public function after() {}


    /**
     * @access public
     */
    public function bulk_header() {}

    /**
     * @access public
     */
    public function bulk_footer() {}
}