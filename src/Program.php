<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Api\Program as ApiProgram;

class Program extends Model {

	public const FIELD_ID = 'program_id';

	protected array $translatable = [
		'name',
		'description',
	];

	protected array $events;

	public function get_name(): string {
		$name = $this->get_field( 'name' );
		return is_string( $name ) ? $name : '';
	}

	public function get_description(): string {
		$description = $this->get_field( 'description' );
		return is_string( $description ) ? $description : '';
	}

	public function get_api_url(): string {
		return Router::get_kinola_api_program_link( $this->get_remote_id() );
	}

	public function save_api_data( ApiProgram $program ) {
		foreach ( $program->get_data() as $field => $value ) {
			if ( $field === 'post_title' ) {
				continue; // sets the WP post title (column), not stored as meta
			}
			$this->set_field( $field, $value );
		}
		$this->update_post_title( $program->get_field( 'post_title' ) );
	}

	public function get_events(): array {
		if ( ! isset( $this->events ) ) {
			$this->events = ( new Event_Query() )->upcoming()->program( $this->get_remote_id() )->get();
		}

		return $this->events;
	}

	public static function find_by_local_id( int $id ): ?Program {
		$post = get_post( $id );

		if ( $post ) {
			return new Program( $post );
		}

		return null;
	}

	public static function find_by_remote_id( string $id ): ?Program {
		$results = ( new \WP_Query( [
			'post_type'      => Helpers::get_programs_post_type(),
			'post_status'    => 'any',
			'meta_key'       => self::FIELD_ID,
			'meta_value'     => $id,
			'posts_per_page' => -1,
		] ) )->get_posts();

		if ( count( $results ) === 1 ) {
			return new Program( $results[0] );
		} else if ( count( $results ) > 1 ) {
			// If we have more than one matching program, delete everything except the first one.
			debug_log( "More than one WP Post matches program ID {$id}. Deleting extra posts." );
			debug_log( $results );
			$result = array_shift( $results );

			foreach ( $results as $duplicate ) {
				wp_delete_post( $duplicate->ID, true );
			}

			return new Program( $result );
		}

		return null;
	}

	public static function create( ApiProgram $api_program ): Program {
		$post_id = wp_insert_post( [
			'post_title'  => $api_program->get_field( 'post_title' ),
			'post_status' => 'publish',
			'post_type'   => Helpers::get_programs_post_type(),
		] );

		if ( is_wp_error( $post_id ) ) {
			throw new \RuntimeException(
				'Failed to create program post: ' . $post_id->get_error_message()
			);
		}

		if ( ! $post_id ) {
			throw new \RuntimeException( 'Failed to create program post: wp_insert_post returned 0' );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			throw new \RuntimeException(
				"Failed to retrieve program post after creation (ID: {$post_id})"
			);
		}

		$program = new Program( $post );
		$program->save_api_data( $api_program );

		return $program;
	}
}
