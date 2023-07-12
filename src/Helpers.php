<?php

namespace Kinola\KinolaWp;

class Helpers {

    public static function get_language(): string {
        return apply_filters( 'kinola/language', self::get_language_from_locale( get_locale() ) );
    }

    public static function get_assets_url( string $path ): string {
        return trailingslashit( plugins_url( 'kinola/assets' ) ) . $path;
    }

    public static function get_checkout_url_slug(): string {
        return apply_filters( 'kinola/checkout/slug', 'checkout' );
    }

    public static function get_films_post_type(): string {
        return apply_filters( 'kinola/post_type/film', 'film' );
    }

    public static function get_events_post_type(): string {
        return apply_filters( 'kinola/post_type/event', 'event' );
    }

    public static function get_venue_taxonomy_name(): string {
        return apply_filters( 'kinola/taxonomy/venue', 'venue' );
    }

    public static function get_date_parameter_slug(): string {
        return apply_filters( 'kinola/filter/date', 'date' );
    }

    public static function get_venue_parameter_slug(): string {
        return apply_filters( 'kinola/filter/date', 'venue' );
    }

    public static function format_datetime( string $date_time_string ): \DateTime {
        $date_time = new \DateTime( $date_time_string, new \DateTimeZone( "UTC" ) );
        $date_time->setTimezone( new \DateTimeZone( wp_timezone_string() ) );

        return $date_time;
    }

    public static function get_language_from_locale( string $locale ) {
        if ( stristr( $locale, '_' ) !== false ) {
            return explode( '_', $locale )[0];
        }

        if ( stristr( $locale, '-' ) !== false ) {
            return explode( '-', $locale )[0];
        }

        return $locale;
    }
}
