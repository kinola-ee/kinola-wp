<?php

namespace Kinola\KinolaWp\Api;

class Film {

    protected array $data = [];

    public function __construct( array $data ) {
        $this->data = $this->reformat( $data );
    }

    public function get_data(): array {
        return $this->data;
    }

    public function get_id() {
        return $this->data[ \Kinola\KinolaWp\Film::FIELD_ID ];
    }

    public function get_field( string $field ) {
        return $this->data[ $field ] ?? null;
    }

    protected function reformat( array $data ): array {
        $unset = [
            'id',
            'image',
            'name',
            'originalName',
            'releaseDate',
            'customFields',
        ];

        $data[ \Kinola\KinolaWp\Film::FIELD_ID ] = $data['id'];
        $data['poster']                          = $data['image']['srcset'] ?? null;
        $data['title']                           = $data['name'];
        $data['title_original']                  = $data['originalName'];
        $data['release_date']                    = $data['releaseDate'];
        $data['custom_fields']                   = $data['customFields'];

        foreach ( $unset as $field ) {
            unset( $data[ $field ] );
        }

        return $data;
    }
}
