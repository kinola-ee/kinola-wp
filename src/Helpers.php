<?php

namespace Kinola\KinolaWp;

class Helpers {

    public static function has_newsletter_checkbox(): bool {
        return defined( 'KINOLA_SHOW_NEWSLETTER_CHECKBOX' ) && KINOLA_SHOW_NEWSLETTER_CHECKBOX;
    }

    public static function newsletter_checked_by_default(): bool {
        if ( !defined( 'KINOLA_NEWSLETTER_CHECKED_BY_DEFAULT' ) ) {
            return false;
        }

        return KINOLA_NEWSLETTER_CHECKED_BY_DEFAULT;
    }

    public static function get_checkout_terms_link(): string {
        if (!defined( 'KINOLA_TERMS_LINK' )) {
            return '';
        }

        if (!KINOLA_TERMS_LINK || KINOLA_TERMS_LINK === 'https://[YOUR_URL_HERE]') {
            return '';
        }

        return KINOLA_TERMS_LINK;
    }

    public static function get_language(): string {
        return apply_filters( 'kinola/language', self::get_language_from_locale( get_locale() ) );
    }

    public static function get_assets_url( string $path ): string {
        return trailingslashit( plugins_url( KINOLA_DIRECTORY . '/assets' ) ) . $path;
    }

    public static function get_checkout_url_slug(): string {
        return apply_filters( 'kinola/checkout/slug', 'checkout' );
    }

    public static function get_films_post_type(): string {
        return apply_filters( 'kinola/post_type/film', 'film' );
    }

    public static function get_events_post_type(): string {
        return apply_filters( 'kinola/post_type/event', 'event' );
    }

    public static function get_programs_post_type(): string {
        return apply_filters( 'kinola/post_type/program', 'program' );
    }

    public static function get_venue_taxonomy_name(): string {
        return apply_filters( 'kinola/taxonomy/venue', 'venue' );
    }

    public static function get_film_parameter_slug(): string {
        return apply_filters( 'kinola/filter/film/slug', 'selected_film' );
    }

    public static function get_venue_parameter_slug(): string {
        return apply_filters( 'kinola/filter/venue/slug', 'venue' );
    }

    public static function get_date_parameter_slug(): string {
        return apply_filters( 'kinola/filter/date/slug', 'date' );
    }

    public static function get_time_parameter_slug(): string {
        return apply_filters( 'kinola/filter/time/slug', 'time' );
    }

    public static function get_filter_parameter_value( string $slug ): ?string {
        if ( ! isset( $_GET[ $slug ] ) || ! $_GET[ $slug ] || $_GET[ $slug ] === 'all' ) {
            return null;
        }

        return sanitize_text_field( wp_unslash( $_GET[ $slug ] ) );
    }

    public static function format_datetime( string $date_time_string ): \DateTime {
        $date_time = new \DateTime( $date_time_string, new \DateTimeZone( "UTC" ) );
        $date_time->setTimezone( new \DateTimeZone( wp_timezone_string() ) );

        return $date_time;
    }

    public static function get_language_from_locale( string $locale ) {
        if ( stristr( $locale, '_' ) !== false ) {
            return explode( '_', $locale )[0];
        }

        if ( stristr( $locale, '-' ) !== false ) {
            return explode( '-', $locale )[0];
        }

        return $locale;
    }

    /**
     * Parse comma-separated venue names into array.
     *
     * Splits comma-separated venue names, trims whitespace, removes empty values,
     * and optionally sanitizes and validates for user input.
     *
     * @param string $venues_string Comma-separated venue names.
     * @param bool   $sanitize      Whether to sanitize and validate (use true for user input).
     * @return array Array of trimmed venue names.
     */
    public static function parse_venue_names( string $venues_string, bool $sanitize = false ): array {
        if ( empty( $venues_string ) ) {
            return [];
        }

        // Sanitize if this is user input (from $_GET, etc.)
        if ( $sanitize ) {
            $venues_string = sanitize_text_field( wp_unslash( $venues_string ) );

            // Validate input length (max 10KB for DoS protection)
            if ( strlen( $venues_string ) > 10240 ) {
                return [];
            }
        }

        // Split by comma and trim whitespace
        $venues = array_map( 'trim', explode( ',', $venues_string ) );

        // Remove empty values and validate individual venue length
        $venues = array_filter( $venues, function( $venue ) {
            return ! empty( $venue ) && strlen( $venue ) <= 200;
        } );

        // Limit to reasonable number
        if ( $sanitize && count( $venues ) > 500 ) {
            $venues = array_slice( $venues, 0, 500 );
        }

        return $venues;
    }

    /**
     * Convert allowed venue names to term IDs.
     *
     * Performs case-insensitive lookup of venue taxonomy terms by name.
     * Returns array of valid term IDs, or [0] if no valid venues found.
     *
     * @param array $allowed_venues Array of allowed venue names to look up.
     * @return array Array of term IDs, or [0] if none found (ensures no results).
     */
    public static function get_venue_term_ids( array $allowed_venues ): array {
        if ( empty( $allowed_venues ) ) {
            return [];
        }

        $taxonomy_name = self::get_venue_taxonomy_name();

        // Batch load all venue terms in a single query
        $all_terms = get_terms( [
            'taxonomy'   => $taxonomy_name,
            'hide_empty' => false,
        ] );

        // Build case-insensitive lookup set for performance
        $allowed_set = array_map( 'strtolower', $allowed_venues );
        $term_ids = [];

        foreach ( $all_terms as $term ) {
            /* @var $term \WP_Term */
            if ( in_array( strtolower( $term->name ), $allowed_set, true ) ) {
                $term_ids[] = (int) $term->term_id;
            }
        }

        // Return impossible term ID to ensure no results if no valid venues
        return empty( $term_ids ) ? [ 0 ] : $term_ids;
    }
}
