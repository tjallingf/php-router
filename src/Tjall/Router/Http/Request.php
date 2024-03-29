<?php
    namespace Tjall\Router\Http;

    use Tjall\Router\Http\UploadedFile;
    use stdClass;

    class Request {
        /* Can be set middleware */
        public $user;
        public stdClass $data;

        readonly public array $body;
        readonly public array $files;
        readonly public string $method;
        readonly public array $params;
        readonly public array $query;
        readonly public array $cookies;

        public function __construct(array $params) {
            $this->data = new stdClass();
            $this->method = $this->readRequestMethod();
            $this->params = $params;
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