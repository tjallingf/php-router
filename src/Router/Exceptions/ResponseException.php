<?php
    namespace Router\Exceptions;

    class ResponseException extends \Exception {
        protected ?int $statusCode = null;

        public function __construct(string $message, ?int $status_code = null) {
            $this->message = $message;
            $this->statusCode = $status_code;
        }

        public function getStatusCode(): int|null {
            return $this->statusCode;
        }
    }