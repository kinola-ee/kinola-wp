<?php

namespace Kinola\KinolaWp\Api;

use Kinola\KinolaWp\Api\Exceptions\ApiException;
use Kinola\KinolaWp\Api\Exceptions\EmptyResponseException;
use Kinola\KinolaWp\Api\Exceptions\JsonDecodeException;

class Kinola_Api {
	public const API_PATH = 'api/public/v1';
	public const PLUGIN_API_PATH = 'api/plugin/v1';

	/**
	 * This function accepts either an endpoint (which is used to build the full URL)
	 * or a full URL (which is used directly without modifications).
	 */
	public static function get( string $endpoint_or_url ): Response {
		if ( filter_var( $endpoint_or_url, FILTER_VALIDATE_URL ) ) {
			$full_url = $endpoint_or_url;
		} else {
			$full_url = self::build_url( $endpoint_or_url );
		}

		$result = wp_remote_get( $full_url, [ 'timeout' => 10 ] );

		if ( is_wp_error( $result ) ) {
			/* @var $result \WP_Error */
			throw new ApiException( implode( ', ', $result->get_error_messages() ) );
		}

		if ( empty( $result['body'] ) ) {
			throw new EmptyResponseException();
		}

		$response_data = json_decode( $result['body'], true );

		if ( is_null( $response_data ) ) {
			throw new JsonDecodeException();
		}

		return new Response( $response_data );
	}

	public static function build_url( string $endpoint ): string {
		return trailingslashit(self::get_api_base_url()) . $endpoint;
	}

	public static function get_api_base_url(): string {
		return trailingslashit( KINOLA_URL ) . self::API_PATH;
	}

	public static function get_plugin_api_base_url(): string {
		return trailingslashit( KINOLA_URL ) . self::PLUGIN_API_PATH;
	}
}