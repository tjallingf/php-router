<?php
    namespace Router;

    use Router\Lib;
    use Router\Models\ComponentModel;
    use Router\Models\ResponseCookieModel;
    use Router\Models\ViewModel;
    use Router\Mesage;
    use Exception;

    class Response extends Message {
        protected const COOKIE_MODEL = ResponseCookieModel::class;

        public array $headers   = [];
        public array $cookies   = [];
        public array $body      = [];
        public ?int $statusCode = null;
        public bool $closed     = false;

        public function __construct() {
            parent::__construct();
        }

        public function send($data): self {
            if(is_array($data))
                return $this->sendJson($data);

            if($data instanceof ViewModel || $data instanceof ComponentModel)
                $data = $data->render();
            
            if($data instanceof Exception)
                return $this->sendError($data);

            array_push($this->body, $data);

            return $this;
        }
        
        public function sendJson($data): self {
            $this->send(json_encode($data));
            $this->sendHeader('content-type', 'application/json', true);

            return $this;
        }

        function sendError($error, int $status_code = -1): self {
            if($error instanceof Exception) {
                $message = $error->getMessage();
                $status_code = $status_code === -1 && $error->getCode() > -1
                    ? $error->getCode() 
                    : $status_code;
            } else {
                $message = $error;
            }

            if($status_code === -1) 
                $status_code = 500;

            $this->sendJson(['error' => $message])->sendStatusCode($status_code)->end();

            return $this;
        }

        public function sendStatusCode(int $status_code): self {
            $this->statusCode = $status_code;

            return $this;
        }

        public function sendHeader(string $name, string $value = '', bool $replace = false): self {
            $name = strtolower(trim($name));
            
            if(!isset($this->headers[$name]) || $replace) {
                $this->headers[$name] = [ $value ];
            } else {
                array_push($this->headers[$name], $value);
            }

            return $this;
        }

        public function redirect(string $url, bool $ignore_app_base_url = false): self {
            $url = trim($url);
            $is_relative = !str_contains(substr($url, 0, 8), '://');

            if($is_relative && !$ignore_app_base_url)
                $url = Lib::joinPaths(Config::get('router.baseUrl'), $url);

            $this->sendHeader('location', $url, true)->sendStatusCode(302)->end();

            return $this;
        }
        
        public function end(): self {
            if($this->closed) return $this;
            $this->closed = true;

            if(!headers_sent()) {
                $this->endStatusCode();    
                $this->endCookies();
                $this->endHeaders();
                $this->endBody();
                $this->close();
            }

            return $this;
        }

        public function createCookie(            
            string $name, 
            $value = null, 
            ?int $expires = null, 
            ?string $domain = null,
            ?string $path = null,
            ?bool $secure = null,
            ?bool $httponly = null
        ): ResponseCookieModel {
            $data = [
                'name'     => $name,
                'value'    => $value,
                'expires'  => $expires,
                'domain'   => $domain,
                'path'     => $path,
                'secure'   => $secure,
                'httponly' => $httponly
            ];

            return new (self::COOKIE_MODEL)($data);
        }

        public function sendCookie(ResponseCookieModel $cookie): self {               
            $this->cookies[$cookie->getName()] = $cookie;
            return $this;
        }

        protected function endCookies(): void {
            foreach ($this->cookies as &$cookie) {
                $this->sendHeader('set-cookie', $cookie->__toString());
            }
        }

        protected function endHeaders(): void {
            // Remove the 'X-Powered-By: PHP' header
            header_remove('x-powered-by');

            // Send headers
            foreach ($this->headers as $name => $values) {
                $capitalized_name = ucwords($name, '-');
                foreach ($values as $value) {
                    header("{$capitalized_name}: {$value}", false);
                }
            }
        }
        protected function endBody(): void {
            echo($this->getBodyAsString());
        }

        protected function endStatusCode(): void {
            http_response_code($this->statusCode ?? 200);
        }

        /**
         * Can be used to extend Response::end().
         */
        protected function close() {}
    }