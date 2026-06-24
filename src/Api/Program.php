<?php

namespace Kinola\KinolaWp\Api;

class Program extends Api_Model {

	public function get_id() {
		return $this->get_field( \Kinola\KinolaWp\Program::FIELD_ID );
	}

	protected function reformat( array $data ): array {
		$unset = [
			'id',
			'customFields',
		];

		$data[ \Kinola\KinolaWp\Program::FIELD_ID ] = $data['id'];
		$data['post_title']                         = $this->resolve_post_title( $data['name'] );
		$data['custom_fields']                      = $data['customFields'] ?? [];

		foreach ( $unset as $field ) {
			unset( $data[ $field ] );
		}

		return $data;
	}
}
