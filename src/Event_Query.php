<?php

namespace Kinola\KinolaWp;

class Event_Query {
    public const DATE_FORMAT = "Y-m-d\TH:i:s\Z";
    protected array $params;

    public function __construct() {
        $this->params = [
            'post_type'      => Helpers::get_events_post_type(),
            'posts_per_page' => - 1,
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
        $this->params['meta_query'] = array_merge( [
            [
                'key'     => 'time',
                'value'   => gmdate( "Y-m-d\TH:i:s\Z" ),
                'compare' => '>=',
            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    public function film( $film_remote_id ): Event_Query {
        $this->params['meta_query'] = array_merge( [
            [
                'key'   => Film::FIELD_ID,
                'value' => $film_remote_id,
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
                [
                    'taxonomy' => Helpers::get_venue_taxonomy_name(),
                    'field'    => 'slug',
                    'terms'    => $venue,
                ],
            ],
        ], $this->params['tax_query'] ?? [] );

        return $this;
    }

    public function filter( $date = null, $venue = null, $time = null ): Event_Query {
        if ( $date && $date !== 'all' && $date !== __( 'all', 'kinola' ) ) {
            $this->date( $date );
        }

        if ( $venue && $venue !== 'all' && $venue !== __( 'all', 'kinola' ) ) {
            $this->venue( $venue );
        }

        if ( $time && $time !== 'all' && $time !== __( 'all', 'kinola' ) ) {
            $this->time( $time );
        }

        return $this;
    }

    public function get(): array {
        $events     = [];
        $eventPosts = ( new \WP_Query( $this->params ) )->posts;

        if ( count( $eventPosts ) ) {
            foreach ( $eventPosts as $eventPost ) {
                $events[] = new Event( $eventPost );
            }
        }

        return $events;
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
