<?php

include_once ABSPATH . 'wp-includes/rest-api/fields/class-wp-rest-meta-fields.php';

class WP_REST_Theme_Meta_Fields extends WP_REST_Meta_Fields {

	/**
	 * Retrieves the object meta type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugin meta type.
	 */
	protected function get_meta_type() {
		return 'theme';
	}

	/**
	 * Retrieves the type for register_rest_field().
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugin REST field type.
	 */
	public function get_rest_field_type() {
		return 'theme';
	}
}
