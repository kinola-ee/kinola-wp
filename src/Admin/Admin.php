<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\Router;
use Kinola\KinolaWp\Scheduler;
use Kinola\KinolaWp\View;

class Admin {

    public const IMPORT_FILM_ACTION     = 'kinola_import_film';
    public const IMPORT_EVENTS_ACTION   = 'kinola_import_events';
    public const IMPORT_PROGRAMS_ACTION = 'kinola_import_programs';
    public const MESSENGER_ACTION       = 'kinola_message';

    public function __construct() {
        add_action( 'init', [ $this, 'handle_actions' ] );
        add_action( 'admin_menu', [ $this, 'register_import_page' ] );
        add_action( 'add_meta_boxes_' . Helpers::get_films_post_type(), [ $this, 'register_edit_film_meta_box' ] );
        add_action( 'add_meta_boxes_' . Helpers::get_events_post_type(), [ $this, 'register_edit_event_meta_box' ] );
        add_action( 'add_meta_boxes_' . Helpers::get_programs_post_type(), [ $this, 'register_edit_program_meta_box' ] );
        add_action( 'admin_head-edit.php', [ $this, 'add_import_button' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_styles' ] );
        add_action( Scheduler::EVENT_NAME_15MIN, [ $this, 'import_events' ] );
        add_action( Scheduler::EVENT_NAME_15MIN, [ $this, 'import_changed_films' ] );
        add_action( Scheduler::EVENT_NAME_15MIN, [ $this, 'import_programs' ] );

        // Background import handlers
        add_action( 'kinola_import_single_film_async', [ $this, 'async_import_film' ] );
        add_action( 'kinola_import_events_async', [ $this, 'async_import_events' ] );
        add_action( 'kinola_import_programs_async', [ $this, 'async_import_programs' ] );
    }

    public function add_import_button() {
        global $current_screen;

        // Add import button for Events page
        if ( $current_screen->post_type === Helpers::get_events_post_type() ) {
            ?>
            <script>
                jQuery(function ($) {
                    $('.wrap h1.wp-heading-inline').after(
                        "<a class='page-title-action' href='<?php echo Router::get_action_url( [ self::IMPORT_EVENTS_ACTION => 1 ] ); ?>'>" +
                        "<?php _ex( 'Import from Kinola', 'Admin', 'kinola' ); ?>" +
                        "</a>"
                    );
                });
            </script>
            <?php
        }

        // Add import button for Programs page
        if ( $current_screen->post_type === Helpers::get_programs_post_type() ) {
            ?>
            <script>
                jQuery(function ($) {
                    $('.wrap h1.wp-heading-inline').after(
                        "<a class='page-title-action' href='<?php echo Router::get_action_url( [ self::IMPORT_PROGRAMS_ACTION => 1 ] ); ?>'>" +
                        "<?php _ex( 'Import from Kinola', 'Admin', 'kinola' ); ?>" +
                        "</a>"
                    );
                });
            </script>
            <?php
        }
    }

    public function register_admin_styles() {
        wp_enqueue_style(
            'kinola_admin_css',
            Helpers::get_assets_url( 'styles/admin.css' ),
            false,
            KINOLA_VERSION
        );
    }

    public function handle_actions() {
        if ( $this->should_run_action( self::MESSENGER_ACTION ) ) {
            ( new Admin_Messenger )->add_message( $_GET[ self::MESSENGER_ACTION ] );
        }

        if ( $this->should_run_action( self::IMPORT_FILM_ACTION ) ) {
            $remote_id = $_GET[ self::IMPORT_FILM_ACTION ];

            // Schedule background import
            wp_schedule_single_event( time(), 'kinola_import_single_film_async', array( $remote_id ) );
            spawn_cron(); // Trigger cron immediately

            // Check if film already exists to determine redirect
            $existing_film = Film::find_by_remote_id( $remote_id );

            if ( $existing_film ) {
                // Redirect to existing film edit page
                $url = get_edit_post_link( $existing_film->get_local_id(), 'redirect' );
                $url = Router::append_message( $url, Admin_Messenger::IMPORT_SCHEDULED );
            } else {
                // Redirect to films list since we don't have a post ID yet
                $url = admin_url( 'edit.php?post_type=' . Helpers::get_films_post_type() );
                $url = Router::append_message( $url, Admin_Messenger::IMPORT_SCHEDULED );
            }

            Router::redirect( $url );
        }

        if ( $this->should_run_action( self::IMPORT_EVENTS_ACTION ) ) {
            // Schedule background import
            wp_schedule_single_event( time(), 'kinola_import_events_async' );
            spawn_cron(); // Trigger cron immediately

            $url = admin_url( 'edit.php?post_type=' . Helpers::get_events_post_type() );
            $url = Router::append_message( $url, Admin_Messenger::IMPORT_SCHEDULED );
            Router::redirect( $url );
        }

        if ( $this->should_run_action( self::IMPORT_PROGRAMS_ACTION ) ) {
            // Schedule background import
            wp_schedule_single_event( time(), 'kinola_import_programs_async' );
            spawn_cron(); // Trigger cron immediately

            $url = admin_url( 'edit.php?post_type=' . Helpers::get_programs_post_type() );
            $url = Router::append_message( $url, Admin_Messenger::IMPORT_SCHEDULED );
            Router::redirect( $url );
        }
    }

    public function register_import_page() {
        add_submenu_page(
            'edit.php?post_type=' . Helpers::get_films_post_type(),
            _x( 'Import films', 'Admin', 'kinola' ),
            _x( 'Import films', 'Admin', 'kinola' ),
            'edit_posts',
            'import_films',
            [ $this, 'render_import_page' ]
        );
    }

    public function render_import_page() {
        $importer = new Film_Importer();
        $page     = new Film_Importer_List_Table( $importer );
        $page->prepare_items();
        $page->display();
    }

    public function import_film(): Film {
        $importer = new Film_Importer();

        return $importer->import_film( $_GET[ self::IMPORT_FILM_ACTION ] );
    }

    public function register_edit_film_meta_box() {
        add_meta_box(
            'edit_film_meta_box',
            _x( 'Film data', 'Admin', 'kinola' ),
            [ $this, 'render_edit_film_meta_box' ],
            Helpers::get_films_post_type(),
            'normal',
            'high',
        );
    }

    public function render_edit_film_meta_box() {
        $film = Film::find_by_local_id( $_GET['post'] );
        View::render( 'admin/edit-film-meta-box', [ 'film' => $film ] );
    }

    public function register_edit_event_meta_box() {
        add_meta_box(
            'edit_event_meta_box',
            _x( 'Event data', 'Admin', 'kinola' ),
            [ $this, 'render_edit_event_meta_box' ],
            Helpers::get_events_post_type(),
            'normal',
            'high',
        );
    }

    public function render_edit_event_meta_box() {
        $event = Event::find_by_local_id( $_GET['post'] );
        View::render( 'admin/edit-event-meta-box', [ 'event' => $event ] );
    }

    public function register_edit_program_meta_box() {
        add_meta_box(
            'edit_program_meta_box',
            _x( 'Program data', 'Admin', 'kinola' ),
            [ $this, 'render_edit_program_meta_box' ],
            Helpers::get_programs_post_type(),
            'normal',
            'high',
        );
    }

    public function render_edit_program_meta_box() {
        $program = \Kinola\KinolaWp\Program::find_by_local_id( $_GET['post'] );
        View::render( 'admin/edit-program-meta-box', [ 'program' => $program ] );
    }

    public function import_events() {
        $importer = new Event_Importer();
        $importer->import();
    }

    public function import_changed_films() {
        $importer = new Film_Importer();
        $importer->import_films( date( 'Y-m-d\TH:i:s\Z', strtotime( '-2 days' ) ) );
    }

    public function import_programs() {
        $importer = new Program_Importer();
        $importer->import_programs();
    }

    /**
     * Background film import handler (called by cron).
     *
     * @param string $remote_id Film ID from Kinola API
     */
    public function async_import_film( $remote_id ) {
        debug_log( "Async film import: Starting import for film ID {$remote_id}" );

        try {
            $importer = new Film_Importer();
            $film     = $importer->import_film( $remote_id );

            if ( $film ) {
                debug_log( "Async film import: Successfully imported film ID {$remote_id} to post #{$film->get_local_id()}" );
            } else {
                debug_log( "Async film import: Film ID {$remote_id} import returned null (may not be public)" );
            }
        } catch ( \Exception $e ) {
            debug_log( "Async film import: ERROR importing film ID {$remote_id} - " . $e->getMessage() );
        }
    }

    /**
     * Background events import handler (called by cron).
     */
    public function async_import_events() {
        debug_log( "Async events import: Starting background import" );

        try {
            $this->import_events();
            debug_log( "Async events import: Successfully completed" );
        } catch ( \Exception $e ) {
            debug_log( "Async events import: ERROR - " . $e->getMessage() );
        }
    }

    /**
     * Background programs import handler (called by cron).
     */
    public function async_import_programs() {
        debug_log( "Async programs import: Starting background import" );

        try {
            $this->import_programs();
            debug_log( "Async programs import: Successfully completed" );
        } catch ( \Exception $e ) {
            debug_log( "Async programs import: ERROR - " . $e->getMessage() );
        }
    }

    protected function should_run_action( string $action ): bool {
        return is_admin() &&
               isset( $_GET[ $action ] ) &&
               $_GET[ $action ];
    }
}
