<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Event;
use Kinola\KinolaWp\Film;
use Kinola\KinolaWp\Helpers;
use Kinola\KinolaWp\Router;
use Kinola\KinolaWp\Scheduler;
use Kinola\KinolaWp\View;

class Admin {

    public const IMPORT_FILM_ACTION   = 'kinola_import_film';
    public const IMPORT_EVENTS_ACTION = 'kinola_import_events';
    public const MESSENGER_ACTION     = 'kinola_message';

    public function __construct() {
        add_action( 'init', [ $this, 'handle_actions' ] );
        add_action( 'admin_menu', [ $this, 'register_import_page' ] );
        add_action( 'add_meta_boxes_' . Helpers::get_films_post_type(), [ $this, 'register_edit_film_meta_box' ] );
        add_action( 'add_meta_boxes_' . Helpers::get_events_post_type(), [ $this, 'register_edit_event_meta_box' ] );
        add_action( 'admin_head-edit.php', [ $this, 'add_import_button' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_styles' ] );
        add_action( Scheduler::EVENT_NAME_15MIN, [ $this, 'import_events' ] );
        add_action( Scheduler::EVENT_NAME_15MIN, [ $this, 'import_changed_films' ] );
    }

    public function add_import_button() {
        global $current_screen;

        if ( $current_screen->post_type !== Helpers::get_events_post_type() ) {
            return;
        }

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
            $imported_film = $this->import_film();
            $url           = get_edit_post_link( $imported_film->get_local_id(), 'redirect' );
            $url           = Router::append_message( $url, Admin_Messenger::FILM_CREATED );
            Router::redirect( $url );
        }

        if ( $this->should_run_action( self::IMPORT_EVENTS_ACTION ) ) {
            $this->import_events();
            $url = admin_url( 'edit.php?post_type=' . Helpers::get_events_post_type() );
            $url = Router::append_message( $url, Admin_Messenger::EVENTS_IMPORTED );
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

    public function import_events() {
        $importer = new Event_Importer();
        $importer->import();
    }

    public function import_changed_films() {
        $importer = new Film_Importer();
        $importer->import_films( date( 'Y-m-d\TH:i:s\Z', strtotime( '-2 days' ) ) );
    }

    protected function should_run_action( string $action ): bool {
        return is_admin() &&
               isset( $_GET[ $action ] ) &&
               $_GET[ $action ];
    }
}
