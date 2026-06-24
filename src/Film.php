<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Admin\Admin;
use Kinola\KinolaWp\Api\Film as ApiFilm;

class Film extends Model {

    public const FIELD_ID = 'film_id';

    protected array $translatable = [
        'title',
        'description',
        'countries',
        'languages',
        'subtitles',
        'genres',
    ];

    protected array $events;

    public function get_import_link(): string {
        return Router::get_action_url( [ Admin::IMPORT_FILM_ACTION => $this->get_remote_id() ] );
    }

    public function get_kinola_edit_link(): string {
        return Router::get_kinola_film_edit_link( $this->get_remote_id() );
    }

    public function get_api_url(): string {
        return Router::get_kinola_api_film_link( $this->get_remote_id() );
    }

    public function get_local_url(): string {
        return get_permalink( $this->post );
    }

    public function get_director(): string {
        $crew      = $this->get_field( 'crew' );
        $directors = [];
        if ( $crew && count( $crew ) ) {
            foreach ( $crew as $crewType ) {
                if ( $crewType['type'] === 'director' ) {
                    foreach ( $crewType['people'] as $director ) {
                        $directors[] = $director['name'];
                    }
                }
            }
        }

        return implode( ', ', $directors );
    }

    public function get_cast(): string {
        $crew   = $this->get_field( 'crew' );
        $actors = [];
        if ( $crew && count( $crew ) ) {
            foreach ( $crew as $crewType ) {
                if ( $crewType['type'] === 'actor' ) {
                    foreach ( $crewType['people'] as $actor ) {
                        $actors[] = $actor['name'];
                    }
                }
            }
        }

        return implode( ', ', $actors );
    }

    public function save_api_data( ApiFilm $film ) {
        foreach ( $film->get_data() as $field => $value ) {
            if ( $field === 'post_title' ) {
                continue; // sets the WP post title (column), not stored as meta
            }
            $this->set_field( $field, $value );
        }
        $this->update_post_title( $film->get_field( 'post_title' ) );

        // Drop any cached copy of this film so a later read in the same (cron) process sees the
        // values just written rather than the pre-import snapshot.
        self::invalidate( $this->get_remote_id() );
    }

    public function get_events(): array {
        if ( ! isset( $this->events ) ) {
            $this->events = ( new Event_Query() )->upcoming()->film( $this->get_remote_id() )->get();
        }

        return $this->events;
    }

    /**
     * schema.org JSON-LD for this film and its upcoming screenings (Movie + ScreeningEvent +
     * MovieTheater), as a ready-to-echo <script type="application/ld+json"> string. The companion to
     * get_events() for templates that already hold a Film. Same output as kinola_get_film_schema()
     * and the [kinola_film_screenings] shortcode; empty string when the film is not published or
     * structured data is turned off.
     */
    public function get_schema(): string {
        $manager = kinola_get_schema_manager();

        return $manager ? $manager->get_film_screenings_schema( $this ) : '';
    }

    public static function find_by_local_id( int $id ): ?Film {
        $post = get_post( $id );

        if ( $post ) {
            return new Film( $post );
        }

        return null;
    }

    /**
     * In-request cache of resolved films, keyed by remote id. Only positive (single-match)
     * lookups are stored, so a film that does not exist yet is always re-queried — important
     * for the importers, which call this to check existence before creating a film.
     *
     * The cache is per-process: empty again on the next web request, but a long-running cron
     * process (the background importers) can outlive a write, so code that mutates a film and
     * then re-reads it in the same process must call self::invalidate() — save_api_data() does
     * this for the film it just wrote.
     *
     * @var array<string,Film>
     */
    private static array $by_remote_id = [];

    public static function find_by_remote_id( string $id ): ?Film {
        if ( isset( self::$by_remote_id[ $id ] ) ) {
            return self::$by_remote_id[ $id ];
        }

        $results = ( new \WP_Query( [
            'post_type'              => Helpers::get_films_post_type(),
            'post_status'            => 'any',
            'meta_key'               => self::FIELD_ID,
            'meta_value'             => $id,
            // Lookup by a unique remote id: cap at 2 so the duplicate check below still fires,
            // skip the SQL_CALC_FOUND_ROWS pagination count, and don't prime film term caches
            // (nothing on this path reads them).
            'posts_per_page'         => 2,
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
        ] ) )->get_posts();

        if ( count( $results ) === 1 ) {
            return self::$by_remote_id[ $id ] = new Film( $results[0] );
        } else if ( count( $results ) > 1 ) {
            trigger_error( "More than one WP Post matches film ID {$id}", E_USER_WARNING );
        }

        return null;
    }

    /**
     * Batch-load films by remote id into the request cache in a single query, so a subsequent
     * per-event find_by_remote_id() (e.g. the events-page schema loop) is a cache hit rather than
     * one query per distinct film. Ids that are blank, already cached, or duplicated in the input
     * are skipped; an id matching more than one post is left uncached so find_by_remote_id() still
     * runs its duplicate-detection path.
     *
     * @param string[] $ids
     */
    public static function prime_by_remote_ids( array $ids ): void {
        $missing = array_values( array_unique( array_filter( $ids, static function ( $id ) {
            return (string) $id !== '' && ! isset( self::$by_remote_id[ (string) $id ] );
        } ) ) );

        if ( ! $missing ) {
            return;
        }

        $posts = ( new \WP_Query( [
            'post_type'              => Helpers::get_films_post_type(),
            'post_status'            => 'any',
            'posts_per_page'         => - 1,
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
            'meta_query'             => [
                [
                    'key'     => self::FIELD_ID,
                    'value'   => $missing,
                    'compare' => 'IN',
                ],
            ],
        ] ) )->get_posts();

        // Group by remote id so an id with duplicates is left out of the cache, matching
        // find_by_remote_id()'s single-match-only contract (it warns on duplicates).
        $by_id = [];
        foreach ( $posts as $post ) {
            $film = new Film( $post );
            $id   = (string) $film->get_field( self::FIELD_ID );
            if ( $id !== '' ) {
                $by_id[ $id ][] = $film;
            }
        }

        foreach ( $by_id as $id => $films ) {
            if ( count( $films ) === 1 ) {
                self::$by_remote_id[ $id ] = $films[0];
            }
        }
    }

    /**
     * Drop a single film from the request cache so the next lookup re-reads it from the database.
     * Use after mutating a film within a long-running process (see $by_remote_id).
     */
    public static function invalidate( string $id ): void {
        unset( self::$by_remote_id[ $id ] );
    }

    /**
     * Clear the entire request cache. Primarily for test isolation, where the static cache would
     * otherwise leak resolved films across test cases sharing one process.
     */
    public static function reset_cache(): void {
        self::$by_remote_id = [];
    }

    public static function create( ApiFilm $api_film ): Film {
        $post = wp_insert_post( [
            'post_title'  => $api_film->get_field( 'post_title' ),
            'post_status' => 'publish',
            'post_type'   => Helpers::get_films_post_type(),
        ] );

        $film = new Film( get_post( $post ) );
        $film->save_api_data( $api_film );

        return $film;
    }
}
