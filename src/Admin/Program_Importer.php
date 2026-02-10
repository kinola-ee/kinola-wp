<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Api\Exceptions\ApiException;
use Kinola\KinolaWp\Api\Kinola_Api;
use Kinola\KinolaWp\Program;
use Kinola\KinolaWp\Api\Program as Api_Program;

class Program_Importer {

	protected string $programs_endpoint       = 'programs?limit=500';
	protected string $single_program_endpoint = 'programs/';

	protected array $data = [];

	public function import_program( string $remote_id ): ?Program {
		debug_log( "Program import: Importing program with ID {$remote_id}" );

		try {
			$response = Kinola_Api::get( $this->single_program_endpoint . $remote_id );
		} catch ( ApiException $e ) {
			debug_log( "Program import: API error for program {$remote_id}: " . $e->getMessage() );
			return null;
		}

		$data = $response->get_data();

		if ( ! $data ) {
			debug_log( "Program import: No data returned for program {$remote_id}" );
			return null;
		}

		return $this->save_program( new Api_Program( $data ) );
	}

	public function import_programs() {
		debug_log( "Program import: Importing all programs" );
		$this->get_programs_data( $this->programs_endpoint, true );

		foreach ( $this->data as $program ) {
			$this->save_program( new Api_Program( $program ) );
		}

		debug_log( "Program import: Finished importing programs. Total: " . count( $this->data ) );
	}

	public function get_programs(): array {
		$this->get_programs_data();

		return $this->data;
	}

	/**
	 * Recursively get all programs from Kinola public API.
	 */
	protected function get_programs_data( $url = null, $with_translations = false ) {
		try {
			$response = Kinola_Api::get( $url ?: $this->programs_endpoint, $with_translations );
		} catch ( ApiException $e ) {
			debug_log( "Program import: API error fetching programs: " . $e->getMessage() );
			return;
		}

		$this->data = array_merge( $this->data, $response->get_data() );

		if ( $response->has_next_link() ) {
			$this->get_programs_data( $response->get_next_link(), $with_translations );
		}
	}

	protected function save_program( Api_Program $api_program ): Program {
		$program = Program::find_by_remote_id( $api_program->get_id() );

		if ( ! $program ) {
			$program = Program::create( $api_program );
			debug_log( "Program import: Created new post #{$program->get_local_id()} for program ID {$api_program->get_id()}." );
		} else {
			$program->save_api_data( $api_program );
			debug_log( "Program import: Updated post #{$program->get_local_id()} for program ID {$api_program->get_id()}." );
		}

		return $program;
	}
}
