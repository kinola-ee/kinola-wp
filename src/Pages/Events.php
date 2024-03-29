<?php

namespace Kinola\KinolaWp\Pages;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Event_Query;
use Kinola\KinolaWp\Filter;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\View;

class Events {
    protected string $template = 'events';

    public function get_rendered_events( $show_dates = 'upcoming', $limit = 25 ): string {
        if ( $limit === 'all' ) {
            $limit = - 1;
        }

        $filter      = new Filter();
        $event_query = ( new Event_Query() )
            ->limit( $limit )
            ->upcoming()
            ->filter( $filter->get_selected_date(), $filter->get_selected_venue(), $filter->get_selected_time(), $filter->get_selected_film() );

        if ( $show_dates === 'today' && !$filter->get_selected_date()) {
            $event_query = $event_query->date( "today" );
        }

        $events = $event_query->get();

        return View::get_rendered_template( $this->template, [
            'events'          => $events,
            'rendered_filter' => $filter->get_rendered_filter(),
        ] );
    }
}
