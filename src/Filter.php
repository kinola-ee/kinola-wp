<?php

namespace Kinola\KinolaWp;

class Filter {
    protected Event_Query $available_dates_query;
    /**
     * @var array Array of allowed venue names (from shortcode).
     */
    private array $allowed_venues = [];

    public function __construct( ?Event_Query $available_dates_query = null ) {
        $this->available_dates_query = $available_dates_query ?? ( new Event_Query() )->upcoming();
    }

    /**
     * Set allowed venues for filtering dropdown options.
     *
     * When set, the venue dropdown will only display venues from this list.
     * Used to restrict dropdown scope when shortcode has allowed_venues parameter.
     *
     * @param array $allowed_venues Array of allowed venue names to restrict dropdown to.
     */
    public function set_allowed_venues( array $allowed_venues ) {
        $this->allowed_venues = $allowed_venues;
    }

    public function get_rendered_filter( ?string $film_remote_id = null ): string {
        $filter_data = [
            'dates'          => $this->get_dates(),
            'selected_date'  => $this->get_selected_date(),
            'venues'         => $this->get_venues(),
            'selected_venue' => $this->get_selected_venue(),
            'film_id'        => $film_remote_id,
            'allowed_venues' => $this->allowed_venues,
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

    /**
     * Get venue dropdown options, optionally restricted by allowed venues.
     *
     * @return array Array of venue options for dropdown.
     */
    public function get_venues(): array {
        $venues = [ 'all' => __( 'All venues', 'kinola' ) ];
        $taxonomy_name = Helpers::get_venue_taxonomy_name();

        // Fetch all venue terms in a single query
        $terms = get_terms( [
            'taxonomy'   => $taxonomy_name,
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );

        if ( ! empty( $this->allowed_venues ) ) {
            $allowed_term_ids = Helpers::get_venue_term_ids( $this->allowed_venues );

            foreach ( $terms as $term ) {
                /* @var $term \WP_Term */
                if ( in_array( (int) $term->term_id, $allowed_term_ids, true ) ) {
                    $venues[ $term->slug ] = $term->name;
                }
            }
        } else {
            // No restriction: include all venue terms
            foreach ( $terms as $term ) {
                /* @var $term \WP_Term */
                $venues[ $term->slug ] = $term->name;
            }
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
