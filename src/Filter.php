<?php

namespace Kinola\KinolaWp;

class Filter {
    protected EventQuery $available_dates_query;

    public function __construct(EventQuery $available_dates_query = null) {
        $this->available_dates_query = $available_dates_query ?? (new EventQuery())->upcoming();
    }

    public function get_rendered_filter(): string {
        return View::get_rendered_template( 'filters', [
            'dates'          => $this->get_dates(),
            'selected_date'  => $this->get_selected_date(),
            'venues'         => $this->get_venues(),
            'selected_venue' => $this->get_selected_location(),
        ] );
    }

    public function get_dates(): array {
        $dates  = [ __('all', 'kinola') => __( 'All dates', 'kinola' ) ];
        $events = $this->available_dates_query->get();
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

    public function get_selected_date(): ?string {
        $slug = Helpers::get_date_parameter_slug();

        if (!isset($_GET[ $slug ]) || !$_GET[ $slug ]) {
            return null;
        }

        if ($_GET[ $slug ] === __('all', 'kinola')) {
            return null;
        }

        return $_GET[ $slug ] ?? null;
    }

    public function get_selected_location(): ?string {
        $slug = Helpers::get_venue_parameter_slug();

        if (!isset($_GET[ $slug ]) || !$_GET[ $slug ]) {
            return null;
        }

        if ($_GET[ $slug ] === __('all', 'kinola')) {
            return null;
        }

        return $_GET[ $slug ] ?? null;
    }
}
