<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Api\Kinola_Api;

class Checkout {
	public static function get_title(): string {
		return get_bloginfo();
	}

	public static function get_plugin_api_base_url(): string {
		return Kinola_Api::get_plugin_api_base_url();
	}

	public static function get_kinola_js_url(): string {
		return 'https://plugin.kinola.ee/index.js';
	}

	public static function get_event_id() {
		global $wp_query;
		return $wp_query->query_vars[ Helpers::get_checkout_url_slug() ];
	}
}