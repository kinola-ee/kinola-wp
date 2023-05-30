<?php

namespace Kinola\KinolaWp;

class Helpers {

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

    public static function format_datetime( string $date_time_string ): \DateTime {
        $date_time = new \DateTime( $date_time_string, new \DateTimeZone( "UTC" ) );
        $date_time->setTimezone( new \DateTimeZone( wp_timezone_string() ) );

        return $date_time;
    }
}
