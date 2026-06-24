<?php

if ( ! function_exists( 'dd' ) ) {
    function dd( $var ) {
        var_dump( $var );
        die;
    }
}

if ( ! function_exists( 'debug_log' ) ) {
    function debug_log( $log ) {
        if (!defined('KINOLA_DEBUG_LOG') || !KINOLA_DEBUG_LOG) {
            return;
        }

        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}

if ( ! function_exists( 'kinola_get_schema_manager' ) ) {
    /**
     * Return the bootstrap-owned schema manager, or null before the plugin has booted.
     */
    function kinola_get_schema_manager(): ?\Kinola\KinolaWp\Schema\Schema_Manager {
        $bootstrap = $GLOBALS['KINOLA_BOOTSTRAP'] ?? null;

        return $bootstrap instanceof \Kinola\KinolaWp\Bootstrap ? $bootstrap->schema() : null;
    }
}

if ( ! function_exists( 'kinola_get_film_schema' ) ) {
    /**
     * Return JSON-LD markup for a published film and its upcoming screenings.
     *
     * @param int $film_id WordPress post ID of the film.
     * @return string A script tag, or an empty string when unavailable or disabled.
     */
    function kinola_get_film_schema( int $film_id ): string {
        $schema = kinola_get_schema_manager();
        if ( ! $schema ) {
            return '';
        }

        $film = \Kinola\KinolaWp\Film::find_by_local_id( $film_id );
        if ( ! $film ) {
            return '';
        }

        return $schema->get_film_screenings_schema( $film );
    }
}

if ( ! function_exists( 'kinola_get_events_schema' ) ) {
    /**
     * Return JSON-LD markup for an upcoming screenings query.
     *
     * @param array $args {
     *     @type string       $show_dates     'upcoming' or 'today'. Default 'upcoming'.
     *     @type int|string   $limit          Max events, or 'all'. Default 25.
     *     @type string|array $allowed_venues Venue names to restrict to.
     * }
     * @return string A script tag, or an empty string when unavailable or disabled.
     */
    function kinola_get_events_schema( array $args = [] ): string {
        $schema = kinola_get_schema_manager();
        if ( ! $schema ) {
            return '';
        }

        $args = array_merge( [
            'show_dates'     => 'upcoming',
            'limit'          => 25,
            'allowed_venues' => '',
        ], $args );

        $limit = $args['limit'] === 'all'
            ? - 1
            : ( is_numeric( $args['limit'] ) ? (int) $args['limit'] : 25 );

        // A plain query, deliberately without Pages\Filter: that reads request-scoped UI selections,
        // which have no meaning for a programmatic call.
        $query = ( new \Kinola\KinolaWp\Event_Query() )->upcoming()->limit( $limit );

        if ( $args['show_dates'] === 'today' ) {
            $query->date( 'today' );
        }

        $allowed_venues = \Kinola\KinolaWp\Helpers::parse_venue_names( $args['allowed_venues'] );
        if ( $allowed_venues ) {
            $query->filterByAllowedVenues( $allowed_venues );
        }

        return $schema->get_events_page_schema( $query->get() );
    }
}

if ( ! function_exists( 'kinola_get_venues_schema' ) ) {
    /**
     * Return JSON-LD markup for venues.
     *
     * @param string|array $names Optional venue names to limit output to.
     * @return string A script tag, or an empty string when unavailable or disabled.
     */
    function kinola_get_venues_schema( $names = '' ): string {
        $schema = kinola_get_schema_manager();
        if ( ! $schema ) {
            return '';
        }

        // get_venue_schema() runs the name(s) through Helpers::parse_venue_names(), which accepts a
        // string or an array, so pass either through unchanged.
        return $schema->get_venue_schema( [ 'name' => $names ] );
    }
}
