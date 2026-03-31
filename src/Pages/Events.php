<?php

namespace Kinola\KinolaWp\Pages;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Event_Query;
use Kinola\KinolaWp\Filter;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\View;

class Events {
    protected string $template = 'events';

    /**
     * Get rendered events page with optional venue filtering.
     *
     * @param string $show_dates Show upcoming or today's events.
     * @param int    $limit      Maximum number of events to display.
     * @param array  $allowed_venues Optional array of venue names to filter by.
     * @return string Rendered HTML for events page.
     */
    public function get_rendered_events( $show_dates = 'upcoming', $limit = 25, array $allowed_venues = [] ): string {
        if ( $limit === 'all' ) {
            $limit = - 1;
        }

        $filter = new Filter();

        // Set allowed venues for filter dropdown restriction
        if ( ! empty( $allowed_venues ) ) {
            $filter->set_allowed_venues( $allowed_venues );
        }

        $event_query = ( new Event_Query() )
            ->limit( $limit )
            ->upcoming()
            ->filter( $filter->get_selected_date(), $filter->get_selected_venue(), $filter->get_selected_time(), $filter->get_selected_film() );

        if ( $show_dates === 'today' && !$filter->get_selected_date()) {
            $event_query = $event_query->date( "today" );
        }

        // Apply shortcode venue restriction if specified
        if ( ! empty( $allowed_venues ) ) {
            $event_query->filterByAllowedVenues( $allowed_venues );
        }

        $events = $event_query->get();

        return View::get_rendered_template( $this->template, [
            'events'          => $events,
            'rendered_filter' => $filter->get_rendered_filter(),
        ] );
    }
}
