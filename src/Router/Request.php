<?php
    namespace Router;
    
    use Router\Models\UrlModel;
    use Router\Models\RequestCookieModel;   
    use Router\Message;

    class Request extends Message {       
        protected const COOKIE_MODEL = RequestCookieModel::class;

        public string $method;
        public UrlModel $url;
        public array $params;
        public array $query;

        public function __construct(
            string $method, 
            UrlModel $url, 
            array $headers, 
            string $body,
            array $params
        ) {
            parent::__construct();

            $this->method = $method;
            $this->url = $url;

            $this->headers = $this->parseHeaders($headers);
            $this->cookies = $this->parseCookies($this->getHeaderLine('cookie'));
            $this->params = $params;
            $this->query = $this->parseQuery((string) $url);
            $this->body = $this->parseBody($body);

            $this->open();
        }

        /**
         * Get a specific url parameter by name.
         * @param string $name - The name of the url parameter to get.
         * @return string|null - The value of the query parameter, or null if it is not set.
         */
        public function getParam(string $name) {
            return @$this->params[trim($name)];
        }

        /**
         * Gets a specific query parameter by name.
         * @param string $name - The name of the query parameter to get.
         * @return any - The value of the query parameter, or null if it is not set.
         */
        public function getQuery(string $name) {
            return @$this->query[trim($name)];
        }

        protected function parseHeaders(array $headers): array {
            $headers = array_change_key_case($headers, CASE_LOWER);

            $parsed = [];

            foreach ($headers as $name => $value) {
                $parsed[$name] = [ $value ];
            }

            return $parsed;
        }

        protected function parseCookies(string $cookie_header): array {
            if(empty($cookie_header)) return [];
            $cookies = explode(';', $cookie_header);
            $parsed = [];

            foreach ($cookies as $cookie_string) {    
                $cookie = self::COOKIE_MODEL::fromString($cookie_string);
                $parsed[$cookie->getName()] = $cookie;     
            }

            return $parsed;
        }

        protected function parseBody(string $body): array {
            if(!isset($body) || empty($body)) return [];

            $content_type = strtok($this->getHeaderLine('content-type'), ';');

            $parsed = null;

            switch($content_type) {
                case 'application/json':
                    $parsed = self::parseJsonBody($body);
                    break;
                case 'application/x-www-form-urlencoded':
                    $parsed = self::parseFormBody($body);
                    break;
                default:
                    $parsed = self::parseJsonBody($body) ?? self::parseFormBody($body) ?? $body;
                    break;
            }

            return is_array($parsed) ? $parsed : [ $parsed ];
        }

        protected function parseJsonBody(string $body) {
            return @json_decode($body, true);
        }

        protected function parseFormBody(string $body) {
            @parse_str($body, $result);
            return $result;
        }

        protected function parseQuery(string $url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $result);
            return $result;
        }


        /**
         * Can be used to extend Request::init().
         */
        protected function open() {}
    }