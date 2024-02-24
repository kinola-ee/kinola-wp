<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Api\Exceptions\ApiException;
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

    protected function get_events( $url = null ): array {
        $response = Kinola_Api::get( $url, false );
        $events   = $response->get_data();

        if ( $response->has_next_link() ) {
            $events = array_merge( $events, $this->get_events( $response->get_next_link() ) );
        }

        return $events;
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
