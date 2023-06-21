<?php

namespace Kinola\KinolaWp\Pages;

use Kinola\KinolaWp\EventQuery;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Filter;
use Kinola\KinolaWp\View;

class SingleFilm {
    protected Film $film;

    public function __construct( Film $film ) {
        $this->film = $film;
    }

    public function get_rendered_content(): string {
        $filter = new Filter(( new EventQuery() )->upcoming()->film($this->film->get_remote_id()));
        $events = ( new EventQuery() )
            ->upcoming()
            ->film( $this->film->get_remote_id() )
            ->filter( $filter->get_selected_date(), $filter->get_selected_location() )
            ->get();


        return View::get_rendered_template( 'film', [
            'film'            => $this->film,
            'events'          => $events,
            'rendered_filter' => $filter->get_rendered_filter(),
        ] );
    }
}
