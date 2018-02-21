<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class FinishRestApi_Response {

    public static function respond($data, $singular = false) {
        self::check_data($data);
        if($singular) {
            $response = ['success' => true, 'total_records' => 1, 'data' => $data];
        } else {
            $response = ['success' => true, 'total_records' => count($data), 'data' => $data];
        }
        return new WP_REST_Response($response, 200);
    }

    /**
     * @param $code
     * @param $message
     * @param null $data
     * @return WP_Error
     */
    public static function error($code, $message, $data = null) {
        return new WP_Error($code, $message, $data);
    }

    /**
     * @param $data
     * @throws Exception
     */
    protected static function check_data($data) {
        if(!is_object($data) && !is_array($data)) {
            throw new Exception('Unable to create response with this information.');
        }
    }

}