<?php
    namespace Tjall\Router;

    class RouteException extends \Exception {
        public int $status;

        public function __construct(string $message, int $status = 500) {
            $this->message = $message;
            $this->status = $status;
        }
    }