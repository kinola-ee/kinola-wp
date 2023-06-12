<?php

namespace Kinola\KinolaWp;

class Filter {
    public function get_rendered_filter(): string {
        return View::get_rendered_template( 'filters', [
            'dates'          => $this->get_dates(),
            'selected_date'  => $this->get_selected_date(),
            'venues'         => $this->get_venues(),
            'selected_venue' => $this->get_selected_location(),
        ] );
    }

    public function get_dates( array $args = [], array $meta_query = [] ): array {
        $dates  = [ __('all', 'kinola') => __( 'All dates', 'kinola' ) ];
        $events = Event::get_upcoming_events( $args, $meta_query );
        foreach ( $events as $event ) {
            /* @var $event Event */
            $dates[ $event->get_date() ] = $event->get_date();
        }

        return array_unique( $dates );
    }

    public function get_venues(): array {
        $venues = [ __('all', 'kinola') => __( 'All venues', 'kinola' ) ];
        $terms  = get_terms( [
            'taxonomy'   => Helpers::get_venue_taxonomy_name(),
            'hide_empty' => true,
        ] );

        foreach ( $terms as $term ) {
            /* @var $term \WP_Term */
            $venues[ $term->slug ] = $term->name;
        }

        return $venues;
    }

    public function get_selected_date() {
        $slug = Helpers::get_date_parameter_slug();

        if (!isset($_GET[ $slug ]) || !$_GET[ $slug ]) {
            return false;
        }

        if ($_GET[ $slug ] === __('all', 'kinola')) {
            return false;
        }

        return $_GET[ $slug ] ?? '';
    }

    public function get_selected_location() {
        $slug = Helpers::get_venue_parameter_slug();

        if (!isset($_GET[ $slug ]) || !$_GET[ $slug ]) {
            return false;
        }

        if ($_GET[ $slug ] === __('all', 'kinola')) {
            return false;
        }

        return $_GET[ $slug ];
    }
}
