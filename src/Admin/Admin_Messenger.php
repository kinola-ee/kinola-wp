<?php

namespace Kinola\KinolaWp\Admin;

class Admin_Messenger {
	public const FILM_CREATED = 'film_created';
    public const EVENTS_IMPORTED = 'events_imported';

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
				default:
					break;
			}
		}
	}

	public function film_created() {
		?>
        <div class="notice notice-success is-dismissible">
            <p><?php
				_ex( 'Film data imported successfully!', 'Admin', 'kinola' ); ?></p>
        </div>
		<?php
	}

	public function events_created() {
		?>
        <div class="notice notice-success is-dismissible">
            <p><?php
				_ex( 'All future events imported successfully!', 'Admin', 'kinola' ); ?></p>
        </div>
		<?php
	}
}
