<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Schema\Movie_Theater_Schema;

/**
 * A venue — a term of the venue taxonomy — wrapped in a small object so it reads like the Film and
 * Event models. Unlike those it does NOT extend Model: venues are taxonomy terms (term meta, term id),
 * not posts, so Model's WP_Post/post-meta machinery does not fit. This is a deliberately thin wrapper
 * that centralizes the venue meta keys and delegates schema output to the existing schema builders
 * rather than duplicating them.
 */
class Venue {

    protected \WP_Term $term;

    public function __construct( \WP_Term $term ) {
        $this->term = $term;
    }

    public static function find_by_name( string $name ): ?Venue {
        $term = get_term_by( 'name', $name, Helpers::get_venue_taxonomy_name() );

        return $term instanceof \WP_Term ? new self( $term ) : null;
    }

    public static function find_by_kinola_id( string $kinola_id ): ?Venue {
        $term = Helpers::find_venue_term_by_kinola_id( $kinola_id );

        return $term ? new self( $term ) : null;
    }

    /**
     * Every venue, ordered by WP's default term ordering.
     *
     * @return Venue[]
     */
    public static function all(): array {
        $terms = get_terms( [
            'taxonomy'   => Helpers::get_venue_taxonomy_name(),
            'hide_empty' => false,
        ] );

        if ( is_wp_error( $terms ) ) {
            return [];
        }

        return array_map( static function ( \WP_Term $term ) {
            return new self( $term );
        }, $terms );
    }

    /**
     * Term IDs of every venue — the id-only counterpart of all(), for callers (the schema layer)
     * that resolve venues by id and batch-load their meta rather than hydrating Venue objects.
     *
     * @return int[]
     */
    public static function all_term_ids(): array {
        $term_ids = get_terms( [
            'taxonomy'   => Helpers::get_venue_taxonomy_name(),
            'hide_empty' => false,
            'fields'     => 'ids',
        ] );

        return is_wp_error( $term_ids ) ? [] : array_map( 'intval', $term_ids );
    }

    /**
     * Term IDs of venues that have at least one upcoming screening. The single definition of that
     * set, shared by with_upcoming_screenings() and the schema layer, so the two cannot drift on
     * what "upcoming" means.
     *
     * @return int[]
     */
    public static function upcoming_term_ids(): array {
        return ( new Event_Query() )->upcoming()->get_venue_term_ids();
    }

    /**
     * Venues that have at least one upcoming screening — the default set
     * [kinola_venues_structured_data] / kinola_get_venues_schema() output.
     *
     * @return Venue[]
     */
    public static function with_upcoming_screenings(): array {
        $taxonomy = Helpers::get_venue_taxonomy_name();

        $venues = [];
        foreach ( self::upcoming_term_ids() as $term_id ) {
            $term = get_term( $term_id, $taxonomy );
            if ( $term instanceof \WP_Term ) {
                $venues[] = new self( $term );
            }
        }

        return $venues;
    }

    public function get_term(): \WP_Term {
        return $this->term;
    }

    public function get_term_id(): int {
        return (int) $this->term->term_id;
    }

    public function get_name(): string {
        return (string) $this->term->name;
    }

    public function get_slug(): string {
        return (string) $this->term->slug;
    }

    /**
     * The venue's Kinola id (stable across renames), or '' if this venue predates id-based matching.
     */
    public function get_kinola_id(): string {
        return (string) get_term_meta( $this->get_term_id(), Helpers::TERM_META_KINOLA_ID, true );
    }

    /**
     * The stored postal address fields (street, locality, postcode, country), or [] if none.
     * This is the raw stored data; the schema-only kinola/schema/venue_address filter is applied
     * in Movie_Theater_Schema, not here.
     *
     * @return array
     */
    public function get_address(): array {
        $meta = get_term_meta( $this->get_term_id(), Helpers::TERM_META_VENUE_ADDRESS, true );

        return is_array( $meta ) && isset( $meta['address'] ) && is_array( $meta['address'] )
            ? $meta['address']
            : [];
    }

    /**
     * schema.org JSON-LD (a MovieTheater node) for this venue, as a ready-to-echo
     * <script type="application/ld+json"> string. The companion to Film::get_schema(); same output
     * as kinola_get_venues_schema() limited to this one venue. Empty string when structured data is
     * turned off (Kinola > Settings / the kinola/schema/enabled filter).
     */
    public function get_schema(): string {
        $manager = kinola_get_schema_manager();

        return $manager ? $manager->render( [ Movie_Theater_Schema::build( $this ) ] ) : '';
    }
}
