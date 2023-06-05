<?php

namespace Kinola\KinolaWp;

abstract class Model {

    protected ?\WP_Post $post = null;

    public function __construct( \WP_Post $post ) {
        $this->post = $post;
    }

    public function get_post(): ?\WP_Post {
        return $this->post;
    }

    public function get_title() {
        return apply_filters( 'the_title', $this->post->post_title );
    }

    public function get_field( string $name, bool $compact = true ) {
        $value = get_post_meta( $this->post->ID, $name, true );

        if ( is_array( $value ) && $compact ) {
            return implode( ', ', $value );
        }

        return $value;
    }

    public function get_fields() {
        return get_post_meta( $this->post->ID );
    }

    public function get_local_id(): int {
        return $this->post->ID;
    }

    public function get_remote_id(): string {
        return $this->get_field( static::FIELD_ID );
    }

    public function set_field( string $field, $value ) {
        update_post_meta( $this->post->ID, $field, $value );
    }
}
