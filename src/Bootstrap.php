<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Shortcodes\Events;
use Kinola\KinolaWp\Shortcodes\Films;

class Bootstrap {
	public function __construct() {
		add_action( 'init', [ $this, 'register_films_post_type' ], 1 );
		add_action( 'init', [ $this, 'register_events_post_type' ], 1 );
		add_action( 'init', [ $this, 'register_endpoints' ] );
		add_action( 'init', [ $this, 'register_shortcodes' ] );
		add_action( 'template_include', [ $this, 'override_checkout_template' ] );
		// add_filter( 'single_template', [ $this, 'override_single_film_template' ] );

		$scheduler = new Scheduler();
		$scheduler->schedule_events();
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
			'can_export'          => true,
			'has_archive'         => true,
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
				'revisions',
			] ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-megaphone',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
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
	}

	public function render_events_page(): string {
		return ( new Events() )->get_rendered_events();
	}

	public function render_films_page(): string {
		return ( new Films() )->get_rendered_films();
	}

	public function override_checkout_template( $template ) {
		global $wp_query;

		if ( isset( $wp_query->query_vars[ Helpers::get_checkout_url_slug() ] ) ) {
			return View::get_template_path( 'checkout' );
		}

		return $template;
	}

	public function override_single_film_template( $template ) {
		global $post;

		if ( $post->post_type === Helpers::get_films_post_type() ) {
			return View::get_template_path( 'film' );
		}

		return $template;
	}
}