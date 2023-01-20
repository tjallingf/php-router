<?php
    namespace Router\Helpers;

    use Router\Models\ComponentModel;
    use Router\Controllers\ViewController;
    use Router\Models\ViewModel;
    use Router\Lib;
    use Exception;

    class Response {
        const BODY_SEPERATOR = "\r\n";

        protected static array $headers = [];
        protected static array $body = [];
        protected static int $statusCode = 200;
        protected static bool $wasEnded = false;

        function send($data): self {
            if(is_array($data))
                return $this->json($data);

            if($data instanceof ViewModel || $data instanceof ComponentModel)
                $data = $data->render();
            
            if($data instanceof Exception)
                return $this->sendError($data);

            array_push(self::$body, $data);

            return $this;
        }

        function json($data): self {
            $this->send(json_encode($data));
            $this->header('content-type', 'application/json');

            return $this;
        }

        function status(int $status_code) {
            self::$statusCode = $status_code;

            return $this;
        }

        function getStatus() {
            return self::$statusCode;
        }

        function getBody(): string {
            return implode(self::BODY_SEPERATOR, self::$body);
        }

        function sendError($error, int $status_code = -1) {
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

            return $this->json(['error' => $message])
                ->status($status_code)
                ->end();
            
            // // Get the name of the error view
            // $view_name = Config::get('router.errorView');

            // // Respond with an error message if the error view can not be found
            // if(!ViewController::exists($view_name)) {
            //     return $this->json(['error' => $message])
            //                 ->status($status_code)
            //                 ->end();
            // }

            // // Get the error view
            // $view = ViewController::find($view_name, [
            //     'message'     => $message,
            //     'status_code' => $status_code
            // ]);

            // // Respond with the error view
            // return $this->send($view)
            //             ->status($status_code)
            //             ->end();
        }

        public function redirect(string $url, bool $ignore_app_base_url = false): self {
            $url = trim($url);
            $is_relative = !str_contains(substr($url, 0, 8), '://');

            if($is_relative && !$ignore_app_base_url)
                $url = Lib::joinPaths(Config::get('router.baseUrl'), $url);

            $this->header('location', $url, true);
            $this->status(302);
            
            return $this;
        }

        public function header(string $key, string $value, bool $replace = false): self {
            $key = strtolower(trim($key));
            
            if(!isset(self::$headers[$key]) || $replace)
                self::$headers[$key] = $value;

            return $this;
        }

        public function catchNotFound($data, string $message = 'Not found') {
            if(is_null($data)) {
                $this->sendError($message);
                return null;
            }

            return $data;
        }

        public function end(): self {
            if(self::$wasEnded) return $this;

            if(!headers_sent()) {
                // Disable the 'X-Powered-By: PHP' header
                header_remove('x-powered-by');

                // Send headers
                foreach (self::$headers as $key => $value) {
                    header("{$key}: {$value}", true);
                }
            }

            // Send status code
            http_response_code(self::$statusCode);

            // Send body
            echo($this->getBody());
            
            self::$wasEnded = true;

            return $this;
        }
    }