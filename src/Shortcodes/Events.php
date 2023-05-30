<?php

namespace Kinola\KinolaWp\Shortcodes;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\View;

class Events {

	public function get_rendered_events(): string {
		$event_posts = $this->get_events();
		$events     = [];

		if ( count( $event_posts ) ) {
			foreach ( $event_posts as $event_post ) {
				$events[] = new Event( $event_post );
			}
		}

		return View::get_rendered_template( 'events', [ 'events' => $events ] );
	}

	public function get_events(): array {
		$query = new \WP_Query( [
			'post_type'      => Helpers::get_events_post_type(),
			'posts_per_page' => 20,
			'meta_key'       => 'time',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'     => 'time',
					'value'   => gmdate( "Y-m-d\TH:i:s\Z" ),
					'compare' => '>=',
				]
			]
		] );

		return $query->get_posts();
	}
}