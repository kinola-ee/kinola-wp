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

	public function import_film( string $id ): Film {
		try {
			$response = Kinola_Api::get( $this->single_film_endpoint . $id );
		} catch ( ApiException $e ) {
			echo $e->getMessage();
			die;
		}

		return $this->save_film( new Api_Film( $response->get_data() ) );
	}

	public function get_films(): array {
		$this->get_films_data();

		return $this->data;
	}

	/**
	 * Recursively get all films from Kinola public API.
	 */
	protected function get_films_data( $url = null ) {
		try {
			$response = Kinola_Api::get( $url ?: $this->films_endpoint, false );
		} catch ( ApiException $e ) {
			echo $e->getMessage();
			die;
		}

		$this->data = array_merge( $this->data, $response->get_data() );

		if ( $response->has_next_link() ) {
			$this->get_films_data( $response->get_next_link() );
		}
	}

	protected function save_film( Api_Film $api_film ): Film {

		$film = Film::find_by_remote_id( $api_film->get_id() );

		if ( ! $film ) {
			$film = Film::create( $api_film );
		} else {
			$film->save_api_data( $api_film );
		}

		return $film;
	}
}
