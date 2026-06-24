<?php

namespace Kinola\KinolaWp;

class Event_Query {
    public const DATE_FORMAT = "Y-m-d\TH:i:s\Z";
    protected array $params;
    protected array $events;

    public function __construct() {
        $this->params = [
            'post_type'      => Helpers::get_events_post_type(),
            'posts_per_page' => - 1,
            // found_posts is never consumed by any caller (events are not paginated), so skip the
            // SQL_CALC_FOUND_ROWS cost. Set in the constructor, it applies to every query built here
            // — get(), get_ids(), and the limit()'d listing query alike.
            'no_found_rows'  => true,
            'meta_key'       => 'time',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => [],
            'post_status'    => 'publish',
        ];
    }

    public function limit( int $limit ): Event_Query {
        $this->params['posts_per_page'] = $limit;

        return $this;
    }

    public function upcoming(): Event_Query {
        return $this->since( gmdate( self::DATE_FORMAT ) );
    }

    /**
     * Restrict to events whose start time is at or after the given UTC datetime
     * (self::DATE_FORMAT). Uses the same lexical ISO-8601 comparison as upcoming().
     */
    public function since( string $utc_datetime ): Event_Query {
        $this->params['meta_query'] = array_merge( [
            [
                'key'     => 'time',
                'value'   => $utc_datetime,
                'compare' => '>=',
            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    /**
     * Post IDs of the events this query matches, without hydrating them into Event
     * objects, for callers that only need IDs and then batch-load just the meta they
     * want. This is the shared fields => 'ids' shortcut get_venue_term_ids() builds on.
     *
     * @return int[]
     */
    public function get_ids(): array {
        $params           = $this->params;
        $params['fields'] = 'ids';

        return array_map( 'intval', ( new \WP_Query( $params ) )->posts );
    }

    /**
     * Distinct venue term IDs among the events this query matches. Runs the query
     * for IDs only, then resolves every venue in a single term query.
     *
     * @return int[]
     */
    public function get_venue_term_ids(): array {
        $event_ids = $this->get_ids();
        if ( empty( $event_ids ) ) {
            return [];
        }

        $term_ids = wp_get_object_terms(
            $event_ids,
            Helpers::get_venue_taxonomy_name(),
            [ 'fields' => 'ids' ]
        );

        if ( is_wp_error( $term_ids ) ) {
            return [];
        }

        return array_values( array_unique( array_map( 'intval', $term_ids ) ) );
    }

    public function film( $film_remote_id ): Event_Query {
        if ($film_remote_id === 'all') {
            return $this;
        }

        $this->params['meta_query'] = array_merge( [
            [
                'key'   => Film::FIELD_ID,
                'value' => $film_remote_id,
            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    public function program( $program_remote_id ): Event_Query {
        if ($program_remote_id === 'all') {
            return $this;
        }

        $this->params['meta_query'] = array_merge( [
            [
                'key'   => \Kinola\KinolaWp\Program::FIELD_ID,
                'value' => $program_remote_id,
            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    public function date( $date ): Event_Query {
        // The date in the database is in UTC time zone, so we need to convert it to whatever is configured in WP.
        $selected_date_utc = new \DateTime( $date, new \DateTimeZone( wp_timezone_string() ) );
        $selected_date_utc->setTimezone( new \DateTimeZone( 'UTC' ) );

        $this->params['meta_query'] = array_merge( [
            [
                'key'     => 'time',
                'value'   => [
                    $selected_date_utc->format( "Y-m-d\TH:i:s\Z" ),
                    $selected_date_utc->add( \DateInterval::createFromDateString( '23 hours 59 minutes' ) )->format( self::DATE_FORMAT ),
                ],
                'compare' => 'BETWEEN',
            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    public function time( $time ): Event_Query {

        // The time filter needs to take daylight savings time into account.
        // For example, if a user queries all events with start time 19:00, then this may actually translate into two
        // different UTC times if DST starts or ends within the time period of the query.
        // To work around this, we make three OR conditions with different UTC times, set between the next DST changes.

        // Get the upcoming DST transition timestamps
        $zone        = new \DateTimeZone( wp_timezone_string() );
        $transitions = $zone->getTransitions( time() );

        // If the time zone does not have DST, just filter for time
        if ( ! $transitions ) {
            $selected_time_today_utc = new \DateTime( $time, new \DateTimeZone( wp_timezone_string() ) );
            $selected_time_today_utc->setTimezone( new \DateTimeZone( 'UTC' ) );

            $this->params['meta_query'] = array_merge( [
                [
                    'key'     => 'time',
                    'value'   => $selected_time_today_utc->format( 'H:i:s' ),
                    'compare' => 'LIKE',
                ],
            ], $this->params['meta_query'] ?? [] );

            return $this;
        }

        // Note the double nested meta query - this is so that we would have an AND relation with the default meta
        // query that queries only future events.
        $this->params['meta_query'] = array_merge( [
            [
                'relation' => 'OR',
                $this->getTimeMetaQuery( $time, $transitions[0]['time'], $transitions[1]['time'], $transitions[0]['offset'] ),
                $this->getTimeMetaQuery( $time, $transitions[1]['time'], $transitions[2]['time'], $transitions[1]['offset'] ),

            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    public function venue( $venue ): Event_Query {
        $this->params['tax_query'] = array_merge( [
            [
                'taxonomy' => Helpers::get_venue_taxonomy_name(),
                'field'    => 'slug',
                'terms'    => $venue,
            ],
        ], $this->params['tax_query'] ?? [] );

        return $this;
    }

    /**
     * Filter events by allowed venues.
     *
     * Looks up venue taxonomy terms by name and adds them to the tax_query.
     * Invalid venue names are silently ignored.
     *
     * @param array $allowed_venues Array of allowed venue names to filter by.
     * @return self Returns $this for method chaining.
     */
    public function filterByAllowedVenues( array $allowed_venues ): Event_Query {
        if ( empty( $allowed_venues ) ) {
            return $this;
        }

        // Use centralized helper for term ID lookup
        $term_ids = Helpers::get_venue_term_ids( $allowed_venues );
        $taxonomy_name = Helpers::get_venue_taxonomy_name();

        $this->params['tax_query'] = array_merge( [
            [
                'taxonomy' => $taxonomy_name,
                'field'    => 'term_id',
                'terms'    => $term_ids,
                'operator' => 'IN',
            ],
        ], $this->params['tax_query'] ?? [] );

        return $this;
    }

    public function filter( $date = null, $venue = null, $time = null, $film = null ): Event_Query {
        if ( $date && $date !== 'all' ) {
            $this->date( $date );
        }

        if ( $venue && $venue !== 'all' ) {
            $this->venue( $venue );
        }

        if ( $time && $time !== 'all' ) {
            $this->time( $time );
        }

        if ( $film && $film !== 'all' ) {
            $this->film( $film );
        }

        return $this;
    }

    public function get(): array {
        if ( isset( $this->events ) && ! is_null( $this->events ) ) {
            return $this->events;
        }

        $this->events = [];
        $eventPosts   = ( new \WP_Query( $this->params ) )->posts;

        if ( count( $eventPosts ) ) {
            foreach ( $eventPosts as $eventPost ) {
                $this->events[] = new Event( $eventPost );
            }
        }

        return $this->events;
    }

    protected function getTimeMetaQuery( string $time, string $start, string $end, int $offsetSeconds ): array {
        $offsetInterval = \DateInterval::createFromDateString( "{$offsetSeconds} seconds" );

        return [
            'relation' => 'AND',
            [
                'key'     => 'time',
                'value'   => [
                    ( new \DateTime( $start ) )->format( self::DATE_FORMAT ),
                    ( new \DateTime( $end ) )->format( self::DATE_FORMAT ),
                ],
                'compare' => 'BETWEEN',
            ],
            [
                'key'     => 'time',
                'value'   => ( new \DateTime( $time ) )->sub( $offsetInterval )->format( 'H:i:s' ),
                'compare' => 'LIKE',
            ],
        ];
    }
}
