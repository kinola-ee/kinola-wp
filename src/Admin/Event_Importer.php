<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Api\Exceptions\ApiException;
use Kinola\KinolaWp\Api\Event as Api_Event;
use Kinola\KinolaWp\Api\Kinola_Api;
use Kinola\KinolaWp\Event;

class Event_Importer {

    protected string $events_endpoint = 'events?limit=500';

    public function import() {
        try {
            $response = Kinola_Api::get( $this->get_endpoint() );
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
            $event->set_title( $api_event->get_field( 'production_title' ), $api_event->get_field( 'time' ) );
            $event->save_api_data( $api_event );
        }

        return $event;
    }
}
