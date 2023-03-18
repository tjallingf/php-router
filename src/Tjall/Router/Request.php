<?php
    namespace Tjall\Router;

    use Tjall\Router\UploadedFile;

    class Request {
        public array|string|null $body;
        public array $files;
        public string $method;
        public array $params;
        public array $query;

        public function __construct(array $params) {
            $this->method = $this->getRequestMethod();
            $this->params = $this->getParams($params);
            $this->files = $this->getFiles();
            $this->body = $this->getBody();
            $this->query = $this->getQuery();
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

        protected function getBody(): array|string|null {
            if(count($_POST)) return $_POST;

            $raw = file_get_contents('php://input');
            if(empty($raw)) return null;

            return $raw;
        }
    }