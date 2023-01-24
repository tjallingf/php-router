<?php
    namespace Router\Exceptions;

    use Router\Response;

    class ResponseException extends \Exception {
        protected ?int $statusCode = null;

        public function __construct(?string $message = null, ?int $status_code = null) {
            if(is_null(Response::getDefaultStatusMessage($status_code))) {
                $status_code = 500;
            }

            $this->statusCode = $status_code;
            $this->message = $message ?? Response::getDefaultStatusMessage($status_code);
        }

        public function getStatusCode(): int|null {
            return $this->statusCode;
        }
    }