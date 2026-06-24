<?php

namespace Kinola\KinolaWp\Schema;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\Venue;

class Schema_Manager {

    public function init() {
        add_action( 'wp_head', [ $this, 'output_film_schema' ] );
    }

    public function output_film_schema() {
        if ( ! Helpers::is_schema_enabled() || ! is_singular( Helpers::get_films_post_type() ) ) {
            return;
        }

        $graph = $this->get_film_graph( new Film( get_queried_object() ) );

        if ( $graph ) {
            echo $this->render( $graph ) . "\n";
        }
    }

    /**
     * A film must be published to appear in public schema. The singular-page path (output_film_schema)
     * only renders for a publicly viewable film, but the [kinola_film_screenings] and [kinola_events]
     * paths resolve films by id and would otherwise expose a draft/pending/private film's title and
     * permalink in @graph.
     */
    private function is_film_published( Film $film ): bool {
        return get_post_status( $film->get_post() ) === 'publish';
    }

    protected function get_film_graph( Film $film ): array {
        if ( ! $this->is_film_published( $film ) ) {
            return [];
        }

        $events = $film->get_events();
        $this->prime_event_term_cache( $events );

        $graph  = [ Movie_Schema::build( $film ) ];
        $venues = [];

        foreach ( $events as $event ) {
            $graph[] = Screening_Event_Schema::build( $event, $film );

            $venue = $event->get_venue();
            if ( $venue ) {
                $venues[ $venue->get_term_id() ] = $venue;
            }
        }

        $graph = $this->append_venue_nodes( $graph, $venues );

        return $graph;
    }

    /**
     * Schema for the [kinola_film_screenings] shortcode; appended to its output so a
     * custom film page carries the same Movie + ScreeningEvent + MovieTheater markup
     * the film's own page emits via wp_head. Reuses get_film_graph(), so both surfaces
     * describe the film identically — mirroring how [kinola_events] rides along with
     * its own schema.
     *
     * Note: get_film_graph() uses Film::get_events() (all upcoming screenings), which is
     * intentionally broader than what the shortcode template renders (its first page of
     * filtered screenings). The schema deliberately describes the film's full upcoming
     * slate for SEO; this costs one extra Event_Query per render.
     */
    public function get_film_screenings_schema( Film $film ): string {
        if ( ! Helpers::is_schema_enabled() ) {
            return '';
        }

        // Don't double-emit: on the film's own singular page, output_film_schema() already emits this
        // same graph via wp_head. Bail so a [kinola_film_screenings] shortcode placed on that page does
        // not produce a second <script> with duplicate @id nodes.
        if ( is_singular( Helpers::get_films_post_type() ) && get_queried_object_id() === $film->get_local_id() ) {
            return '';
        }

        $graph = $this->get_film_graph( $film );

        // get_film_graph() returns [] for a non-published film; emit nothing rather than an empty @graph.
        return $graph ? $this->render( $graph ) : '';
    }

    /**
     * Schema for the events listing page; appended to the shortcode output,
     * so the graph describes exactly the events being displayed.
     *
     * @param Event[] $events
     */
    public function get_events_page_schema( array $events ): string {
        if ( ! Helpers::is_schema_enabled() ) {
            return '';
        }

        $this->prime_event_term_cache( $events );

        // Batch-load every event's film in one query so the per-event Film::find_by_remote_id()
        // below is a cache hit, not a query each — otherwise a page of N distinct films costs N
        // queries on a cold request.
        Film::prime_by_remote_ids( array_map(
            static function ( Event $event ) {
                return $event->get_field( Film::FIELD_ID );
            },
            $events
        ) );

        $graph  = [];
        $films  = [];
        $venues = [];

        foreach ( $events as $event ) {
            $remote_id = $event->get_field( Film::FIELD_ID );
            if ( empty( $remote_id ) ) {
                // Malformed event with no film id — skip the lookup rather than query for meta_value ''.
                continue;
            }

            $film = Film::find_by_remote_id( $remote_id );
            if ( ! $film || ! $this->is_film_published( $film ) ) {
                continue;
            }

            $graph[] = Screening_Event_Schema::build( $event, $film );

            $films[ $film->get_local_id() ] = $film;

            $venue = $event->get_venue();
            if ( $venue ) {
                $venues[ $venue->get_term_id() ] = $venue;
            }
        }

        if ( ! $graph ) {
            return '';
        }

        foreach ( $films as $film ) {
            $graph[] = Movie_Schema::build_reference( $film );
        }

        $graph = $this->append_venue_nodes( $graph, $venues );

        return $this->render( $graph );
    }

    /**
     * MovieTheater schema for the [kinola_venues_structured_data] shortcode, giving the venue a
     * canonical home on a page the admin chooses. Reads the venue address from
     * term meta, so it works on any page independent of the events query and
     * shares the same @id the ScreeningEvent location nodes reference.
     *
     * @param array $atts Shortcode attributes; 'name' optionally limits output to specific venue
     *                    name(s) (comma-separated). Empty = venues with upcoming screenings, or all
     *                    venues if none are scheduled.
     */
    public function get_venue_schema( array $atts ): string {
        if ( ! Helpers::is_schema_enabled() ) {
            return '';
        }

        $taxonomy = Helpers::get_venue_taxonomy_name();
        $graph    = [];

        $term_ids = $this->resolve_venue_ids( $atts );
        // Prime term meta in one query so each Movie_Theater_Schema::build() reads the
        // venue address from cache rather than issuing a get_term_meta() query per venue.
        if ( $term_ids ) {
            update_meta_cache( 'term', $term_ids );
        }

        foreach ( $term_ids as $term_id ) {
            $term = get_term( $term_id, $taxonomy );
            if ( $term instanceof \WP_Term ) {
                $graph[] = Movie_Theater_Schema::build( new Venue( $term ) );
            }
        }

        return $graph ? $this->render( $graph ) : '';
    }

    /**
     * Venue term IDs to output for [kinola_venues_structured_data].
     *
     * - name="…" → exactly those venues (nothing on no match).
     * - default  → venues that have a scheduled screening; if none are scheduled, every venue term,
     *              so a deliberately-placed shortcode always emits something.
     *
     * @return int[]
     */
    protected function resolve_venue_ids( array $atts ): array {
        $names = Helpers::parse_venue_names( $atts['name'] ?? '', true );
        if ( $names ) {
            // get_venue_term_ids() returns the [0] sentinel ("impossible id", intended for
            // tax_query) when no name matches; array_filter drops it so a typo yields [] rather
            // than a get_term( 0 ) call that renders nothing with no hint why.
            return array_filter( Helpers::get_venue_term_ids( $names ) );
        }

        // No name filter: venues that currently have an upcoming screening, via Venue's single
        // definition of that set — so "active venues" means one thing across the plugin. Fall back
        // to every venue when nothing is scheduled, so a deliberately-placed shortcode always emits
        // something.
        return Venue::upcoming_term_ids() ?: Venue::all_term_ids();
    }

    /**
     * Prime the object-term cache for a set of events in a single query, so the per-event venue
     * lookups during graph building — Event::get_venue() and the one inside
     * Screening_Event_Schema::build(), which read the venue term via get_the_terms() — are cache
     * hits instead of one query each.
     *
     * @param Event[] $events
     */
    protected function prime_event_term_cache( array $events ): void {
        $event_ids = array_filter( array_map(
            static function ( $event ) {
                return $event->get_local_id();
            },
            $events
        ) );

        if ( $event_ids ) {
            update_object_term_cache( $event_ids, Helpers::get_events_post_type() );
        }
    }

    /**
     * Append a MovieTheater node for each collected venue. The $venues map is term-id-keyed so
     * each venue appears once. Shared by get_film_graph() and get_events_page_schema().
     *
     * @param array            $graph
     * @param array<int,Venue> $venues
     * @return array
     */
    private function append_venue_nodes( array $graph, array $venues ): array {
        if ( $venues ) {
            // Prime term meta for every venue in one query so each Movie_Theater_Schema::build()
            // reads the address from cache rather than issuing a get_term_meta() query per venue.
            update_meta_cache( 'term', array_map(
                static function ( Venue $venue ) {
                    return $venue->get_term_id();
                },
                array_values( $venues )
            ) );
        }

        foreach ( $venues as $venue ) {
            $graph[] = Movie_Theater_Schema::build( $venue );
        }

        return $graph;
    }

    public function render( array $graph ): string {
        // Defense-in-depth gate: the public methods above each bail early when the toggle is off, but
        // every non-empty schema string also flows through render(), so guarding here too means no
        // surface — even a future one that forgets its own check — can emit structured data while off.
        if ( ! Helpers::is_schema_enabled() ) {
            return '';
        }

        // Fires once per rendered <script> block, so on a page with several Kinola shortcodes
        // it runs once per block (each with its own graph), not once for the whole page.
        $filtered = apply_filters( 'kinola/schema/graph', $graph );

        // Guard the filter return defensively: fall back to the original graph if a hook hands
        // back a non-array, and drop any element that is not itself an array, so a stray scalar
        // from a third-party hook can't land in @graph as an invalid node. array_values() re-keys
        // the result so it still encodes as a JSON array (a gapped key set would become an object).
        $graph = array_values( array_filter( is_array( $filtered ) ? $filtered : $graph, 'is_array' ) );

        $data = [
            '@context' => 'https://schema.org',
            '@graph'   => $graph,
        ];

        $json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT );

        // wp_json_encode() returns false on failure (e.g. malformed UTF-8 in a meta value);
        // emit nothing rather than a broken "<script>false</script>" block.
        if ( $json === false ) {
            return '';
        }

        return '<script type="application/ld+json">' . $json . '</script>';
    }
}
