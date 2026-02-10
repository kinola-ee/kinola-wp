<?php

namespace Kinola\KinolaWp;

class Program_Query {
	protected array $params;
	protected array $programs;

	public function __construct() {
		$this->params = [
			'post_type'      => Helpers::get_programs_post_type(),
			'posts_per_page' => - 1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];
	}

	public function limit( int $limit ): Program_Query {
		$this->params['posts_per_page'] = $limit;

		return $this;
	}

	public function active(): Program_Query {
		// All programs in WordPress are active (inactive ones are not imported)
		return $this;
	}

	public function order_by( string $field, string $direction = 'ASC' ): Program_Query {
		$this->params['orderby'] = $field;
		$this->params['order']   = $direction;

		return $this;
	}

	public function get(): array {
		if ( isset( $this->programs ) && ! is_null( $this->programs ) ) {
			return $this->programs;
		}

		$this->programs = [];
		$programPosts   = ( new \WP_Query( $this->params ) )->posts;

		if ( count( $programPosts ) ) {
			foreach ( $programPosts as $programPost ) {
				$this->programs[] = new Program( $programPost );
			}
		}

		return $this->programs;
	}
}
