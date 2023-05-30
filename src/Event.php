<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Api\Event as ApiEvent;

class Event {

	public const FIELD_ID = 'event_id';

	protected ?\WP_Post $post = null;

	public function __construct( \WP_Post $post ) {
		$this->post = $post;
	}

	public function get_date() {
		return date( get_option( 'date_format' ), strtotime( $this->get_field( 'time' ) ) );
	}

	public function get_time() {
		return date( get_option( 'time_format' ), strtotime( $this->get_field( 'time' ) ) );
	}

	public function get_poster_url() {
		return $this->get_field( 'production', false )['image']['srcset'];
	}

	public function get_checkout_url(): string {
		return Router::get_event_checkout_url( $this->get_remote_id() );
	}

	public function get_field( string $name, bool $compact = true ) {
		$value = get_post_meta( $this->post->ID, $name, true );

		if ( is_array( $value ) && $compact ) {
			return implode( ', ', $value );
		}

		return $value;
	}

	public function get_local_id(): int {
		return $this->post->ID;
	}

	public function get_remote_id(): string {
		return $this->get_field( self::FIELD_ID );
	}

	public function set_field( string $field, $value ) {
		update_post_meta( $this->post->ID, $field, $value );
	}

	public function set_title( ApiEvent $event ) {
		wp_update_post( [
			'ID'         => $this->get_local_id(),
			'post_title' => $event->get_formatted_title(),
		] );
	}

	public function save_api_data( ApiEvent $event ) {
		foreach ( $event->get_data() as $field => $value ) {
			$this->set_field( $field, $value );
		}
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
			'post_type'  => Helpers::get_events_post_type(),
			'meta_key'   => self::FIELD_ID,
			'meta_value' => $id,
		] ) )->get_posts();

		if ( count( $results ) === 1 ) {
			return new Event( $results[0] );
		} else if ( count( $results ) > 1 ) {
			trigger_error( "More than one WP Post matches event ID {$id}", E_USER_WARNING );
		}

		return null;
	}

	public static function create( ApiEvent $api_event ): Event {
		$post = wp_insert_post( [
			'post_title'  => $api_event->get_formatted_title(),
			'post_status' => 'publish',
			'post_type'   => Helpers::get_events_post_type(),
		] );

		$event = new Event( get_post( $post ) );
		$event->save_api_data( $api_event );

		return $event;
	}
}