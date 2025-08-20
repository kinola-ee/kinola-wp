<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Router;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Film_Importer_List_Table extends \WP_List_Table {

    protected Film_Importer $importer;

    public function __construct( Film_Importer $importer ) {
        $this->importer = $importer;

        parent::__construct( [
            'singular' => __( 'Film', 'kinola' ),
            'plural'   => __( 'Films', 'kinola' ),
            'ajax'     => false,
        ] );
    }

    public function get_columns(): array {
        return [
            'title'          => _x( 'Title', 'Admin', 'kinola' ),
            'title_original' => _x( 'Original title', 'Admin', 'kinola' ),
            'actions'        => _x( 'Actions', 'Admin', 'kinola' ),
        ];
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'actions':
                $url = Router::get_action_url( [ Admin::IMPORT_FILM_ACTION => $item['id'] ] );

                return "<a href='{$url}'>" . _x( 'Import', 'Admin', 'kinola' ) . "</a>";
            default:
                return $item[ $column_name ];
        }
    }

    public function prepare_items() {
        $this->_column_headers = [ $this->get_columns(), [], [] ];

        $data = $this->importer->get_films();
        $data = $this->remove_duplicates( $data );

        if ( empty( $data ) ) {
            $this->items = [];
        }

        $films = [];
        $skipped_private = [];
        
        foreach ( $data as $film ) {
            // Skip non-array items
            if ( ! is_array( $film ) ) {
                continue;
            }
            
            // Skip films with missing required fields (likely private films)
            if ( ! isset( $film['id'], $film['name'], $film['originalName'] ) ) {
                // Track private/incomplete films for potential logging
                if ( isset( $film['id'], $film['visibility'] ) ) {
                    if ( $film['visibility'] === 'private' ) {
                        $skipped_private[] = $film['id'];
                    } else {
                        // Log unexpected case: non-private film missing required fields
                        error_log( "Film_Importer_List_Table: WARNING - Film {$film['id']} with visibility '{$film['visibility']}' is missing required fields" );
                    }
                }
                continue;
            }
            
            $films[] = [
                'id'             => $film['id'],
                'title'          => $film['name'],
                'title_original' => $film['originalName'],
            ];
        }
        
        // Only log if we skipped private films
        if ( ! empty( $skipped_private ) ) {
            error_log( "Film_Importer_List_Table: Skipped " . count( $skipped_private ) . " private film(s)" );
        }

        $this->items = $films;
    }

    function remove_duplicates( array $films ): array {
        $results = array_map( function ( $film ) {
            return $film['id'];
        }, $films );

        $unique = array_unique( $results );

        return array_values( array_intersect_key( $films, $unique ) );
    }
}
