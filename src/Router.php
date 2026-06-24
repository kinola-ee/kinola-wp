<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Admin\Admin;

class Router {

    public static function get_action_url( array $params ): string {
        // Every action URL built here is an admin import trigger, so carry the nonce that
        // Admin::should_run_import_action() verifies (CSRF protection for the import actions).
        $params['_wpnonce'] = wp_create_nonce( Admin::ACTION_NONCE );

        return get_admin_url( null, '?' . http_build_query( $params ) );
    }

    public static function append_message( string $url, string $message ): string {
        if ( stristr( $url, '?' ) === false ) {
            $url .= '?';
        }

        return $url . '&' . Admin::MESSENGER_ACTION . '=' . $message;
    }

    public static function redirect( string $url ) {
        wp_safe_redirect( $url );
        exit;
    }

    public static function get_kinola_film_edit_link( string $id ): string {
        return trailingslashit( KINOLA_URL ) . 'admin/productions/uuid/' . $id;
    }

    public static function get_kinola_api_film_link( string $id ): string {
        return trailingslashit( KINOLA_URL ) . 'api/public/v1/productions/' . $id;
    }

    public static function get_kinola_api_events_link(): string {
        return trailingslashit( KINOLA_URL ) . 'api/public/v1/events';
    }

    public static function get_kinola_api_program_link( string $id ): string {
        return trailingslashit( KINOLA_URL ) . 'api/public/v1/programs/' . $id;
    }

    public static function get_event_checkout_url( string $id ): string {
        return home_url( '/checkout/' . $id );
    }
}
