<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Admin\Admin;
use Kinola\KinolaWp\Api\Film as ApiFilm;

class Film {

	public const FIELD_ID = 'film_id';

	protected ?\WP_Post $post = null;

	public function __construct( \WP_Post $post ) {
		$this->post = $post;
	}

	public function get_poster_url() {
		return $this->get_field('poster');
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

	public function get_import_link(): string {
		return Router::get_action_url( [ Admin::IMPORT_FILM_ACTION => $this->get_remote_id() ] );
	}

	public function get_edit_link(): string {
		return Router::get_local_film_edit_link( $this->get_local_id() );
	}

	public function get_kinola_edit_link(): string {
		return Router::get_kinola_film_edit_link( $this->get_remote_id() );
	}

	public function set_field( string $field, $value ) {
		update_post_meta( $this->post->ID, $field, $value );
	}

	public function save_api_data( ApiFilm $film ) {
		foreach ( $film->get_data() as $field => $value ) {
			$this->set_field( $field, $value );
		}
	}

    public function get_rendered_content(): string {
        return View::get_rendered_template('film', ['film' => $this]);
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
			'post_type'  => Helpers::get_films_post_type(),
			'meta_key'   => self::FIELD_ID,
			'meta_value' => $id,
		] ) )->get_posts();

		if ( count( $results ) === 1 ) {
			return new Film( $results[0] );
		} elseif ( count( $results ) > 1 ) {
			trigger_error( "More than one WP Post matches film ID {$id}", E_USER_WARNING );
		}

		return null;
	}

	public static function create( ApiFilm $api_film ): Film {
		$post = wp_insert_post( [
			'post_title'  => $api_film->get_field( 'title' ),
			'post_status' => 'draft',
			'post_type'   => Helpers::get_films_post_type(),
		] );

		$film = new Film( get_post( $post ) );
		$film->save_api_data( $api_film );

		return $film;
	}
}
