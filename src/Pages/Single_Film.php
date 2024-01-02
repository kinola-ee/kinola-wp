<?php

namespace Kinola\KinolaWp\Pages;

use Kinola\KinolaWp\Event_Query;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Filter;
use Kinola\KinolaWp\View;

class Single_Film {
    protected Film   $film;
    protected string $template = 'film';

    public function __construct( Film $film ) {
        $this->film = $film;
    }

    public function set_template( string $template ) {
        $this->template = $template;
    }

    public function get_rendered_content( $show_dates = 'upcoming' ): string {
        $filter = new Filter( ( new Event_Query() )->upcoming()->film( $this->film->get_remote_id() ) );

        $event_query = ( new Event_Query() )
            ->limit( 25 )
            ->upcoming()
            ->film( $this->film->get_remote_id() )
            ->filter(
                $filter->get_selected_date(),
                $filter->get_selected_venue(),
                $filter->get_selected_time()
            );

        if ( $show_dates === 'today' ) {
            $event_query = $event_query->date("today");
        }

        $events = $event_query->get();

        return View::get_rendered_template( $this->template, [
            'film'            => $this->film,
            'events'          => $events,
            'rendered_filter' => $filter->get_rendered_filter( $this->film->get_remote_id() ),
        ] );
    }
}
