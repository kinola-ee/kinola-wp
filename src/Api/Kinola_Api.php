<?php

namespace Kinola\KinolaWp\Api;

use Kinola\KinolaWp\Api\Exceptions\ApiException;
use Kinola\KinolaWp\Api\Exceptions\EmptyResponseException;
use Kinola\KinolaWp\Api\Exceptions\JsonDecodeException;

class Kinola_Api {

    // Public API for productions, events, etc
    public const API_PATH = 'api/public/v1';

    // Checkout
    public const PLUGIN_API_PATH = 'api/plugin/v1';

    /**
     * This function accepts either an endpoint (which is used to build the full URL)
     * or a full URL (which is used directly without modifications).
     */
    public static function get( string $endpoint_or_url, bool $with_translations = true ): Response {
        if ( filter_var( $endpoint_or_url, FILTER_VALIDATE_URL ) ) {
            $full_url = $with_translations ? self::maybe_add_translations_param($endpoint_or_url) : $endpoint_or_url;
        } else {
            $full_url = self::build_url( $endpoint_or_url, $with_translations );
        }

        debug_log( "API: Making request to {$full_url}" );

        $result = wp_remote_get( $full_url, [ 'timeout' => 10 ] );

        if ( is_wp_error( $result ) ) {
            /* @var $result \WP_Error */
            throw new ApiException( implode( ', ', $result->get_error_messages() ) );
        }

        if ( empty( $result['body'] ) ) {
            throw new EmptyResponseException();
        }

        $response_data = json_decode( $result['body'], true );

        debug_log( "API: response data: " );
        debug_log( $response_data );

        if ( is_null( $response_data ) ) {
            throw new JsonDecodeException();
        }

        return new Response( $response_data );
    }

    public static function build_url( string $endpoint, bool $with_translations = true ): string {
        $url = trailingslashit( self::get_api_base_url() ) . $endpoint;

        if ( $with_translations ) {
            if ( stristr( $url, '?' ) === false ) {
                $url .= '?lang=all';
            } else {
                $url .= '&lang=all';
            }
        }

        return $url;
    }

    public static function maybe_add_translations_param( string $url ): string {
        if ( stristr( $url, 'lang=' ) !== false ) {
            return $url;
        }

        if ( stristr( $url, '?' ) === false ) {
            $url .= '?lang=all';
        } else {
            $url .= '&lang=all';
        }

        return $url;
    }

    public static function get_api_base_url(): string {
        return trailingslashit( KINOLA_URL ) . self::API_PATH;
    }

    public static function get_plugin_api_base_url(): string {
        return trailingslashit( KINOLA_URL ) . self::PLUGIN_API_PATH;
    }
}
