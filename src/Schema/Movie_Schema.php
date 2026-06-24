<?php

namespace Kinola\KinolaWp\Schema;

use Kinola\KinolaWp\Film;

class Movie_Schema {

    public static function get_id( Film $film ): string {
        return $film->get_local_url() . '#movie';
    }

    /**
     * The identity header shared by the full node and the compact reference, so both
     * describe one film under a single @id (with matching url and name) across pages.
     */
    private static function build_base( Film $film ): array {
        return [
            '@type' => 'Movie',
            '@id'   => self::get_id( $film ),
            'url'   => $film->get_local_url(),
            'name'  => $film->get_field( 'title' ) ?: $film->get_title(),
        ];
    }

    public static function build( Film $film ): array {
        $node = self::build_base( $film );

        $original_title = $film->get_field( 'title_original' );
        if ( $original_title && $original_title !== $node['name'] ) {
            $node['alternateName'] = $original_title;
        }

        $poster = Schema_Helpers::safe_url( $film->get_field( 'poster' ) );
        if ( $poster ) {
            $node['image'] = $poster;
        }

        $description = $film->get_field( 'description' );
        if ( $description ) {
            $node['description'] = wp_strip_all_tags( $description );
        }

        $year = (int) $film->get_field( 'year' );
        if ( $year > 0 ) {
            $node['dateCreated'] = (string) $year;
        }

        $runtime = (int) $film->get_field( 'runtime' );
        if ( $runtime > 0 ) {
            $node['duration'] = 'PT' . $runtime . 'M';
        }

        $directors = self::get_people( $film, 'director' );
        if ( $directors ) {
            $node['director'] = $directors;
        }

        $actors = self::get_people( $film, 'actor' );
        if ( $actors ) {
            $node['actor'] = $actors;
        }

        $genres = Schema_Helpers::normalize_list( $film->get_field( 'genres', false ) );
        if ( $genres ) {
            $node['genre'] = $genres;
        }

        $languages = Schema_Helpers::normalize_list( $film->get_field( 'languages', false ) );
        if ( $languages ) {
            $node['inLanguage'] = $languages;
        }

        $subtitles = Schema_Helpers::normalize_list( $film->get_field( 'subtitles', false ) );
        if ( $subtitles ) {
            $node['subtitleLanguage'] = $subtitles;
        }

        $countries = Schema_Helpers::normalize_list( $film->get_field( 'countries', false ) );
        if ( $countries ) {
            $node['countryOfOrigin'] = array_map( function ( $country ) {
                return [
                    '@type' => 'Country',
                    'name'  => $country,
                ];
            }, $countries );
        }

        return $node;
    }

    /**
     * Compact reference node for pages where the full Movie node lives elsewhere
     * (the film's own page) under the same @id.
     */
    public static function build_reference( Film $film ): array {
        $node = self::build_base( $film );

        $poster = Schema_Helpers::safe_url( $film->get_field( 'poster' ) );
        if ( $poster ) {
            $node['image'] = $poster;
        }

        return $node;
    }

    protected static function get_people( Film $film, string $type ): array {
        $crew   = $film->get_field( 'crew' );
        $people = [];

        if ( $crew && is_array( $crew ) ) {
            foreach ( $crew as $crew_type ) {
                if ( ( $crew_type['type'] ?? '' ) !== $type ) {
                    continue;
                }

                foreach ( $crew_type['people'] ?? [] as $person ) {
                    if ( ! empty( $person['name'] ) ) {
                        $people[] = [
                            '@type' => 'Person',
                            // Collapse stray whitespace from the API; browsers
                            // collapse it visually, so this matches the page.
                            'name'  => preg_replace( '/\s+/u', ' ', trim( $person['name'] ) ),
                        ];
                    }
                }
            }
        }

        return $people;
    }
}
