<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Api\Exceptions\ApiException;
use Kinola\KinolaWp\Api\Kinola_Api;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Api\Film as Api_Film;

class Film_Importer {

    protected string $films_endpoint       = 'productions?limit=500';
    protected string $single_film_endpoint = 'productions/';

    protected array $data = [];

    public function import_film( string $remote_id ): Film {
        debug_log( "Film import: Importing film with ID {$remote_id}" );

        try {
            $response = Kinola_Api::get( $this->single_film_endpoint . $remote_id );
        } catch ( ApiException $e ) {
            echo $e->getMessage();
            die;
        }

        return $this->save_film( new Api_Film( $response->get_data() ) );
    }

    public function import_films( string $last_modified_since ) {
        debug_log( "Film import: Importing films changed since {$last_modified_since}" );
        $this->get_films_data( $this->films_endpoint . '&last_modified_since=' . $last_modified_since, true );

        foreach ( $this->data as $film ) {
            $this->save_film( new Api_Film( $film ) );
        }

        debug_log( "Film import: Finished importing changed films." );
    }

    public function get_films(): array {
        $this->get_films_data();

        return $this->data;
    }

    /**
     * Recursively get all films from Kinola public API.
     */
    protected function get_films_data( $url = null, $with_translations = false ) {
        try {
            $response = Kinola_Api::get( $url ?: $this->films_endpoint, $with_translations );
        } catch ( ApiException $e ) {
            echo $e->getMessage();
            die;
        }

        $this->data = array_merge( $this->data, $response->get_data() );

        if ( $response->has_next_link() ) {
            $this->get_films_data( $response->get_next_link() );
        }
    }

    protected function save_film( Api_Film $api_film ): ?Film {

        if ( ! $api_film->is_public() ) {
            debug_log( "Film import: Film ID {$api_film->get_id()} is not public, skip import." );

            return null;
        }

        $film = Film::find_by_remote_id( $api_film->get_id() );

        if ( ! $film ) {
            $film = Film::create( $api_film );
            debug_log( "Film import: Created new post #{$film->get_local_id()} for film ID {$api_film->get_id()}." );
        } else {
            $film->save_api_data( $api_film );
            debug_log( "Film import: Updated post #{$film->get_local_id()} for film ID {$api_film->get_id()}." );
        }

        return $film;
    }
}
