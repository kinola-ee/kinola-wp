<?php

namespace Kinola\KinolaWp\Schema;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\Router;

class Screening_Event_Schema {

    private const STATUS_SCHEDULED      = 'https://schema.org/EventScheduled';
    private const ATTENDANCE_OFFLINE    = 'https://schema.org/OfflineEventAttendanceMode';
    private const AVAILABILITY_IN_STOCK = 'https://schema.org/InStock';
    private const AVAILABILITY_SOLD_OUT = 'https://schema.org/SoldOut';

    public static function get_id( Event $event ): string {
        // The local checkout URL is a real page on this site and contains the
        // stable Kinola event UUID. Deliberately not get_checkout_url(), which
        // may point to an off-site domain and so isn't a stable on-site identifier.
        return Router::get_event_checkout_url( $event->get_remote_id() ) . '#screening';
    }

    public static function build( Event $event, Film $film ): array {
        $node = [
            '@type'               => 'ScreeningEvent',
            '@id'                 => self::get_id( $event ),
            // Prefer the film's translatable title so the name follows the page
            // language; production_title is an untranslated import snapshot.
            'name'                => $film->get_field( 'title' ) ?: ( $event->get_field( 'production_title' ) ?: $film->get_title() ),
            'eventStatus'         => self::STATUS_SCHEDULED,
            'eventAttendanceMode' => self::ATTENDANCE_OFFLINE,
            'workPresented'       => [ '@id' => Movie_Schema::get_id( $film ) ],
        ];

        $poster = Schema_Helpers::safe_url( $film->get_field( 'poster' ) );
        if ( $poster ) {
            $node['image'] = $poster;
        }

        // No endDate: it would be a guess (start + runtime ignores trailers
        // and ads); the film length is already stated as Movie duration.
        $time     = $event->get_field( 'time' );
        $datetime = $time ? Helpers::format_datetime( $time ) : null;
        if ( $datetime ) {
            $node['startDate'] = $datetime->format( 'c' );
        }

        $venue = $event->get_venue();
        if ( $venue ) {
            $node['location'] = [ '@id' => Movie_Theater_Schema::get_id( $venue ) ];
        }

        $languages = Schema_Helpers::normalize_list( $film->get_field( 'languages', false ) );
        if ( $languages ) {
            $node['inLanguage'] = $languages;
        }

        $subtitles = Schema_Helpers::normalize_list( $film->get_field( 'subtitles', false ) );
        if ( $subtitles ) {
            $node['subtitleLanguage'] = $subtitles;
        }

        if ( $event->is_free() ) {
            $node['isAccessibleForFree'] = true;
        }

        // Coming soon screenings are real scheduled events but have no
        // purchasable ticket yet, so they get no offer at all.
        if ( ! $event->is_coming_soon() ) {
            $node['offers'] = self::build_offer( $event, $film );
        }

        return $node;
    }

    protected static function build_offer( Event $event, Film $film ): array {
        $offer = [ '@type' => 'Offer' ];

        // Both branches are normally http(s) (a WP permalink or the API/Router checkout URL);
        // validate defensively and omit the field rather than emit a non-http(s) URL.
        $url = Schema_Helpers::safe_url(
            $event->requires_registration() || ! $event->is_free()
                ? $event->get_checkout_url()
                : $film->get_local_url()
        );
        if ( $url ) {
            $offer['url'] = $url;
        }

        // Mirrors the sold-out check in templates/events.php and film/screenings.php so the structured
        // data always matches the visible page. A null seat count is unknown (stays in stock); only a
        // known count of 0 is sold out.
        $free_seats            = $event->get_free_seats();
        $offer['availability'] = $free_seats === 0
            ? self::AVAILABILITY_SOLD_OUT
            : self::AVAILABILITY_IN_STOCK;

        if ( $event->is_free() ) {
            $offer['price']         = '0';
            $offer['priceCurrency'] = self::default_currency();
        } else {
            $range = $event->get_field( 'price_range', false );
            $low   = is_array( $range ) ? self::format_price( $range['min'] ?? null ) : null;
            $high  = is_array( $range ) ? self::format_price( $range['max'] ?? null ) : null;

            if ( $low !== null && $high !== null ) {
                if ( $low === $high ) {
                    // A single price stays a plain Offer.
                    $offer['price'] = $low;
                } else {
                    // A spread becomes an AggregateOffer — schema.org's (and Google's) shape for a
                    // low/high price range. AggregateOffer extends Offer, so url and availability
                    // already set above stay valid on it.
                    $offer['@type']     = 'AggregateOffer';
                    $offer['lowPrice']  = $low;
                    $offer['highPrice'] = $high;
                }

                // The API's own currency is authoritative for a real ticket price; fall back to the
                // same filtered default the free branch uses if the payload omits it.
                $offer['priceCurrency'] = ( isset( $range['currency'] ) && is_string( $range['currency'] ) && $range['currency'] !== '' )
                    ? $range['currency']
                    : self::default_currency();
            }
        }

        return $offer;
    }

    /**
     * The configured fallback currency, via the kinola/schema/currency filter. Guards the filter
     * return so a hook handing back a non-string (or empty) can't produce an invalid priceCurrency —
     * mirrors the is_string() check applied to the API's own currency.
     */
    protected static function default_currency(): string {
        $currency = apply_filters( 'kinola/schema/currency', 'EUR' );

        return is_string( $currency ) && $currency !== '' ? $currency : 'EUR';
    }

    /**
     * Normalize an API price (int/float/numeric string) to a schema.org price string, or null when
     * it is not a usable, non-negative number. The `+ 0` keeps natural number formatting — 3 → "3",
     * 10.5 → "10.5" — without the trailing zeros (string) on a float could introduce.
     */
    protected static function format_price( $value ): ?string {
        if ( ! is_numeric( $value ) || $value < 0 ) {
            return null;
        }

        return (string) ( $value + 0 );
    }
}
