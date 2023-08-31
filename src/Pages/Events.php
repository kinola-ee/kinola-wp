<?php

namespace Kinola\KinolaWp\Pages;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Event_Query;
use Kinola\KinolaWp\Filter;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\View;

class Events {

    public function get_rendered_events(): string {
        $filter = new Filter();
        $events = ( new Event_Query() )
            ->upcoming()
            ->filter( $filter->get_selected_date(), $filter->get_selected_location(), $filter->get_selected_time() )
            ->get();

        return View::get_rendered_template( 'events', [
            'events' => $events,
            'rendered_filter' => $filter->get_rendered_filter(),
        ] );
    }
}
