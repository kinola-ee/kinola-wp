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
        ];

        $data[ \Kinola\KinolaWp\Event::FIELD_ID ] = $data['id'];
        $data[ \Kinola\KinolaWp\Film::FIELD_ID ]  = $data['production']['id'];
        $data['production_title']                 = $data['production']['name'];
        $data['venue']                            = $data['venue']['name'];
        $data['room']                             = $data['room']['name'];

        foreach ( $unset as $field ) {
            unset( $data[ $field ] );
        }

        return $data;
    }
}
