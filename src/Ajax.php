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
        $query = new Event_Query();
        $query->filter( $_GET[ self::PARAM_DATE ], $_GET[ self::PARAM_VENUE ], $_GET[ self::PARAM_TIME ] );

        if ( isset( $_GET[ self::PARAM_FILM ] ) && $_GET[ self::PARAM_FILM ] ) {
            $query->film( $_GET[ self::PARAM_FILM ] );
        }

        $filter = new Filter( $query );

        if ( $_GET['field'] === self::PARAM_FILM ) {
            $result = $this->format_for_select2( $filter->get_films() );
        } else if ( $_GET['field'] === self::PARAM_VENUE ) {
            $result = $this->format_for_select2( $filter->get_venues() );
        } else if ( $_GET['field'] === self::PARAM_DATE ) {
            $result = $this->format_for_select2( $filter->get_dates() );
        } else if ( $_GET['field'] === self::PARAM_TIME ) {
            $result = $this->format_for_select2( $filter->get_times() );
        } else {
            echo json_encode( [
                'status'  => 'error',
                'message' => 'Invalid field',
            ] );

            wp_die();
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
