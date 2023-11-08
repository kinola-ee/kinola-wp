<?php

namespace Kinola\KinolaWp;

class Scheduler {
    public const EVENT_NAME = 'kinola_15min';

    public function __construct() {
        add_filter( 'cron_schedules', [ $this, 'register_15min_schedule' ] );
    }

    public function schedule_events() {
        if ( ! wp_next_scheduled( self::EVENT_NAME ) ) {
            wp_schedule_event( time(), '15min', self::EVENT_NAME );
        }
    }

    public function register_15min_schedule( array $schedules ): array {
        $schedules['15min'] = [
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display'  => _x( 'Every 15 minutes', 'Admin', 'kinola' ),
        ];

        return $schedules;
    }
}
