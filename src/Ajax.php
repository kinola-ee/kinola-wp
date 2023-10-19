<?php

namespace Kinola\KinolaWp;

class Ajax {
    public function init() {
        add_action( 'wp_ajax_kinola_get_filter_options', [ $this, 'get_filter_options' ] );
        add_action( 'wp_ajax_nopriv_kinola_get_filter_options', [ $this, 'get_filter_options' ] );
    }

    public function get_filter_options() {
        $query = new Event_Query();
        $query->filter( $_GET['date'], $_GET['venue'], $_GET['time'] );

        if (isset($_GET['film']) && $_GET['film']) {
            $query->film($_GET['film']);
        }

        $filter = new Filter( $query );

        if ( $_GET['field'] === 'date' ) {
            $result = $this->format_for_select2( $filter->get_dates() );
        } else if ( $_GET['field'] === 'location' ) {
            $result = $this->format_for_select2( $filter->get_venues() );
        } else if ( $_GET['field'] === 'time' ) {
            $result = $this->format_for_select2( $filter->get_times() );
        } else {
            echo json_encode( [
                'status'  => 'error',
                'message' => 'Invalid field',
            ] );
        }

        echo json_encode( [
            'results' => array_values( $result ),
            'pagination' => [
                'more' => false,
            ]
        ] );

        wp_die();
    }

    public function format_for_select2( array $items ): array {
        $results = [];

        foreach ( $items as $key => $item ) {
            $results[] = [
                'id'   => $key,
                'text' => $item,
            ];
        }

        return $results;
    }
}
