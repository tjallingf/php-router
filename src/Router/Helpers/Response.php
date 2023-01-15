<?php
    namespace Router\Helpers;

    use Router\Models\ComponentModel;
    use Router\Models\ViewModel;

    class Response {
        const BODY_SEPERATOR = "\r\n";

        protected array $headers = [];
        protected array $body = [];
        protected int $statusCode = 200;

        function send($data): string {
            if(is_array($data))
                return $this->json($data);
            
            if($data instanceof ViewModel || $data instanceof ComponentModel)
                $data = $data->render();

            array_push($this->body, $data);

            return __CLASS__;
        }

        function json($data): string {
            $this->send(json_encode($data));
            $this->header('content-type', 'application/json');

            return __CLASS__;
        }

        function status(int $status_code) {
            $this->statusCode = $status_code;

            return __CLASS__;
        }

        function sendError($error, int $status_code = null) {
            if($error instanceof \Exception) {
                $message = $error->getMessage();
                $status_code = !isset($status_code) && $error->getCode() > 0 
                    ? $error->getCode() 
                    : $status_code;
            } else {
                $message = $error;
            }

            // Use status code 500 (Server Error) by default
            $status_code = $status_code ?? 500;

            if($status_code) $this->status($status_code);
            $this->send(['error' => $message]);
            
            $this->end();

            return __CLASS__;
        }

        public function header(string $key, string $value, bool $replace = false): string {
            $key = strtolower(trim($key));
            
            if(!isset($this->headers[$key]) || $replace)
                $this->headers[$key] = $value;

            return __CLASS__;
        }

        public function end(): string {
            // Send headers
            foreach ($this->headers as $key => $value) {
                header("{$key}: {$value}", true);
            }

            // Send status code
            http_response_code($this->statusCode);

            // Send body
            echo(implode(self::BODY_SEPERATOR, $this->body));

            return __CLASS__;
        }
    }