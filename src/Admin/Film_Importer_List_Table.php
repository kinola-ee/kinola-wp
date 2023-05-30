<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Router;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Film_Importer_List_Table extends \WP_List_Table {

	protected Film_Importer $importer;

	public function __construct( Film_Importer $importer ) {
		$this->importer = $importer;

		parent::__construct( [
			'singular' => __( 'Film', 'kinola' ),
			'plural'   => __( 'Films', 'kinola' ),
			'ajax'     => false,
		] );
	}

	public function get_columns(): array {
		return [
			'title'          => _x( 'Title', 'Admin', 'kinola' ),
			'title_original' => _x( 'Original title', 'Admin', 'kinola' ),
			'actions'        => _x( 'Actions', 'Admin', 'kinola' ),
		];
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'actions':
				$url = Router::get_action_url( [ Admin::IMPORT_FILM_ACTION => $item['id'] ] );

				return "<a href='{$url}'>" . _x( 'Import', 'Admin', 'kinola' ) . "</a>";
			default:
				return $item[ $column_name ];
		}
	}

	public function prepare_items() {
		$this->_column_headers = [ $this->get_columns(), [], [] ];

		$data = $this->importer->get_films();
		$data = $this->remove_duplicates( $data );

		if ( empty( $data ) ) {
			$this->items = [];
		}

		$films = [];
		foreach ( $data as $film ) {
			$films[] = [
				'id'             => $film['id'],
				'title'          => $film['name'],
				'title_original' => $film['originalName'],
			];
		}

		$this->items = $films;
	}

	function remove_duplicates( array $films ): array {
		$results = array_map( function ( $film ) {
			return $film['id'];
		}, $films );

		$unique = array_unique( $results );

		return array_values( array_intersect_key( $films, $unique ) );
	}
}