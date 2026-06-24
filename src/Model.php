<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Admin\Admin_Messenger;

abstract class Model {

    protected ?\WP_Post $post = null;

    protected array $translatable = [];

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
        $value = get_post_meta(
            $this->post->ID,
            $name,
            true
        );

        if ( empty( $value ) ) {
            return '';
        }

        // The first level array of these elements should be $language => $value
        if ( in_array( $name, $this->translatable ) ) {
            if ( isset( $value[ Helpers::get_language() ] ) ) {
                $value = $value[ Helpers::get_language() ];
            } else {
                $value = '';
                ( new Admin_Messenger )->add_message(
                    sprintf(
                        /* translators: 1: the field name, 2: the website's configured language. */
                        __( "The field '%1\$s' does not have a corresponding translation in Kinola for this website's configured language (%2\$s).", 'kinola' ),
                        $name,
                        Helpers::get_language()
                    )
                );
            }
        }

        if ( is_array( $value ) && $compact ) {
            // Check if the given array is multidimensional - if yes, do not flatten it
            if ( isset( $value[ array_key_first( $value ) ] ) && ! is_array( $value[ array_key_first( $value ) ] ) ) {
                return implode( ', ', $value );
            }

            if ( empty( $value ) ) {
                return '';
            }
        }

        return $value;
    }

    public function get_fields() {
        return get_post_meta( $this->post->ID );
    }

    public function get_custom_fields() {
        return get_post_meta( $this->post->ID, 'custom_fields', true );
    }

    public function get_local_id(): int {
        return $this->post->ID;
    }

    public function get_remote_id(): string {
        return $this->get_field( static::FIELD_ID );
    }

    public function set_field( string $field, $value ) {
        update_post_meta(
            $this->post->ID,
            $field,
            $value
        );
    }

    /**
     * Update the WP post title (the post column, not meta) when it has actually changed.
     * Keeps renamed remote entities in sync on re-import. Skips empty titles so an
     * incomplete payload can't wipe the title, and preserves the existing slug.
     */
    protected function update_post_title( $title ): void {
        if ( is_string( $title ) && $title !== '' && $title !== $this->post->post_title ) {
            wp_update_post( [
                'ID'         => $this->post->ID,
                'post_title' => $title,
            ] );
            $this->post->post_title = $title;
        }
    }
}
