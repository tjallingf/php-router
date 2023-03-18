<?php
    namespace Tjall\Router\Http;

    use Tjall\Router\Http\UploadedFile;
    use stdClass;

    class Request {
        /* Available for middleware */
        public $user;
        public stdClass $data;

        public array $body;
        public array $files;
        public string $method;
        public array $params;
        public array $query;
        public array $cookies;

        public function __construct(array $params) {
            $this->method = $this->getRequestMethod();
            $this->params = $this->getParams($params);
            $this->files = $this->getFiles();
            $this->body = $this->getBody();
            $this->query = $this->getQuery();
            $this->cookies = $this->getCookies();
        }

        public function input(string $name, $fallback = null) {
            if(!isset($this->body[$name])) return $fallback;  
            return $this->body[$name];
        }

        public function query(string $name, $fallback = null) {
            if(!isset($this->query[$name])) return $fallback;  
            return $this->query[$name];
        }

        protected function getQuery(): array {
            return $_GET;
        }

        protected function getCookies(): array {
            return $_COOKIE;
        }

        protected function getRequestMethod(): string {
            return trim(strtoupper($_SERVER['REQUEST_METHOD']));
        }

        protected function getParams(array $params): array {
            return $params;
        }

        protected function getFiles() {
            return array_map(function($file) {
                return new UploadedFile($file);
            }, $_FILES);
        }

        protected function getBody(): array {
            if(count($_POST)) 
                return $_POST;
                
            return (array) @json_decode(file_get_contents('php://input'), true);
        }
    }