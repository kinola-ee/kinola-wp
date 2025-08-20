<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Api\Event as ApiEvent;

class Event extends Model {

    public const FIELD_ID = 'event_id';

    public function get_date(): string {
        $dateTime = Helpers::format_datetime( $this->get_field( 'time' ) );

        return $dateTime->format( get_option( 'date_format' ) );
    }

    public function get_time(): string {
        $dateTime = Helpers::format_datetime( $this->get_field( 'time' ) );

        return $dateTime->format( get_option( 'time_format' ) );
    }

    public function get_checkout_url(): string {
        // Use checkout_url from API if available (handles both internal and external ticketing)
        $checkout_url = $this->get_field( 'checkout_url' );
        if ( $checkout_url ) {
            return $checkout_url;
        }
        // Fallback to generating local checkout URL
        return Router::get_event_checkout_url( $this->get_remote_id() );
    }

    public function get_film_url(): string {
        return $this->get_film()->get_local_url();
    }

    public function get_api_url(): string {
        return Router::get_kinola_api_events_link();
    }

    public function get_film(): Film {
        return Film::find_by_remote_id( $this->get_field( \Kinola\KinolaWp\Film::FIELD_ID ) );
    }

    public function get_free_seats(): ?string {
        $seats = $this->get_field( 'freeSeats' );
        return $seats !== null ? (string) $seats : null;
    }

    public function set_title( string $production_title, string $date_time ) {
        wp_update_post( [
            'ID'         => $this->get_local_id(),
            'post_title' => self::format_title( $production_title, $date_time ),
        ] );
    }

    public function save_api_data( ApiEvent $event ) {
        foreach ( $event->get_data() as $field => $value ) {
            switch ( $field ) {
                case 'venue':
                    $this->set_venue( $value );
                    break;
                default:
                    $this->set_field( $field, $value );
            }
        }
    }

    public function get_venue_name(): string {
        $venue = $this->get_venue();

        return $venue ? $venue->name : '';
    }

    public function get_venue(): ?\WP_Term {
        $terms = wp_get_object_terms( $this->get_local_id(), Helpers::get_venue_taxonomy_name() );
        if ( count( $terms ) ) {
            return $terms[0];
        }

        return null;
    }

    public function set_venue( $venue ) {
        wp_insert_term(
            $venue,
            Helpers::get_venue_taxonomy_name()
        );

        $term = get_term_by( 'name', $venue, Helpers::get_venue_taxonomy_name() );

        wp_set_object_terms(
            $this->get_local_id(),
            $term->term_id,
            Helpers::get_venue_taxonomy_name()
        );
    }

    public function delete() {
        wp_delete_post( $this->post->ID, true );
    }

    public static function find_by_local_id( int $id ): ?Event {
        $post = get_post( $id );

        if ( $post ) {
            return new Event( $post );
        }

        return null;
    }

    public static function find_by_remote_id( string $id ): ?Event {
        $results = ( new \WP_Query( [
            'post_type'      => Helpers::get_events_post_type(),
            'post_status'    => 'any',
            'meta_key'       => self::FIELD_ID,
            'meta_value'     => $id,
            'posts_per_page' => - 1,
        ] ) )->get_posts();

        if ( count( $results ) === 1 ) {
            return new Event( $results[0] );
        } else if ( count( $results ) > 1 ) {
            // If we have more than one matching event, delete everything except the last (latest) one.
            debug_log( "More than one WP Post matches event ID {$id}. Deleting extra posts." );
            debug_log( $results );
            $result = array_shift( $results );

            foreach ( $results as $duplicate ) {
                $event = new Event( $duplicate );
                $event->delete();
            }

            return new Event( $result );
        }

        return null;
    }

    public static function create( ApiEvent $api_event ): Event {
        $title = $api_event->get_field( 'production_title' ) ?: '';

        $post = wp_insert_post( [
            'post_title'  => self::format_title( $title, $api_event->get_field( 'time' ) ),
            'post_status' => 'publish',
            'post_type'   => Helpers::get_events_post_type(),
        ] );

        $event = new Event( get_post( $post ) );
        $event->save_api_data( $api_event );

        return $event;
    }

    public static function format_title( string $production_title, string $date_time ): string {
        $dateTime = Helpers::format_datetime( $date_time );

        return $production_title . ' - ' .
               $dateTime->format( get_option( 'date_format' ) ) . ' ' .
               $dateTime->format( get_option( 'time_format' ) );
    }
}
