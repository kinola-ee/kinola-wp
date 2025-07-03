<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Api\Event as Api_Event;
use Kinola\KinolaWp\Api\Kinola_Api;
use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Helpers;

class Event_Importer {

    protected string $events_endpoint = 'events?limit=500';

    public function import() {
        debug_log( "Event import: Fetching events from endpoint " . $this->get_endpoint() );

        $saved_event_ids = [];
        $events          = $this->get_events( $this->get_endpoint() );

        debug_log( "Event import: API returned a total of " . count( $events ) . " events." );

        foreach ( $events as $event ) {
            $saved_event_id = $this->save_event( new Api_Event( $event ) );
            if ( $saved_event_id ) {
                $saved_event_ids[] = $saved_event_id;
            }
        }

        debug_log( "Event import: Saved a total of " . count( $saved_event_ids ) . " events." );

        $this->deleted_event_cleanup( $saved_event_ids );
        $this->past_event_cleanup();

        debug_log( "Event import: Completed successfully." );
    }

    protected function get_events( $url = null, $recursion_depth = 0 ): array {
        if ( $recursion_depth > 20 ) {
            debug_log( "Event import: Reached maximum recursion depth of 20 pages. Stopping pagination." );
            return [];
        }

        try {
            $response = Kinola_Api::get( $url, false );
            $events   = $response->get_data();

            if ( $response->has_next_link() ) {
                $next_url = $response->get_next_link();
                
                // Preserve original query parameters in pagination URLs
                $original_params = [];
                
                // Handle both full URLs and endpoint strings
                if ( $url ) {
                    if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
                        $original_query = parse_url( $url, PHP_URL_QUERY );
                    } else {
                        $parts = explode( '?', $url, 2 );
                        $original_query = isset( $parts[1] ) ? $parts[1] : '';
                    }
                    
                    if ( $original_query ) {
                        parse_str( $original_query, $original_params );
                    }
                }
                
                // Parse next URL and merge with original parameters
                $next_parts = parse_url( $next_url );
                $next_params = [];
                if ( isset( $next_parts['query'] ) ) {
                    parse_str( $next_parts['query'], $next_params );
                }
                
                // Preserve important parameters from original request
                if ( isset( $original_params['limit'] ) && ! isset( $next_params['limit'] ) ) {
                    $next_params['limit'] = $original_params['limit'];
                }
                if ( isset( $original_params['time_from'] ) && ! isset( $next_params['time_from'] ) ) {
                    $next_params['time_from'] = $original_params['time_from'];
                }
                
                // Rebuild URL with merged parameters
                $next_url = $next_parts['scheme'] . '://' . $next_parts['host'] . $next_parts['path'] . '?' . http_build_query( $next_params );
                
                $events = array_merge( $events, $this->get_events( $next_url, $recursion_depth + 1 ) );
            }

            return $events;
        } catch ( \Exception $e ) {
            debug_log( "Event import: Error fetching events at recursion depth {$recursion_depth}: " . $e->getMessage() );
            return [];
        }
    }

    protected function get_endpoint(): string {
        return $this->events_endpoint . '&time_from=' . gmdate( "Y-m-d\TH:i:s\Z" );
    }

    protected function save_event( Api_Event $api_event ): ?string {
        // Check if a corresponding film exists. If not, check if it's public. If it is, then import it.
        $film = Film::find_by_remote_id( $api_event->get_field( Film::FIELD_ID ) );
        if ( ! $film ) {
            $importer = new Film_Importer();
            $film     = $importer->import_film( $api_event->get_field( Film::FIELD_ID ) );

            // If there's no film, do not import related event either.
            if ( ! $film ) {
                debug_log( "Event import: No matching film for Event ID {$api_event->get_id()}, skipping import." );

                return null;
            }
        }

        // Create the event
        $event = Event::find_by_remote_id( $api_event->get_id() );

        if ( ! $event ) {
            $event = Event::create( $api_event );
            debug_log( "Event import: Created event #{$event->get_local_id()} ID {$event->get_remote_id()}." );
        } else {
            $event->set_title( $film->get_title(), $api_event->get_field( 'time' ) );
            $event->save_api_data( $api_event );
            debug_log( "Event import: Updated event #{$event->get_local_id()} ID {$event->get_remote_id()}." );
        }

        // If the corresponding film is published, also publish the event.
        // If the film is draft or deleted, set the event to draft.
        if ( $film->get_post()->post_status === 'publish' ) {
            $event_status = 'publish';
        } else {
            $event_status = 'draft';
        }

        wp_update_post( [
            'ID'          => $event->get_local_id(),
            'post_status' => $event_status,
        ] );

        return $event->get_local_id();
    }

    protected function deleted_event_cleanup( array $ids ) {
        $query = new \WP_Query( [
            'post_type'      => Helpers::get_events_post_type(),
            'posts_per_page' => - 1,
            'post__not_in'   => $ids,
        ] );

        $deleted_events = $query->get_posts();

        foreach ( $deleted_events as $event_post ) {
            $event = new Event( $event_post );
            debug_log( "Event import: Event #{$event->get_local_id()} ID {$event->get_remote_id()} deleted in Kinola. Removing event from WP." );
            $event->delete();
        }
    }

    protected function past_event_cleanup() {
        $today_utc = new \DateTime( 'today', new \DateTimeZone( wp_timezone_string() ) );
        $today_utc->setTimezone( new \DateTimeZone( 'UTC' ) );

        $query = new \WP_Query( [
            'post_type'      => Helpers::get_events_post_type(),
            'posts_per_page' => - 1,
            'meta_query'     => [
                [
                    'key'     => 'time',
                    'value'   => $today_utc->format( "Y-m-d\TH:i:s\Z" ),
                    'compare' => '<',
                ],
            ],
        ] );

        $events = $query->get_posts();

        foreach ( $events as $eventPost ) {
            $event = new Event( $eventPost );
            $event->delete();
        }

        debug_log( "Event import: Deleted a total of " . count( $events ) . " past events." );
    }
}
