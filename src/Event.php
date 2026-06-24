<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Api\Event as ApiEvent;

class Event extends Model {

    public const FIELD_ID = 'event_id';

    // Post meta key for the remaining seat count. Read directly (not via get_field) by
    // get_free_seats() so a known "0" survives — see the note there.
    public const FIELD_FREE_SEATS = 'freeSeats';

    // Memoized parse of the 'time' meta: get_date() and get_time() are routinely called for the
    // same event in one render, and new \DateTime() (in format_datetime) is not free. The bool
    // distinguishes "not yet parsed" from a parsed-to-null (unparseable) result.
    private bool $datetime_resolved = false;
    private ?\DateTime $datetime = null;

    private function get_datetime(): ?\DateTime {
        if ( ! $this->datetime_resolved ) {
            $this->datetime          = Helpers::format_datetime( $this->get_field( 'time' ) );
            $this->datetime_resolved = true;
        }

        return $this->datetime;
    }

    public function get_date(): string {
        $dateTime = $this->get_datetime();

        return $dateTime ? wp_date( get_option( 'date_format' ), $dateTime->getTimestamp() ) : '';
    }

    public function get_time(): string {
        $dateTime = $this->get_datetime();

        return $dateTime ? wp_date( get_option( 'time_format' ), $dateTime->getTimestamp() ) : '';
    }

    public function get_checkout_url(): string {
        // Use checkout_url from API if available (handles both internal and external ticketing)
        $checkout_url = $this->get_field( 'checkout_url' );
        if ( $checkout_url ) {
            return $checkout_url;
        }
        // Fallback to generating local checkout URL
        return Router::get_event_checkout_url( $this->get_remote_id() );
    }

    public function get_film_url(): string {
        return $this->get_film()->get_local_url();
    }

    public function get_api_url(): string {
        return Router::get_kinola_api_events_link();
    }

    public function get_film(): Film {
        return Film::find_by_remote_id( $this->get_field( \Kinola\KinolaWp\Film::FIELD_ID ) );
    }

    public function get_program(): ?Program {
        $program_id = $this->get_field( Program::FIELD_ID );
        if ( $program_id ) {
            return Program::find_by_remote_id( $program_id );
        }

        return null;
    }

    public function get_free_seats(): ?int {
        // Read the raw meta directly rather than via get_field(), which empties "0" and so would make a
        // sold-out screening (0 seats) indistinguishable from one with no seat count. The two must stay
        // distinct: null = seat count unknown, 0 = a known count of zero (sold out).
        $raw = get_post_meta( $this->get_local_id(), self::FIELD_FREE_SEATS, true );

        // Anything non-numeric — an absent value or an unexpected payload — is unknown, not zero:
        // a bare (int) cast would turn it into a false 0 (sold out).
        if ( ! is_numeric( $raw ) ) {
            return null;
        }

        return (int) $raw;
    }

    public function is_coming_soon(): bool {
        return $this->get_field( 'visibility' ) === 'coming_soon';
    }

    public function is_bookable(): bool {
        // Event is bookable if it's not coming_soon and has available seats
        if ( $this->is_coming_soon() ) {
            return false;
        }

        $free_seats = $this->get_free_seats();
        return $free_seats === null || $free_seats > 0;
    }

    public function get_visibility(): string {
        return $this->get_field( 'visibility' ) ?: 'public';
    }

    public function is_free(): bool {
        $event_type = $this->get_field( 'event_type' ) ?: 'paid';
        return in_array( $event_type, [ 'free_registered', 'free_public' ] );
    }

    public function requires_registration(): bool {
        $event_type = $this->get_field( 'event_type' ) ?: 'paid';
        return $event_type === 'free_registered';
    }

    public function set_title( string $production_title, string $date_time ) {
        wp_update_post( [
            'ID'         => $this->get_local_id(),
            'post_title' => self::format_title( $production_title, $date_time ),
        ] );
    }

    public function save_api_data( ApiEvent $event ) {
        foreach ( $event->get_data() as $field => $value ) {
            switch ( $field ) {
                case 'venue':
                    $this->set_venue( $value, $event->get_field( 'venue_details' ) );
                    break;
                case 'venue_details':
                    // Consumed by set_venue() above; not stored as post meta.
                    break;
                default:
                    $this->set_field( $field, $value );
            }
        }
    }

    public function get_venue_name(): string {
        $venue = $this->get_venue();

        return $venue ? $venue->get_name() : '';
    }

    public function get_venue(): ?Venue {
        $term = $this->get_venue_term();

        return $term ? new Venue( $term ) : null;
    }

    /**
     * The raw venue taxonomy term, or null. The internal counterpart to get_venue(): the schema
     * layer and anything that needs the WP_Term directly uses this, while get_venue() returns the
     * richer Venue wrapper.
     */
    protected function get_venue_term(): ?\WP_Term {
        // get_the_terms() reads WP's object-term cache (primed per render by the schema layer,
        // and invalidated by wp_set_object_terms() on import), so repeated calls across a page
        // render don't each hit the database the way the uncached wp_get_object_terms() would.
        $terms = get_the_terms( $this->get_local_id(), Helpers::get_venue_taxonomy_name() );

        return ( is_array( $terms ) && $terms ) ? $terms[0] : null;
    }

    public function set_venue( $venue, ?array $details = null ): void {
        // A Kinola event always has a venue, so a blank name is a malformed/unexpected payload.
        // Guard defensively: skip rather than create a bogus empty term, and leave any existing
        // venue association intact so a transient bad payload can't wipe good data.
        if ( trim( (string) $venue ) === '' ) {
            return;
        }

        $taxonomy  = Helpers::get_venue_taxonomy_name();
        $kinola_id = isset( $details['id'] ) ? trim( (string) $details['id'] ) : '';

        $term = $this->resolve_venue_term( (string) $venue, $kinola_id, $taxonomy );
        if ( ! $term ) {
            return;
        }

        wp_set_object_terms( $this->get_local_id(), $term->term_id, $taxonomy );

        // Stamp the Kinola id so future imports match this venue by id (rename-safe),
        // and backfill it onto a legacy term that predates id-matching.
        if ( $kinola_id ) {
            update_term_meta( $term->term_id, Helpers::TERM_META_KINOLA_ID, $kinola_id );
        }

        $this->update_venue_address( $term, $details );
    }

    /**
     * Persist the venue's address as term meta for schema.org MovieTheater output. Only an import
     * that actually carried venue details ($details !== null) is authoritative about the address:
     * it upserts when present and clears stale meta when the backend no longer sends one, so a
     * removed address stops being published. A name-only/legacy import (null) leaves it as-is.
     */
    private function update_venue_address( \WP_Term $term, ?array $details ): void {
        if ( $details === null ) {
            return;
        }

        $address = array_diff_key( $details, [ 'name' => true, 'id' => true ] );
        if ( $address ) {
            update_term_meta( $term->term_id, Helpers::TERM_META_VENUE_ADDRESS, $address );

            return;
        }

        // Clearing because the import carried venue details but no address fields. Log only when an
        // address was actually stored, so an unexpected wipe (e.g. a trimmed API payload that omits
        // address for a venue that has one) is detectable, not silent.
        $had_address = get_term_meta( $term->term_id, Helpers::TERM_META_VENUE_ADDRESS, true );
        if ( $had_address ) {
            debug_log( "Event import: clearing stored address for venue #{$term->term_id} ({$term->name}); import sent venue details with no address fields." );
        }

        delete_term_meta( $term->term_id, Helpers::TERM_META_VENUE_ADDRESS );
    }

    /**
     * Resolve the venue term to attach this event to, preferring the Kinola id so a
     * venue renamed in Kinola updates in place rather than spawning a duplicate.
     *
     * 1. By id — found: rename in place if the name changed, keeping the slug stable.
     * 2. By name — only a legacy term that has no id yet (it gets the id stamped by the
     *    caller); never hijack a name that already belongs to a different venue id.
     * 3. Otherwise create — with a disambiguated slug when the name is already taken,
     *    so two distinct same-named venues can coexist.
     */
    private function resolve_venue_term( string $name, string $kinola_id, string $taxonomy ): ?\WP_Term {
        // No id (defensive — the backend always sends one): keep the original
        // name-based behaviour, attaching to the existing term or creating it.
        if ( ! $kinola_id ) {
            $named = get_term_by( 'name', $name, $taxonomy );

            return $named instanceof \WP_Term ? $named : $this->insert_venue_term( $name, $taxonomy );
        }

        $term = Helpers::find_venue_term_by_kinola_id( $kinola_id );
        if ( $term ) {
            // Rename in place when the name changed in Kinola. Passing only the name preserves the
            // slug — wp_update_term() merges the current term data first, so it isn't regenerated
            // from the new name.
            if ( $name !== '' && $name !== $term->name ) {
                $updated = wp_update_term( $term->term_id, $taxonomy, [ 'name' => $name ] );
                if ( ! is_wp_error( $updated ) ) {
                    // The per-process id->term cache now holds the pre-rename term; drop it so the
                    // next lookup in this import run re-reads the new name instead of re-renaming.
                    Helpers::invalidate_venue_term_cache( $kinola_id );
                    $term = get_term( $term->term_id, $taxonomy );
                } else {
                    debug_log( "Event import: failed to rename venue term #{$term->term_id} to '{$name}': " . $updated->get_error_message() );
                }
            }

            return $term instanceof \WP_Term ? $term : null;
        }

        // No term for this id yet — only now is the name lookup needed (the id-match path
        // above returns without it). Adopt a legacy (id-less) name match — it gets the id
        // stamped by the caller. A name match that already carries a different id is a
        // distinct venue, so create a new term with a disambiguated slug instead.
        $named = get_term_by( 'name', $name, $taxonomy );
        if ( $named instanceof \WP_Term && ! get_term_meta( $named->term_id, Helpers::TERM_META_KINOLA_ID, true ) ) {
            return $named;
        }

        $slug = $named instanceof \WP_Term ? sanitize_title( $name . '-' . $kinola_id ) : '';

        return $this->insert_venue_term( $name, $taxonomy, $slug );
    }

    private function insert_venue_term( string $name, string $taxonomy, string $slug = '' ): ?\WP_Term {
        $inserted = wp_insert_term( $name, $taxonomy, $slug ? [ 'slug' => $slug ] : [] );
        if ( is_wp_error( $inserted ) ) {
            // wp_insert_term() returns a 'term_exists' error carrying the existing term's id
            // (a concurrent import already created it, or a disambiguated slug collided with
            // an existing term). Recover that id and attach to it rather than returning null
            // and leaving the event venue-less — a Kinola event always has a venue.
            $existing_id = (int) $inserted->get_error_data( 'term_exists' );
            if ( $existing_id ) {
                $term = get_term( $existing_id, $taxonomy );

                return $term instanceof \WP_Term ? $term : null;
            }

            debug_log( "Event import: failed to insert venue term '{$name}': " . $inserted->get_error_message() );

            return null;
        }

        $term = get_term( $inserted['term_id'], $taxonomy );

        return $term instanceof \WP_Term ? $term : null;
    }

    public function delete() {
        wp_delete_post( $this->post->ID, true );
    }

    public static function find_by_local_id( int $id ): ?Event {
        $post = get_post( $id );

        if ( $post ) {
            return new Event( $post );
        }

        return null;
    }

    public static function find_by_remote_id( string $id ): ?Event {
        $results = ( new \WP_Query( [
            'post_type'      => Helpers::get_events_post_type(),
            'post_status'    => 'any',
            'meta_key'       => self::FIELD_ID,
            'meta_value'     => $id,
            'posts_per_page' => - 1,
        ] ) )->get_posts();

        if ( count( $results ) === 1 ) {
            return new Event( $results[0] );
        } else if ( count( $results ) > 1 ) {
            // If we have more than one matching event, delete everything except the last (latest) one.
            debug_log( "More than one WP Post matches event ID {$id}. Deleting extra posts." );
            debug_log( $results );
            $result = array_shift( $results );

            foreach ( $results as $duplicate ) {
                $event = new Event( $duplicate );
                $event->delete();
            }

            return new Event( $result );
        }

        return null;
    }

    public static function create( ApiEvent $api_event ): Event {
        $title = $api_event->get_field( 'production_title' ) ?: '';

        $post = wp_insert_post( [
            'post_title'  => self::format_title( $title, $api_event->get_field( 'time' ) ),
            'post_status' => 'publish',
            'post_type'   => Helpers::get_events_post_type(),
        ] );

        $event = new Event( get_post( $post ) );
        $event->save_api_data( $api_event );

        return $event;
    }

    public static function format_title( string $production_title, string $date_time ): string {
        $dateTime = Helpers::format_datetime( $date_time );
        if ( ! $dateTime ) {
            // Unparseable time — keep the event titled rather than failing the import.
            return $production_title;
        }

        return $production_title . ' - ' .
               $dateTime->format( get_option( 'date_format' ) ) . ' ' .
               $dateTime->format( get_option( 'time_format' ) );
    }
}
