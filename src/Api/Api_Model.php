<?php

namespace Kinola\KinolaWp\Api;

use Kinola\KinolaWp\Helpers;

abstract class Api_Model {

    protected array $data = [];

    public function __construct( array $data ) {
        $this->data = $this->reformat( $data );
    }

    public function get_data(): array {
        return $this->data;
    }

    public function get_field( string $field ) {
        return $this->data[ $field ] ?? null;
    }

    /**
     * Try to get the translation of the title in default WP language.
     */
    public function resolve_post_title( $title ) {
        if ( is_array( $title ) ) {
            if ( isset( $title[ Helpers::get_language() ] ) ) {
                return $title[ Helpers::get_language() ];
            } else {
                return $title[ array_key_first( $title ) ];
            }
        }

        return $title;
    }
}
