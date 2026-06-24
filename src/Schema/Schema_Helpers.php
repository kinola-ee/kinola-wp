<?php

namespace Kinola\KinolaWp\Schema;

/**
 * Stateless utilities shared by the schema node builders (Movie_Schema,
 * Screening_Event_Schema).
 */
class Schema_Helpers {

    /**
     * Translatable list fields come back as an array or a comma-separated
     * string depending on how they were stored; normalize to a clean array.
     */
    public static function normalize_list( $value ): array {
        if ( ! $value ) {
            return [];
        }

        if ( is_string( $value ) ) {
            $value = explode( ',', $value );
        }

        if ( ! is_array( $value ) ) {
            return [];
        }

        return array_values( array_filter( array_map( 'trim', $value ) ) );
    }

    /**
     * Normalize a URL for the JSON-LD, or null if it is not a usable absolute http(s) URL. The schema
     * fields that take a URL (image, url, embedUrl) must be absolute: a protocol-relative //host/path is
     * upgraded to https, and anything still lacking an http/https scheme — a relative path, a bare host,
     * a javascript:/data: scheme, or arbitrary text — is rejected. The data is from the trusted Kinola
     * API and JSON-LD is not an execution context, so this is a validation guard (keep the schema valid
     * and absolute), not an XSS control.
     */
    public static function safe_url( $url ): ?string {
        $url = trim( (string) $url );
        if ( $url === '' ) {
            return null;
        }

        // Protocol-relative (//host/path) → https, so it becomes a valid absolute URL.
        if ( strpos( $url, '//' ) === 0 ) {
            $url = 'https:' . $url;
        }

        $scheme = strtolower( (string) parse_url( $url, PHP_URL_SCHEME ) );
        if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
            return null;
        }

        return $url;
    }
}
