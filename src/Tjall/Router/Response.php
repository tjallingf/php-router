<?php
    namespace Tjall\Router;

    use Tjall\Router\Config;
    use Tjall\Router\Request;

    class Response {
        protected array $headers = [];
        protected string $body = '';
        protected int $status = 200;
        protected Request $request;

        function __construct(Request $request) {
            $this->request = $request;
        }

        function send($data) {
            // Append data to current body if it is of a primitive type.
            if(is_scalar($data)) {
                $this->body .= strval($data);
                return $this;
            }

            // If data is view, overwrite body
            if($data instanceof View) {
                $this->body = $data->render();
                return $this;
            }

            // Convert data to JSON
            $this->json($data);
            return $this;
        }

        function status(int $status) {
            $this->status = $status;
        }

        function redirect(string $url, ?int $status = null, ?bool $do_prepend_base_path = true) {
            // Check if the url starts with a protocol (e.g., //, https://, http://)
            $is_outward = (strpos($url, '//') <= 6);

            // Prepend the basepath if the url is not outward
            if($do_prepend_base_path && !$is_outward)
                $url = '/'.rtrim(Config::get('basePath'), '/').ltrim($url, '/');

            $this->status($status || static::STATUS_TEMPORARY_REDIRECT);
            $this->header('Location', $url);

            return $this;
        }
        
        function header(string $field, array|string $value): self {
            $field = strtolower($field);
            
            $this->headers[$field] = $this->headers[$field] ?? [];
            array_push($this->headers[$field], $value);

            return $this;
        }

        function headers(object $headers): self {
            foreach ($headers as $field => $value) {
                $this->header($field, $value);
            }

            return $this;
        }

        function json($json, int $flags = 0, int $depth = 512): self {
            $this->header('Content-Type', 'application/json');
            $this->send(json_encode($json, $flags, $depth));

            return $this;
        }

        function end(): void {
            $this->endStatus();
            $this->endHeaders();
            $this->endBody();
        }

        protected function endStatus(): void {
            http_response_code($this->status);
        }

        protected function endHeaders(): void {
            foreach ($this->headers as $field => $values) {
                foreach ($values as $value) {
                    header("$field: $value", true);
                }
            }
        }

        protected function endBody(): void {
            if($this->request->method === 'HEAD') 
                return;

            echo($this->body);
        }

        const STATUS_CONTINUE = 100;
        const STATUS_SWITCHING_PROTOCOLS = 101;
        const STATUS_OK = 200;
        const STATUS_CREATED = 201;
        const STATUS_ACCEPTED = 202;
        const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;
        const STATUS_NO_CONTENT = 204;
        const STATUS_RESET_CONTENT = 205;
        const STATUS_PARTIAL_CONTENT = 206;
        const STATUS_MULTIPLE_CHOICES = 300;
        const STATUS_MOVED_PERMANENTLY = 301;
        const STATUS_FOUND = 302;
        const STATUS_SEE_OTHER = 303;
        const STATUS_NOT_MODIFIED = 304;
        const STATUS_USE_PROXY = 305;
        const STATUS_TEMPORARY_REDIRECT = 307;
        const STATUS_PERMANENT_REDIRECT = 308;
        const STATUS_BAD_REQUEST = 400;
        const STATUS_UNAUTHORIZED = 401;
        const STATUS_PAYMENT_REQUIRED = 402;
        const STATUS_FORBIDDEN = 403;
        const STATUS_NOT_FOUND = 404;
        const STATUS_METHOD_NOT_ALLOWED = 405;
        const STATUS_NOT_ACCEPTABLE = 406;
        const STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;
        const STATUS_REQUEST_TIMEOUT = 408;
        const STATUS_CONFLICT = 409;
        const STATUS_GONE = 410;
        const STATUS_LENGTH_REQUIRED = 411;
        const STATUS_PRECONDITION_FAILED = 412;
        const STATUS_CONTENT_TOO_LARGE = 413;
        const STATUS_URI_TOO_LONG = 414;
        const STATUS_UNSUPPORTED_MEDIA_TYPE = 415;
        const STATUS_RANGE_NOT_SATISFIABLE = 416;
        const STATUS_EXPECTATION_FAILED = 417;
        const STATUS_MISDIRECTED_REQUEST = 421;
        const STATUS_UNPROCESSABLE_CONTENT = 422;
        const STATUS_UPGRADE_REQUIRED = 426;
        const STATUS_INTERNAL_SERVER_ERROR = 500;
        const STATUS_NOT_IMPLEMENTED = 501;
        const STATUS_BAD_GATEWAY = 502;
        const STATUS_SERVICE_UNAVAILABLE = 503;
        const STATUS_GATEWAY_TIMEOUT = 504;
        const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
    }