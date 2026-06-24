<?php

namespace Kinola\KinolaWp\Admin;

class Admin_Messenger {
    public const IMPORT_SCHEDULED  = 'import_scheduled';

    /**
     * Status codes that may arrive via the kinola_message URL param. The param carries a known code,
     * not free text, so anything not in this list is ignored — a crafted link can't surface arbitrary
     * content in an admin notice.
     */
    public const URL_MESSAGES = [ self::IMPORT_SCHEDULED ];

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
                case self::IMPORT_SCHEDULED:
                    $this->import_scheduled();
                    break;
                default:
                    $this->message( $message );
                    break;
            }
        }
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
                Kinola: <?php echo esc_html( $message ); ?>
            </p>
        </div>
        <?php
    }
}
