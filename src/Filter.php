<?php

namespace Kinola\KinolaWp;

class Filter {
    protected Event_Query $available_dates_query;

    public function __construct( ?Event_Query $available_dates_query = null ) {
        $this->available_dates_query = $available_dates_query ?? ( new Event_Query() )->upcoming();
    }

    public function get_rendered_filter( ?string $film_remote_id = null ): string {
        $filter_data = [
            'dates'          => $this->get_dates(),
            'selected_date'  => $this->get_selected_date(),
            'venues'         => $this->get_venues(),
            'selected_venue' => $this->get_selected_venue(),
            'film_id'        => $film_remote_id,
        ];

        if ( apply_filters( 'kinola/filters/time', false ) ) {
            $filter_data['times']         = $this->get_times();
            $filter_data['selected_time'] = $this->get_selected_time();
        }

        if ( apply_filters( 'kinola/filters/film', true ) && ! $film_remote_id ) {
            $filter_data['films']         = $this->get_films();
            $filter_data['selected_film'] = $this->get_selected_film();
        }

        return View::get_rendered_template( 'filters', $filter_data );
    }

    public function get_films(): array {
        $films = [ 'all' => __( 'All films', 'kinola' ) ];
        $events = $this->available_dates_query->get();

        foreach ( $events as $event ) {
            /* @var $event Event */
            $films[ $event->get_film()->get_remote_id() ] = $event->get_film()->get_title();
        }

        return $films;
    }

    public function get_venues(): array {
        $venues = [ 'all' => __( 'All venues', 'kinola' ) ];
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

    public function get_dates(): array {
        $dates  = [ 'all' => __( 'All dates', 'kinola' ) ];
        $events = $this->available_dates_query->get();
        foreach ( $events as $event ) {
            /* @var $event Event */
            $dates[ $event->get_date() ] = $event->get_date();
        }

        return array_unique( $dates );
    }

    public function get_times(): array {
        $times  = [];
        $events = $this->available_dates_query->get();
        foreach ( $events as $event ) {
            /* @var $event Event */
            $times[ $event->get_time() ] = $event->get_time();
        }

        asort( $times );

        return array_unique( [ 'all' => __( 'All times', 'kinola' ) ] + $times );
    }

    public function get_selected_film(): ?string {
        return Helpers::get_filter_parameter_value(Helpers::get_film_parameter_slug());
    }

    public function get_selected_venue(): ?string {
        return Helpers::get_filter_parameter_value(Helpers::get_venue_parameter_slug());
    }

    public function get_selected_date(): ?string {
        return Helpers::get_filter_parameter_value(Helpers::get_date_parameter_slug());
    }

    public function get_selected_time(): ?string {
        return Helpers::get_filter_parameter_value(Helpers::get_time_parameter_slug());
    }
}
