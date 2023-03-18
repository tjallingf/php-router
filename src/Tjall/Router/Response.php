<?php
    namespace Tjall\Router;

    use Tjall\Router\Config;
    use Tjall\Router\Request;
    use Tjall\Router\Http\Status;

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

            // Overwrite the body if the data is a view
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
            // Check if the url has a protocol suffix
            $protocol_suffix_pos = strpos($url, '://');
            $is_outward = ($protocol_suffix_pos !== false && $protocol_suffix_pos < 5);
            $is_relative = (strpos($url, '/') === false);

            // Prepend the basepath if the url is absolute and outward
            if($do_prepend_base_path && !$is_outward && !$is_relative)
                $url = '/'.trim(Config::get('routes.basePath'), '/').'/'.ltrim($url, '/');

            $this->status($status || Status::TEMPORARY_REDIRECT);
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
    }