<?php

namespace Kinola\KinolaWp\Shortcodes;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Filter;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\View;

class Events {

    public function get_rendered_events(): string {
        $filter = new Filter();
        $args = [];
        $meta_query = [];

        if ($filter->get_selected_location()) {
            $args['tax_query'] = [
                [
                    'taxonomy' => Helpers::get_venue_taxonomy_name(),
                    'field' => 'slug',
                    'terms' => $filter->get_selected_location(),
                ],
            ];
        }

        if ($filter->get_selected_date()) {
            // The date in the database is in UTC time zone, so we need to convert it to whatever is configured in WP.
            $selected_date_utc = new \DateTime($filter->get_selected_date(), new \DateTimeZone(wp_timezone_string()));
            $selected_date_utc->setTimezone(new \DateTimeZone('UTC'));

            $meta_query = [
                'key'     => 'time',
                'value'   => [
                    $selected_date_utc->format("Y-m-d\TH:i:s\Z"),
                    $selected_date_utc->add(\DateInterval::createFromDateString('23 hours 59 minutes'))->format("Y-m-d\TH:i:s\Z"),
                ],
                'compare' => 'BETWEEN',
            ];
        }

        return View::get_rendered_template( 'events', [
            'events' => Event::get_upcoming_events($args, $meta_query),
            'filter' => $filter->get_rendered_filter(),
        ] );
    }
}
