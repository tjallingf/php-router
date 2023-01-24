<?php
    namespace Router;

    use Router\Response;
    use Router\Models\UrlPathModel;
    use Router\Models\RouteModel;
    use Router\Controllers\RouteController;
    use Router\Models\MiddlewareModel;
    use Router\Helpers\Overridable;
    use Router\Exceptions\ResponseException;

    class Router extends Overridable {
        public static bool $closed = false;
        public static array $globalMiddlewares = [];
        public static Response $res;
        public static Request $req;

        public static function use(MiddlewareModel $middleware): string {
            if(static::$closed)
                throw new \Exception("Trying to add middleware to ".static::class." when closed.");

            array_push(static::$globalMiddlewares, $middleware);

            return static::class;
        }

        public static function handleRequest(
            string $method, 
            string $url, 
            ?array $headers = [], 
            ?string $body = ''
        ): void {
            static::$closed = true;
            static::$res = new (Response::getOverride());

            $url_path = new (UrlPathModel::getOverride())($url);
            $method = trim(strtolower($method));

            // Find route
            $route = (RouteController::getOverride())::find($method, $url_path);
            
            if(isset($route)) {
                static::handleRoute($method, $url, $headers, $body, $route);
                return;
            }

            static::handleException(new ResponseException('Route Not Found', 404));  
        }

        public static function handleException(\Exception $e): void {
            static::$res->sendError($e);
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
            string $method, 
            string $url, 
            array $headers, 
            string $body,
            RouteModel $route
        ): void {
            static::$req = new (Request::getOverride())(
                $method, 
                $url, 
                $headers, 
                $body, 
                $route
            );

            register_shutdown_function([ static::class, 'exitHandler' ]);
            // set_error_handler([ static::class, 'errorHandler' ], E_ALL & ~E_WARNING);

            try {
                $route->handle(static::$req, static::$res);
            } catch(\Exception $e) {
                // If app is in development mode, throw the exception if it is not a ResponseException
                if(APP_MODE_DEV && get_class($e) !== ResponseException::class) {
                    throw $e;
                }

                static::handleException($e, $route);
            }
        }
    }