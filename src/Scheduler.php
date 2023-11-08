<?php

namespace Kinola\KinolaWp;

class Scheduler {
    public const EVENT_NAME_15MIN = 'kinola_15min';
    public const EVENT_NAME_DAILY = 'kinola_daily';

    public function __construct() {
        add_filter( 'cron_schedules', [ $this, 'register_15min_schedule' ] );
    }

    public function schedule_events() {
        if ( ! wp_next_scheduled( self::EVENT_NAME_15MIN ) ) {
            wp_schedule_event( time(), '15min', self::EVENT_NAME_15MIN );
        }

        if ( ! wp_next_scheduled( self::EVENT_NAME_DAILY ) ) {
            wp_schedule_event( time(), 'daily', self::EVENT_NAME_DAILY );
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
