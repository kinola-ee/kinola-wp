<?php

namespace Kinola\KinolaWp\Api;

class Event {

    protected array $data = [];

    public function __construct( array $data ) {
        $this->data = $this->reformat( $data );
    }

    public function get_data(): array {
        return $this->data;
    }

    public function get_id() {
        return $this->get_field( \Kinola\KinolaWp\Event::FIELD_ID );
    }

    public function get_field( string $field ) {
        return $this->data[ $field ] ?? null;
    }

    protected function reformat( array $data ): array {
        $unset = [
            'id',
            'freeSeats',
            'program',
            'room',
            'venue',
            'production',
        ];

        $data[ \Kinola\KinolaWp\Event::FIELD_ID ] = $data['id'];
        $data[ \Kinola\KinolaWp\Film::FIELD_ID ]  = $data['production']['id'] ?? null;
        $data['production_title']                 = $data['production']['name'] ?? null;
        $data['production_poster']                = $data['production']['image']['srcset'] ?? null;
        $data['venue']                            = $data['venue']['name'] ?? null;
        $data['room']                             = $data['room']['name'] ?? null;
        $data['program']                          = $data['program']['name'] ?? null;

        foreach ( $unset as $field ) {
            unset( $data[ $field ] );
        }

        return $data;
    }
}
