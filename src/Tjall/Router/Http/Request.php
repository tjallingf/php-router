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
            $this->method = $this->readRequestMethod();
            $this->params = $this->readParams($params);
            $this->files = $this->readFiles();
            $this->body = $this->readBody();
            $this->query = $this->readQuery();
            $this->cookies = $this->readCookies();
        }

        public function input(string $name, $fallback = null) {
            if(!isset($this->body[$name])) return $fallback;  
            return $this->body[$name];
        }

        public function query(string $name, $fallback = null) {
            if(!isset($this->query[$name])) return $fallback;  
            return $this->query[$name];
        }

        protected function readQuery(): array {
            return $_GET;
        }

        protected function readCookies(): array {
            return $_COOKIE;
        }

        protected function readRequestMethod(): string {
            return trim(strtoupper($_SERVER['REQUEST_METHOD']));
        }

        protected function readParams(array $params): array {
            return $params;
        }

        protected function readFiles() {
            return array_map(function($file) {
                return new UploadedFile($file);
            }, $_FILES);
        }

        protected function readBody(): array {
            if(count($_POST)) 
                return $_POST;
                
            return (array) @json_decode(file_get_contents('php://input'), true);
        }
    }