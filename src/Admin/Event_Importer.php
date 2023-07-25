<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Api\Exceptions\ApiException;
use Kinola\KinolaWp\Api\Event as Api_Event;
use Kinola\KinolaWp\Api\Kinola_Api;
use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Film;

class Event_Importer {

    protected string $events_endpoint = 'events?limit=500';

    public function import() {
        try {
            $response = Kinola_Api::get( $this->get_endpoint(), false );
        } catch ( ApiException $e ) {
            echo $e->getMessage();
            die;
        }

        foreach ( $response->get_data() as $event ) {
            $this->save_event( new Api_Event( $event ) );
        }
    }

    protected function get_endpoint(): string {
        return $this->events_endpoint . '&time_from=' . gmdate( "Y-m-d\TH:i:s\Z" );
    }

    protected function save_event( Api_Event $api_event ): Event {

        $event = Event::find_by_remote_id( $api_event->get_id() );

        if ( ! $event ) {
            $event = Event::create( $api_event );
        } else {
            $event->set_title( $api_event->get_field( 'post_title' ), $api_event->get_field( 'time' ) );
            $event->save_api_data( $api_event );
        }

        // Check if a corresponding film exists. If not, import it.
        $film = Film::find_by_remote_id( $event->get_field( Film::FIELD_ID ) );
        if ( ! $film ) {
            $importer = new Film_Importer();
            $film     = $importer->import_film( $event->get_field( Film::FIELD_ID ) );
            if (!$film) {
                trigger_error("Failed to import film ID " . $event->get_field( Film::FIELD_ID ), E_USER_WARNING);
            }
        }

        // If the corresponding film is published, also publish the event.
        // If the film is draft or deleted, set the event to draft.
        if ($film->get_post()->post_status === 'publish') {
            $event_status = 'publish';
        } else {
            $event_status = 'draft';
        }

        wp_update_post([
            'ID' => $event->get_local_id(),
            'post_status' => $event_status,
        ]);

        return $event;
    }
}
