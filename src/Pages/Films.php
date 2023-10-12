<?php

namespace Kinola\KinolaWp\Pages;

use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\View;

class Films {
    protected string $template = 'films';

    public function get_rendered_films(): string {
        $film_posts = $this->get_films();
        $films      = [];

        if ( count( $film_posts ) ) {
            foreach ( $film_posts as $film_post ) {
                $films[] = new Film( $film_post );
            }
        }

        return View::get_rendered_template( $this->template, [ 'films' => $films ] );
    }

    public function get_films(): array {
        $query = new \WP_Query( [
            'post_type'      => Helpers::get_films_post_type(),
            'posts_per_page' => - 1,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ] );

        return $query->get_posts();
    }
}
