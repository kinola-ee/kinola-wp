<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Pages\Events;
use Kinola\KinolaWp\Pages\Films;
use Kinola\KinolaWp\Pages\Single_Film;

class Bootstrap {
    public function __construct() {
        add_action( 'init', [ $this, 'register_films_post_type' ], 1 );
        add_action( 'init', [ $this, 'register_events_post_type' ], 1 );
        add_action( 'init', [ $this, 'register_venue_taxonomy' ], 1 );
        add_action( 'init', [ $this, 'register_endpoints' ] );
        add_action( 'init', [ $this, 'register_shortcodes' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'template_include', [ $this, 'override_checkout_template' ] );
        add_filter( 'the_content', [ $this, 'override_single_film_content' ] );

        add_filter( 'the_title', [ $this, 'translate_post_title' ], 10, 2 );

        $scheduler = new Scheduler();
        $scheduler->schedule_events();

        $ajax = new Ajax();
        $ajax->init();
    }

    public function register_films_post_type() {
        register_post_type( Helpers::get_films_post_type(), [
            'label'               => __( 'Film', 'kinola' ),
            'supports'            => apply_filters( 'kinola/post_type/film/supports', [
                'title',
                'revisions',
            ] ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-editor-video',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => false,
            'has_archive'         => false,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
            'capabilities'        => [
                'create_posts' => false,
            ],
            'map_meta_cap'        => true,
            'labels'              => [
                'name'          => __( 'Films', 'kinola' ),
                'singular_name' => __( 'Film', 'kinola' ),
                'menu_name'     => _x( 'Films', 'Admin', 'kinola' ),
            ],
        ] );
    }

    public function register_events_post_type() {
        register_post_type( Helpers::get_events_post_type(), [
            'label'               => __( 'Event', 'kinola' ),
            'supports'            => apply_filters( 'kinola/post_type/event/supports', [
                'title',
            ] ),
            'hierarchical'        => false,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-megaphone',
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => false,
            'rewrite'             => false,
            'can_export'          => false,
            'has_archive'         => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
            'capabilities'        => [
                'create_posts' => false,
            ],
            'map_meta_cap'        => true,
            'labels'              => [
                'name'          => __( 'Events', 'kinola' ),
                'singular_name' => __( 'Event', 'kinola' ),
                'menu_name'     => _x( 'Events', 'Admin', 'kinola' ),
            ],
        ] );
    }

    public function register_venue_taxonomy() {
        register_taxonomy(
            Helpers::get_venue_taxonomy_name(),
            [ Helpers::get_events_post_type() ],
            [
                'labels'            => [
                    'name'          => __( 'Venues', 'kinola' ),
                    'singular_name' => __( 'Venue', 'kinola' ),
                    'menu_name'     => __( 'Venues', 'kinola' ),
                ],
                'hierarchical'      => true,
                'public'            => false,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'capabilities'      => [
                    'assign_terms' => 'manage_options',
                    'edit_terms'   => 'disabled',
                    'manage_terms' => 'manage_options',
                ],
            ]
        );
    }

    public function register_endpoints() {
        add_rewrite_endpoint( Helpers::get_checkout_url_slug(), EP_ROOT );

        if ( get_option( 'kinola_rewrite_endpoint_registered' ) !== 'yes' ) {
            flush_rewrite_rules();
            update_option( 'kinola_rewrite_endpoint_registered', 'yes' );
        }
    }

    public function register_shortcodes() {
        add_shortcode( 'kinola_events', [ $this, 'render_events_page' ] );
        add_shortcode( 'kinola_films', [ $this, 'render_films_page' ] );
        add_shortcode( 'kinola_film_screenings', [ $this, 'render_film_screenings' ] );
    }

    public function enqueue_scripts() {
        if ( apply_filters( 'kinola/assets/css', true ) ) {
            wp_enqueue_style( 'kinola', Helpers::get_assets_url( 'styles/kinola.css' ), [], KINOLA_VERSION );
        }

        if ( apply_filters( 'kinola/assets/select2', true ) ) {
            wp_enqueue_style( 'kinola-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], KINOLA_VERSION );
            wp_enqueue_script( 'kinola-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', [ 'jquery' ], KINOLA_VERSION );
        }

        if ( is_singular( Helpers::get_films_post_type() ) && apply_filters( 'kinola/assets/photoswipe', true ) ) {
            wp_enqueue_style( 'kinola-photoswipe', Helpers::get_assets_url( 'styles/photoswipe/photoswipe.css' ), [], KINOLA_VERSION );
        }

        wp_enqueue_script( 'kinola', Helpers::get_assets_url( 'scripts/kinola.js' ), [ 'jquery' ], KINOLA_VERSION );
        wp_localize_script( 'kinola', 'Kinola', [ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ] );
    }

    public function render_events_page( $atts ): string {
        $atts = shortcode_atts( [
            'show_dates' => 'upcoming',
        ], $atts );

        return ( new Events() )->get_rendered_events( $atts['show_dates'] );
    }

    public function render_films_page(): string {
        return ( new Films() )->get_rendered_films();
    }

    public function render_film_screenings( $atts ) {
        $atts = shortcode_atts( [
            'film'       => false,
            'show_dates' => 'upcoming',
        ], $atts );

        if ( ! $atts['film'] ) {
            return __( "This shortcode requires a 'film' parameter.", 'kinola' );
        }

        $film_post = get_post( $atts['film'] );

        if ( ! $film_post ) {
            return sprintf(
                __( "There is no film with ID %s.", 'kinola' ),
                $atts['film']
            );
        }

        $film        = new Film( $film_post );
        $single_film = new Single_Film( $film );
        $single_film->set_template( 'film_screenings' );

        return $single_film->get_rendered_content( $atts['show_dates'] );
    }

    public function override_checkout_template( $template ) {
        global $wp_query;

        if ( isset( $wp_query->query_vars[ Helpers::get_checkout_url_slug() ] ) ) {
            return View::get_template_path( 'checkout' );
        }

        return $template;
    }

    public function override_single_film_content( $content ) {
        global $post;

        if ( is_singular( Helpers::get_films_post_type() ) ) {
            return ( new Single_Film( new Film( $post ) ) )->get_rendered_content();
        }

        return $content;
    }

    public function translate_post_title( string $title, int $post_id = null ) {

        if ( ! $post_id ) {
            return $title;
        }

        $post = get_post( $post_id );

        if ( $post->post_type !== Helpers::get_films_post_type() ) {
            return $title;
        }

        $film = new Film( $post );

        $translated_title = $film->get_field( 'title' );

        if ( $translated_title ) {
            return $translated_title;
        }

        return $title;
    }
}
