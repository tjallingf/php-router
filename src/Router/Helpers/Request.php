<?php
    namespace Router\Helpers;

    use Router\Models\UrlModel;
    use Router\Models\RouteModel;

    class Request {
        protected RouteModel $route;
        protected UrlModel $url;

        /**
         * The body that was sent with the request, as parsed with Request::parseBody().
         */
        public $body;

        /**
         * The url parameters.
         */
        public array $params;

        /**
         * The query parameters that were sent with the request.
         */
        public array $query;

        /**
         * The headers that were sent with the request.
         */
        public array $headers;

        /** 
         * The method that was used for the request, in lowercase (get, post, put, etc.).
         */
        public string $method;

        public function __construct(RouteModel $route, string $method, UrlModel $url) {
            $this->route = $route;
            $this->method = $method;
            $this->url = $url;

            $this->headers = array_change_key_case(getallheaders() ?? [], CASE_LOWER);
            $this->params = $this->route->resolveUrlParameters($url);
            $this->query = $_GET;
            $this->body = $this->parseBody(file_get_contents('php://input'));
        }

        /**
         * Gets a specific url parameter by name.
         * @param string name - The name of the url parameter to get.
         * @return string|null - The value of the query parameter, or null if it is not set.
         */
        public function getParam(string $name) {
            return @$this->params[trim($name)];
        }

        /**
         * Gets a specific query parameter by name.
         * @param string name - The name of the query parameter to get.
         * @return any - The value of the query parameter, or null if it is not set.
         */
        public function getQuery(string $name) {
            return @$this->query[trim($name)];
        }

        /**
         * Gets a specific request header by name.
         * @param string name - The name of the request header to get.
         * @return string|null - The value of the request header, or null if it is not set.
         */
        public function getHeader(string $name): string|null {
            $value = @$this->headers[trim(strtolower($name))];
            return is_null($value) ? null : trim($value);
        }

        /**
         * Attempts to parse the request body.
         * @param string name - The name of the query parameter to get.
         * @return any - The value of the request body, or null if it is not set.
         */
        protected function parseBody(string $body) {
            if(empty($body)) return null;

            $content_type = strtok($this->getHeader('content-type'), ';');

            switch($content_type) {
                case 'application/json':
                    return @json_decode($body, true);
                case 'application/x-www-form-urlencoded':
                    @parse_str($body, $result);
                    return $result;
                default:
                    $json_decoded_body = @json_decode($body, true);
                    if(json_last_error() == JSON_ERROR_NONE)
                        return $json_decoded_body;

                    @parse_str($body, $result);
                    if(!is_null($result))
                        return $result;

                    return null;
            }
        }
    }