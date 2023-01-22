<?php
    namespace Router;

    use Router\Response;
    use Router\Models\UrlModel;
    use Router\Models\RouteModel;
    use Router\Controllers\RouteController;
    use Router\Models\MiddlewareModel;
    use Router\Helpers\Overridable;
    use Router\Exception;

    class Router extends Overridable {
        public static bool $closed = false;
        public static array $globalMiddlewares = [];
        public static Response $res;
        public static Request $req;

        public static function use(MiddlewareModel $middleware): string {
            if(static::$closed)
                throw new Exception("Trying to add middleware to ".static::class." when closed.", 500);

            array_push(static::$globalMiddlewares, $middleware);

            return static::class;
        }

        public static function handleRequest(
            string $method, 
            string $url_path, 
            ?array $headers = [], 
            ?string $body = ''
        ): void {
            static::$closed = true;
            static::$res = new (Response::getOverride());

            $url = new (UrlModel::getOverride())($url_path);
            $method = trim(strtolower($method));

            // Find route
            $found_route = (RouteController::getOverride())::find($method, $url);
            
            if($found_route) {
                static::handleRoute($found_route, $method, $url, $headers, $body);
                return;
            }
                
            // Throw 404 error if no route can be found.
            static::$res->throw('Route not found', 404);
        }
  
        public static function handleException(\Exception $e, ?RouteModel $route = null): void {
            echo('A');
            $data = [
                'error'       => str_replace('"', '\'', $e->getMessage()),
                'status_code' => ($e instanceof Exception
                    ? $e->getStatusCode() : 500) ?? 500
            ];

            if(APP_MODE_DEV) {
                if($e->getFile()) $data['file']  = $e->getFile();
                if($e->getLine()) $data['line']  = $e->getLine();
                if(isset($route)) $data['route'] = $route->__toString();
            }

            static::$res
                ->clearBody()
                ->sendJson($data, JSON_UNESCAPED_SLASHES)
                ->sendStatusCode($data['status_code']);
        }

        public static function errorHandler(int $level, string $message): void {
            $e = new \Exception($message, $level);

            if(APP_MODE_PROD) {
                static::handleException($e);
                return;
            }

            throw $e;
        }

        public static function exitHandler(): void {    
            static::$res->end();
        }
 
        protected static function handleRoute(
            RouteModel $route,
            string $method, 
            UrlModel $url, 
            array $headers, 
            string $body
        ): void {
            static::$req = new (Request::getOverride())(
                $method, 
                $url, 
                $headers, 
                $body, 
                $route->getParams($url)
            );

            set_error_handler([ static::class, 'errorHandler' ], E_ALL & ~E_WARNING);
            register_shutdown_function([ static::class, 'exitHandler' ]);

            try {
                $route->handle(static::$req, static::$res);
            } catch(\Exception $e) {
                static::handleException($e, $route);
            }
        }
    }