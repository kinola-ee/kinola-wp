<?php

namespace Kinola\KinolaWp;

class Helpers {

    /**
     * Term meta key holding a venue's Kinola UUID. Venues are matched on import by
     * this id (not by name), so a venue renamed in Kinola updates its term in place
     * instead of spawning a duplicate — the same identity pattern films/events use
     * via their FIELD_ID post meta.
     */
    public const TERM_META_KINOLA_ID = 'kinola_venue_id';

    /**
     * Term meta key holding a venue's address (the import payload's venue details
     * minus name and id), persisted for schema.org MovieTheater output. It lives
     * here — not on the schema class that reads it — so the import write path and
     * the schema read path both depend on this shared key rather than on each other.
     */
    public const TERM_META_VENUE_ADDRESS = 'kinola_venue_address';

    /**
     * Option key for the "Structured data" toggle. It lives here — not on the
     * Admin\Settings class that registers and renders it — so is_schema_enabled()
     * (foundation) can read it without depending on the admin/UI layer; Settings
     * references it from here too.
     */
    public const OPTION_SCHEMA_ENABLED = 'kinola_schema_enabled';

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

    public static function is_schema_enabled(): bool {
        return apply_filters(
            'kinola/schema/enabled',
            get_option( self::OPTION_SCHEMA_ENABLED, '1' ) === '1'
        );
    }

    /**
     * Build a site-timezone DateTime from a UTC datetime string, or null if it can't be parsed.
     * new \DateTime() throws on a malformed value on PHP 8 — which would fatal a public page (the
     * schema and the date/time getters all run on the frontend) — so a bad stored value degrades to
     * null here and every caller omits the date rather than crashing.
     */
    public static function format_datetime( string $date_time_string ): ?\DateTime {
        // An empty/blank value is not a date: new \DateTime('') silently means "now", which would
        // render a missing time as the current moment. Treat it as unparseable like any other bad
        // input, so callers omit the date rather than show a wrong one.
        if ( trim( $date_time_string ) === '' ) {
            return null;
        }

        try {
            $date_time = new \DateTime( $date_time_string, new \DateTimeZone( "UTC" ) );
        } catch ( \Exception $e ) {
            return null;
        }

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
     * Parse venue names into a clean array.
     *
     * Accepts either a comma-separated string or an array of names, so callers holding an array
     * don't have to implode-then-split. Trims whitespace, removes empty values, and — when
     * $sanitize is set (for user input from $_GET, shortcode atts, etc.) — sanitizes and bounds
     * the input.
     *
     * @param string|array $venues   Comma-separated venue names, or an array of names.
     * @param bool         $sanitize Whether to sanitize and validate (use true for user input).
     * @return array Array of trimmed venue names.
     */
    public static function parse_venue_names( $venues, bool $sanitize = false ): array {
        $is_array_input = is_array( $venues );

        // Normalize to a list of raw name strings.
        if ( $is_array_input ) {
            $names = array_map( static function ( $name ) {
                return (string) $name;
            }, $venues );
        } else {
            $venues_string = (string) $venues;

            if ( $sanitize ) {
                $venues_string = sanitize_text_field( wp_unslash( $venues_string ) );

                // Validate input length (max 10KB for DoS protection)
                if ( strlen( $venues_string ) > 10240 ) {
                    return [];
                }
            }

            $names = $venues_string === '' ? [] : explode( ',', $venues_string );
        }

        // The string path is sanitized whole above; an array of user input needs each element done.
        if ( $sanitize && $is_array_input ) {
            $names = array_map( static function ( $name ) {
                return sanitize_text_field( wp_unslash( $name ) );
            }, $names );
        }

        // Trim, then drop empty values and over-long names.
        $names = array_values( array_filter(
            array_map( 'trim', $names ),
            static function ( $venue ) {
                return $venue !== '' && strlen( $venue ) <= 200;
            }
        ) );

        // Limit to a reasonable number for user input.
        if ( $sanitize && count( $names ) > 500 ) {
            $names = array_slice( $names, 0, 500 );
        }

        return $names;
    }

    /**
     * Convert allowed venue names to term IDs.
     *
     * Performs case-insensitive lookup of venue taxonomy terms by name.
     *
     * Returns the impossible term id [0] when nothing matches, NOT an empty array: this is built
     * for tax_query 'terms', where [0] forces zero results while [] would match everything. A
     * caller using the ids outside a tax_query (e.g. the schema layer) must array_filter the [0].
     *
     * @param array $allowed_venues Array of allowed venue names to look up.
     * @return array Array of term IDs, or [0] if none found (the no-results sentinel).
     */
    public static function get_venue_term_ids( array $allowed_venues ): array {
        if ( empty( $allowed_venues ) ) {
            return [];
        }

        $taxonomy_name = self::get_venue_taxonomy_name();

        // Batch load all venue terms in a single query, as id => name: the only fields this lookup
        // needs, so it does not hydrate full WP_Term objects.
        $all_terms = get_terms( [
            'taxonomy'   => $taxonomy_name,
            'hide_empty' => false,
            'fields'     => 'id=>name',
        ] );

        if ( is_wp_error( $all_terms ) ) {
            return [ 0 ];
        }

        // Build case-insensitive lookup set for performance
        $allowed_set = array_map( 'strtolower', $allowed_venues );
        $term_ids = [];

        foreach ( $all_terms as $term_id => $name ) {
            if ( in_array( strtolower( $name ), $allowed_set, true ) ) {
                $term_ids[] = (int) $term_id;
            }
        }

        // Return impossible term ID to ensure no results if no valid venues
        return empty( $term_ids ) ? [ 0 ] : $term_ids;
    }

    /**
     * In-request cache of resolved venue terms, keyed by Kinola id. Positive matches only (mirrors
     * Film::$by_remote_id): a not-yet-existing venue is re-queried, which matters during an import
     * where a venue is created and then looked up again for the next event. The get_terms() call
     * below — filtered by meta_key/meta_value — is a JOIN that WP's term cache does not serve, so
     * without this an N-event import issues N such queries for the same handful of venues.
     *
     * @var array<string,\WP_Term>
     */
    private static array $venue_term_by_kinola_id = [];

    /**
     * Find a venue term by its Kinola UUID — the term-level counterpart of
     * Film/Event::find_by_remote_id(). Returns null on empty id, no match, or error.
     */
    public static function find_venue_term_by_kinola_id( string $kinola_id ): ?\WP_Term {
        $kinola_id = trim( $kinola_id );
        if ( $kinola_id === '' ) {
            return null;
        }

        if ( isset( self::$venue_term_by_kinola_id[ $kinola_id ] ) ) {
            return self::$venue_term_by_kinola_id[ $kinola_id ];
        }

        $terms = get_terms( [
            'taxonomy'   => self::get_venue_taxonomy_name(),
            'hide_empty' => false,
            'meta_key'   => self::TERM_META_KINOLA_ID,
            'meta_value' => $kinola_id,
            'number'     => 1,
        ] );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return null;
        }

        return self::$venue_term_by_kinola_id[ $kinola_id ] = $terms[0];
    }

    /**
     * Drop a cached id->term entry after the term's identity changes (e.g. a rename during import),
     * so the next find_venue_term_by_kinola_id() re-reads it. Mirrors Film::invalidate().
     */
    public static function invalidate_venue_term_cache( string $kinola_id ): void {
        unset( self::$venue_term_by_kinola_id[ trim( $kinola_id ) ] );
    }
}
