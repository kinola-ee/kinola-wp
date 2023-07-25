<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Admin\Admin;
use Kinola\KinolaWp\Api\Film as ApiFilm;

class Film extends Model {

    public const FIELD_ID = 'film_id';

    protected array $translatable = [
        'title',
        'description',
        'countries',
        'languages',
        'subtitles',
        'genres',
    ];

    protected array $events;

    public function get_import_link(): string {
        return Router::get_action_url( [ Admin::IMPORT_FILM_ACTION => $this->get_remote_id() ] );
    }

    public function get_edit_link(): string {
        return Router::get_local_film_edit_link( $this->get_local_id() );
    }

    public function get_kinola_edit_link(): string {
        return Router::get_kinola_film_edit_link( $this->get_remote_id() );
    }

    public function get_local_url(): string {
        return get_permalink( $this->post );
    }

    public function get_director(): string {
        $crew      = $this->get_field( 'crew' );
        $directors = [];
        if ( $crew && count( $crew ) ) {
            foreach ( $crew as $crewType ) {
                if ( $crewType['type'] === 'director' ) {
                    foreach ( $crewType['people'] as $director ) {
                        $directors[] = $director['name'];
                    }
                }
            }
        }

        return implode( ', ', $directors );
    }

    public function get_cast(): string {
        $crew   = $this->get_field( 'crew' );
        $actors = [];
        if ( $crew && count( $crew ) ) {
            foreach ( $crew as $crewType ) {
                if ( $crewType['type'] === 'actor' ) {
                    foreach ( $crewType['people'] as $actor ) {
                        $actors[] = $actor['name'];
                    }
                }
            }
        }

        return implode( ', ', $actors );
    }

    public function save_api_data( ApiFilm $film ) {
        foreach ( $film->get_data() as $field => $value ) {
            $this->set_field( $field, $value );
        }
    }

    public function get_events(): array {
        if ( ! isset( $this->events ) ) {
            $this->events = ( new EventQuery() )->upcoming()->film( $this->get_remote_id() )->get();
        }

        return $this->events;
    }

    public static function find_by_local_id( int $id ): ?Film {
        $post = get_post( $id );

        if ( $post ) {
            return new Film( $post );
        }

        return null;
    }

    public static function find_by_remote_id( string $id ): ?Film {
        $results = ( new \WP_Query( [
            'post_type'   => Helpers::get_films_post_type(),
            'post_status' => 'any',
            'meta_key'    => self::FIELD_ID,
            'meta_value'  => $id,
        ] ) )->get_posts();

        if ( count( $results ) === 1 ) {
            return new Film( $results[0] );
        } else if ( count( $results ) > 1 ) {
            trigger_error( "More than one WP Post matches film ID {$id}", E_USER_WARNING );
        }

        return null;
    }

    public static function create( ApiFilm $api_film ): Film {
        $post = wp_insert_post( [
            'post_title'  => $api_film->get_field( 'post_title' ),
            'post_status' => 'draft',
            'post_type'   => Helpers::get_films_post_type(),
        ] );

        $film = new Film( get_post( $post ) );
        $film->save_api_data( $api_film );

        return $film;
    }
}
