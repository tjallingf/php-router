<?php
    namespace Router;
    
    use Router\Message;
    use Router\Models\UrlPathModel;
    use Router\Models\RequestCookieModel;
    use Router\Models\RouteModel;
    use stdClass;

    class Request extends Message {       
        public string $method;
        public stdClass $data;
        public UrlPathModel $url;
        public UrlPathModel $relativeUrl;
        public array $params;
        public array $query;
        public ?RouteModel $route;

        public function __construct(
            string $method, 
            string $url, 
            array $headers, 
            string $body,
            RouteModel $route
        ) {
            parent::__construct();

            $this->method = $method;
            $this->url = new UrlPathModel($url);
            $this->relativeUrl = new UrlPathModel(trim(substr(trim($url, '/'), strlen(Config::get('router.baseUrl'))), '/'));
            $this->route = $route;

            $this->headers = $this->parseHeaders($headers);
            $this->cookies = $this->parseCookies($this->getHeaderLine('cookie'));
            $this->params = $this->route->getParams($this->url);
            $this->query = $this->parseQuery((string) $url);
            $this->body = $this->parseBody($body);
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
         * @return string|null - The value of the query parameter, or null if it is not set.
         */
        public function getQuery(string $name): string|null {
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
                $cookie = RequestCookieModel::getOverride()::fromString($cookie_string);
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
                    $parsed = static::parseJsonBody($body);
                    break;
                case 'application/x-www-form-urlencoded':
                    $parsed = static::parseFormBody($body);
                    break;
                default:
                    $parsed = static::parseJsonBody($body) ?? static::parseFormBody($body) ?? $body;
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

        protected function parseQuery(string $url): array {
            parse_str(parse_url($url, PHP_URL_QUERY), $result);
            return $result;
        }

        /**
         * Subclasses can use Request::open() to extend Request::__construct().
         */
        protected function open(): void {}
    }