<?php
    namespace Router\Helpers;

    use Router\Helpers\Config;
    use Router\Models\UrlModel;
    use Router\Controllers\MiddlewareController;
    use Router\Controllers\LocaleController;

    class Request {
        protected array $listener;
        protected UrlModel $url;
        public array $body = [];
        public array $params = [];
        public string $method;
        public array $user;

        public function init(array $listener, UrlModel $url, string $method) {
            $this->listener = $listener;
            $this->url = $url;

            $this->body = $_POST ?? $this->parseBody(file_get_contents('php://input'));
            $this->params = $this->url->parseParameters($this->listener['template']);
            $this->method = $method;

            if(Config::get('controllers.user.enabled'))
                $this->user = @MiddlewareController::find('User')::find($_SESSION['user_username'] ?? '') 
                    ?? @MiddlewareController::find('User')::find('__GUEST__') 
                    ?? [];

            if(Config::get('controllers.locale.enabled'))
                LocaleController::select(@$this->user['settings']['locale'] ?? '');

            return __CLASS__;
        }

        public function getParams() {
            return $this->params;
        }

        public function getParam(string $key) {
            return @$this->getParams()[$key];
        }

        public function getMethod() {
            return $this->method;
        }

        public function getBody() {
            return $this->body;
        }

        public function getUser() {
            return $this->user;
        }

        public function getQueryParams() {
            return $_GET;
        }

        public function getQueryParam(string $key) {
            return @$_GET[$key];
        }

        protected function parseBody(string $body): array {
            // Return empty array if the body is empty
            if(empty($body)) return [];

            // Try to use json_decode()
            $data = @json_decode($body, true);
            if($data) return $data;
            
            // Try to use parse_str()
            @parse_str($body, $data);
            if($data) return $data;

            return [];
        }
    }