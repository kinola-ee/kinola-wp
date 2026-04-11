<?php

namespace Kinola\KinolaWp\Admin;

class Admin_Messenger {
    public const FILM_CREATED      = 'film_created';
    public const EVENTS_IMPORTED   = 'events_imported';
    public const PROGRAMS_IMPORTED = 'programs_imported';
    public const IMPORT_SCHEDULED  = 'import_scheduled';

    protected array $messages = [];

    public function __construct() {
        add_action( 'admin_notices', [ $this, 'render_messages' ] );
    }

    public function add_message( string $message ) {
        $this->messages[] = $message;
    }

    public function render_messages() {
        foreach ( $this->messages as $message ) {
            switch ( $message ) {
                case self::FILM_CREATED:
                    $this->film_created();
                    break;
                case self::EVENTS_IMPORTED:
                    $this->events_created();
                    break;
                case self::PROGRAMS_IMPORTED:
                    $this->programs_imported();
                    break;
                case self::IMPORT_SCHEDULED:
                    $this->import_scheduled();
                    break;
                default:
                    $this->message( $message );
                    break;
            }
        }
    }

    public function film_created() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                Kinola: <?php _ex( 'Film data imported successfully!', 'Admin', 'kinola' ); ?>
            </p>
        </div>
        <?php
    }

    public function events_created() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                Kinola: <?php _ex( 'All future events imported successfully!', 'Admin', 'kinola' ); ?>
            </p>
        </div>
        <?php
    }

    public function programs_imported() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                Kinola: <?php _ex( 'All programs imported successfully!', 'Admin', 'kinola' ); ?>
            </p>
        </div>
        <?php
    }

    public function import_scheduled() {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                Kinola: <?php _ex( 'Import started in background. This may take a few minutes. Refresh to see the results.', 'Admin', 'kinola' ); ?>
            </p>
        </div>
        <?php
    }

    public function message( string $message ) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                Kinola: <?php echo $message; ?>
            </p>
        </div>
        <?php
    }
}
