<?php

namespace Kinola\KinolaWp\Schema;

use Kinola\KinolaWp\Venue;

class Movie_Theater_Schema {

    public static function get_id( Venue $venue ): string {
        // Prefer Kinola's stable venue id; fall back to the term slug for a legacy venue the import
        // has not yet backfilled an id onto. The slug — not the term id — keeps the @id stable
        // across environments, since term ids differ between databases but slugs do not.
        $id = $venue->get_kinola_id() ?: $venue->get_slug();

        return home_url( '/' ) . '#venue-' . $id;
    }

    public static function build( Venue $venue ): array {
        $node = [
            '@type' => 'MovieTheater',
            '@id'   => self::get_id( $venue ),
            'name'  => $venue->get_name(),
        ];

        // The stored address fields, passed through the kinola/schema/venue_address filter so a hook
        // can supply or override them; the venue term is given for context.
        $address = apply_filters( 'kinola/schema/venue_address', $venue->get_address(), $venue->get_term() );
        // Guard the filter return: a hook handing back a non-array must not break build_address().
        if ( ! is_array( $address ) ) {
            $address = [];
        }

        $address_node = self::build_address( $address );
        if ( $address_node ) {
            $node['address'] = $address_node;
        }

        return $node;
    }

    protected static function build_address( $address ): ?array {
        if ( ! is_array( $address ) ) {
            return null;
        }

        $mapping = [
            'street'   => 'streetAddress',
            'locality' => 'addressLocality',
            'postcode' => 'postalCode',
            'country'  => 'addressCountry',
        ];

        $node = [ '@type' => 'PostalAddress' ];
        foreach ( $mapping as $source => $target ) {
            // Enforce scalar Text: drop arrays/objects (a tampered row or a
            // kinola/schema/venue_address hook must not embed nodes here) and
            // coerce a stray number to string so every field is valid schema.org.
            $value = $address[ $source ] ?? '';
            if ( is_scalar( $value ) && (string) $value !== '' ) {
                $node[ $target ] = (string) $value;
            }
        }

        return count( $node ) > 1 ? $node : null;
    }
}
