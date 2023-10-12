<?php

namespace Kinola\KinolaWp\Pages;

use Kinola\KinolaWp\Event_Query;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Filter;
use Kinola\KinolaWp\View;

class SingleFilm {
    protected Film   $film;
    protected string $template = 'film';

    public function __construct( Film $film ) {
        $this->film = $film;
    }

    public function get_rendered_content(): string {
        $filter = new Filter( ( new Event_Query() )->upcoming()->film( $this->film->get_remote_id() ) );
        $events = ( new Event_Query() )
            ->upcoming()
            ->film( $this->film->get_remote_id() )
            ->filter( $filter->get_selected_date(), $filter->get_selected_location(), $filter->get_selected_time() )
            ->get();


        return View::get_rendered_template( $this->template, [
            'film'            => $this->film,
            'events'          => $events,
            'rendered_filter' => $filter->get_rendered_filter(),
        ] );
    }
}
