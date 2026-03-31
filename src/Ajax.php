<?php

namespace Kinola\KinolaWp;

class Ajax {
    public const PARAM_DATE  = 'date';
    public const PARAM_VENUE = 'venue';
    public const PARAM_TIME  = 'time';
    public const PARAM_FILM  = 'film';

    public function init() {
        add_action( 'wp_ajax_kinola_get_filter_options', [ $this, 'get_filter_options' ] );
        add_action( 'wp_ajax_nopriv_kinola_get_filter_options', [ $this, 'get_filter_options' ] );
    }

    public function get_filter_options() {
        // Verify nonce for security
        check_ajax_referer( 'kinola_filter_nonce', 'nonce' );

        $date = isset( $_GET[ self::PARAM_DATE ] )
            ? sanitize_text_field( wp_unslash( $_GET[ self::PARAM_DATE ] ) )
            : null;

        $venue = isset( $_GET[ self::PARAM_VENUE ] )
            ? sanitize_text_field( wp_unslash( $_GET[ self::PARAM_VENUE ] ) )
            : null;

        $time = isset( $_GET[ self::PARAM_TIME ] )
            ? sanitize_text_field( wp_unslash( $_GET[ self::PARAM_TIME ] ) )
            : null;

        $film = isset( $_GET[ self::PARAM_FILM ] )
            ? sanitize_text_field( wp_unslash( $_GET[ self::PARAM_FILM ] ) )
            : null;

        // Build dropdown query with only upcoming events (user filters not applied to preserve full option scope)
        $dropdown_query = ( new Event_Query() )->upcoming();

        $filter = new Filter( $dropdown_query );

        // Parse and validate allowed_venues parameter
        if ( isset( $_GET['allowed_venues'] ) && ! empty( $_GET['allowed_venues'] ) ) {
            $allowed_venues = Helpers::parse_venue_names( $_GET['allowed_venues'], true );

            // Validate that parsing succeeded
            if ( empty( $allowed_venues ) ) {
                wp_send_json_error( [ 'message' => 'Invalid allowed_venues parameter' ], 400 );
                wp_die();
            }

            $filter->set_allowed_venues( $allowed_venues );
            $dropdown_query->filterByAllowedVenues( $allowed_venues );
        }

        $field = isset( $_GET['field'] )
            ? sanitize_text_field( wp_unslash( $_GET['field'] ) )
            : '';

        $valid_fields = [ self::PARAM_FILM, self::PARAM_VENUE, self::PARAM_DATE, self::PARAM_TIME ];
        if ( ! in_array( $field, $valid_fields, true ) ) {
            wp_send_json_error( [ 'message' => 'Invalid field' ], 400 );
            wp_die();
        }

        if ( $field === self::PARAM_FILM ) {
            $result = $this->format_for_select2( $filter->get_films() );
        } else if ( $field === self::PARAM_VENUE ) {
            $result = $this->format_for_select2( $filter->get_venues() );
        } else if ( $field === self::PARAM_DATE ) {
            $result = $this->format_for_select2( $filter->get_dates() );
        } else if ( $field === self::PARAM_TIME ) {
            $result = $this->format_for_select2( $filter->get_times() );
        }

        echo json_encode( [
            'results'    => array_values( $result ),
            'pagination' => [
                'more' => false,
            ],
        ] );

        wp_die();
    }

    public function format_for_select2( array $items ): array {
        $results = [];

        foreach ( $items as $key => $item ) {
            $results[] = [
                'id'   => $key,
                'text' => html_entity_decode( $item, ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
            ];
        }

        return $results;
    }
}
