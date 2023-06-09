<?php

namespace Kinola\KinolaWp;

class EventQuery {
    protected array $params;

    public function __construct() {
        $this->params = [
            'post_type'      => Helpers::get_events_post_type(),
            'posts_per_page' => - 1,
            'meta_key'       => 'time',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => [],
        ];
    }

    public function upcoming(): EventQuery {
        $this->params['meta_query'] = array_merge( [
            [
                'key'     => 'time',
                'value'   => gmdate( "Y-m-d\TH:i:s\Z" ),
                'compare' => '>=',
            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    public function film( $film_remote_id ): EventQuery {
        $this->params['meta_query'] = array_merge( [
            [
                'key'   => Film::FIELD_ID,
                'value' => $film_remote_id,
            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    public function date( $date ): EventQuery {
        // The date in the database is in UTC time zone, so we need to convert it to whatever is configured in WP.
        $selected_date_utc = new \DateTime( $date, new \DateTimeZone( wp_timezone_string() ) );
        $selected_date_utc->setTimezone( new \DateTimeZone( 'UTC' ) );

        $this->params['meta_query'] = array_merge( [
            [
                'key'     => 'time',
                'value'   => [
                    $selected_date_utc->format( "Y-m-d\TH:i:s\Z" ),
                    $selected_date_utc->add( \DateInterval::createFromDateString( '23 hours 59 minutes' ) )->format( "Y-m-d\TH:i:s\Z" ),
                ],
                'compare' => 'BETWEEN',
            ],
        ], $this->params['meta_query'] ?? [] );

        return $this;
    }

    public function location( $location ): EventQuery {
        $this->params['tax_query'] = array_merge( [
            [
                [
                    'taxonomy' => Helpers::get_venue_taxonomy_name(),
                    'field'    => 'slug',
                    'terms'    => $location,
                ],
            ],
        ], $this->params['tax_query'] ?? [] );

        return $this;
    }

    public function filter( $date = null, $location = null ): EventQuery {
        if ( $date ) {
            $this->date( $date );
        }

        if ( $location ) {
            $this->location( $location );
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
}
