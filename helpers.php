<?php

if ( ! function_exists( 'dd' ) ) {
    function dd( $var ) {
        var_dump( $var );
        die;
    }
}

if ( ! function_exists( 'debug_log' ) ) {
    function debug_log( $log ) {
        if (!defined('KINOLA_DEBUG_LOG') || !KINOLA_DEBUG_LOG) {
            return;
        }

        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}
