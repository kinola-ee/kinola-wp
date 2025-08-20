<?php

namespace Kinola\KinolaWp\Api;

class Event extends Api_Model {

    public function get_id() {
        return $this->get_field( \Kinola\KinolaWp\Event::FIELD_ID );
    }

    protected function reformat( array $data ): array {
        $unset = [
            'id',
            'production',
        ];

        $data[ \Kinola\KinolaWp\Event::FIELD_ID ] = $data['id'];
        $data[ \Kinola\KinolaWp\Film::FIELD_ID ]  = $data['production']['id'] ?? null;
        $data['production_title']                 = $data['production']['name'] ?? null;
        $data['production_poster']                = $data['production']['image']['src'] ?? null;
        $data['venue']                            = $data['venue']['name'] ?? null;
        $data['room']                             = $data['room']['name'] ?? null;
        $data['program']                          = $data['program']['name'] ?? null;
        $data['checkout_url']                     = $data['checkout_url'] ?? null;

        foreach ( $unset as $field ) {
            unset( $data[ $field ] );
        }

        return $data;
    }
}
