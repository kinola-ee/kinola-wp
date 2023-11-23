<?php

namespace Kinola\KinolaWp\Api;

class Film extends Api_Model {

    public function get_id() {
        return $this->get_field( \Kinola\KinolaWp\Film::FIELD_ID );
    }

    public function is_public(): bool {
        return $this->data['visibility'] === 'public';
    }

    protected function reformat( array $data ): array {
        $unset = [
            'id',
            'image',
            'name',
            'duration',
            'embeddableVideo',
            'originalName',
            'releaseDate',
            'customFields',
        ];

        $data[ \Kinola\KinolaWp\Film::FIELD_ID ] = $data['id'];
        $data['poster']                          = $data['image']['src'] ?? null;
        $data['post_title']                      = $this->resolve_post_title( $data['name'] );
        $data['title']                           = $data['name'];
        $data['title_original']                  = $data['originalName'];
        $data['release_date']                    = $data['releaseDate'];
        $data['runtime']                         = $data['duration'];
        $data['embeddable_video']                = $data['embeddableVideo'];
        $data['custom_fields']                   = $data['customFields'];

        foreach ( $unset as $field ) {
            unset( $data[ $field ] );
        }

        return $data;
    }
}
