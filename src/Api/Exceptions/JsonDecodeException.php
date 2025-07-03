<?php

namespace Kinola\KinolaWp\Api\Exceptions;

class JsonDecodeException extends ApiException {
    public function __construct( $message = "Failed to decode JSON response", $code = 0, \Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }
}