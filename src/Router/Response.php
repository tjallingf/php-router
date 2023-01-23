<?php
    namespace Router;

    use Router\Lib;
    use Router\Models\ComponentModel;
    use Router\Models\ResponseCookieModel;
    use Router\Exceptions\ResponseException;
    use Router\Models\ViewModel;

    class Response extends Message {
        public array $headers   = [];
        public array $cookies   = [];
        public array $body      = [];
        public int $statusCode  = 200;
        protected bool $closed  = false;

        public function __construct() {
            parent::__construct();
        }

        public function send($data): self {
            if(is_array($data))
                return $this->sendJson($data);

            if($data instanceof ViewModel || $data instanceof ComponentModel)
                $data = $data->render();
            
            if($data instanceof \Exception)
                throw $data;

            array_push($this->body, $data);

            return $this;
        }
        
        public function sendJson($data, int $flags = 0): self {
            $this->send(json_encode($data, $flags));
            $this->sendHeader('content-type', 'application/json', true);

            return $this;
        }

        public function sendError(\Exception $e): void {
            $data = [
                'error'       => str_replace('"', '\'', $e->getMessage()),
                'status_code' => ($e instanceof ResponseException
                    ? $e->getStatusCode() : 500) ?? 500
            ];

            if(APP_MODE_DEV) {
                if($e->getFile()) $data['file']  = $e->getFile();
                if($e->getLine()) $data['line']  = $e->getLine();
                if(isset($route)) $data['route'] = $route->__toString();
            }

            $this
                ->clearBody()
                ->sendJson($data, JSON_UNESCAPED_SLASHES)
                ->sendStatusCode($data['status_code'])
                ->end();
        }
        
        public function sendStatusCode(int $status_code): self {
            if(is_null(static::getStatusMessage($status_code)))
                $this->sendError(new ResponseException("Status code '$status_code' does not exist", 500));

            $this->statusCode = $status_code;

            return $this;
        }

        public function getStatusMessage(?int $status_code = null): string|null {
            return @static::STATUS_CODES[$status_code];
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

        public function clearBody(): self {
            $this->body = [];
            
            return $this;
        }

        public function redirect(string $url, bool $ignore_app_base_url = false): void {
            $url = trim($url);
            $is_relative = !str_contains(substr($url, 0, 8), '://');

            if($is_relative && !$ignore_app_base_url)
                $url = Lib::joinPaths(Config::get('router.baseUrl'), $url);

            $this->sendHeader('location', $url, true)->sendStatusCode(302)->end();
        }
        
        public function end(): void {
            if($this->closed) return;

            $this->endStatusCode();    
            $this->endCookies();
            $this->endHeaders();
            $this->endBody();
            $this->close();

            $this->closed = true;
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

            return new (ResponseCookieModel::getOverride())($data);
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
            echo($this->getBody());
        }

        protected function endStatusCode(): void {
            http_response_code($this->statusCode ?? 200);
        }

        /**
         * Subclasses can use Response::close() to extend Response::end().
         */
        protected function close(): void {}

        protected const STATUS_CODES = [ 
            // 1xx: Informational
            100 => 'Continue', 
            101 => 'Switching Protocols',

            // 2xx: Successful
            200 => 'OK', 
            201 => 'Created', 
            202 => 'Accepted', 
            203 => 'Non-authoritative information', 
            204 => 'No content', 
            205 => 'Reset content', 
            206 => 'Partial content',

            // 3xx: Redirection
            300 => 'Multiple choices',
            301 => 'Moved permanently',
            302 => 'Found',
            303 => 'See ether',
            304 => 'Not modified',
            305 => 'Use oroxy',
            306 => '(Unused)',
            307 => 'Temporary redirect',

            // 4xx: Client Error
            400 => 'Bad request',
            401 => 'Unauthorized',
            402 => 'Payment required',
            403 => 'Forbidden',
            404 => 'Not found',
            405 => 'Method not allowed',
            406 => 'Not acceptable',
            407 => 'Proxy authentication required',
            408 => 'Request timeout',
            409 => 'Conflict',
            410 => 'Gone',

            // 5xx: Server Error
            500 => 'Internal server error',
            501 => 'Not implemented',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
            504 => 'Gateway timeout',
            505 => 'HTTP version not supported'
        ];
    }