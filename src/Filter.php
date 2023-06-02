<?php

namespace Kinola\KinolaWp;

class Filter {
    public function get_rendered_filter(): string {
        return View::get_rendered_template( 'filters', [
            'dates'           => $this->get_dates(),
            'selected_date'   => $this->get_selected_date(),
            'venues'          => $this->get_venues(),
            'selected_venue' => $this->get_selected_location(),
        ] );
    }

    public function get_dates(): array {
        $dates = [0 => __('All dates', 'kinola')];
        $events = Event::get_upcoming_events();
        foreach ($events as $event) {
            /* @var $event Event */
            $dates[$event->get_date()] = $event->get_date();
        }

        return array_unique($dates);
    }

    public function get_venues(): array {
        $venues = [0 => __('All venues', 'kinola')];
        $terms = get_terms( [
            'taxonomy'   => Helpers::get_venue_taxonomy_name(),
            'hide_empty' => true,
        ] );

        foreach ($terms as $term) {
            $venues[$term->term_id] = $term->name;
        }

        return $venues;
    }

    public function get_selected_date() {
        $slug = Helpers::get_date_parameter_slug();

        return $_GET[ $slug ] ?? '';
    }

    public function get_selected_location() {
        $slug = Helpers::get_venue_parameter_slug();

        return $_GET[ $slug ] ?? '';
    }
}
