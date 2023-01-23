<?php
    namespace Router;

    use Router\Response;
    use Router\Models\UrlModel;
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
            static::handleException(new ResponseException('Route not found', 404));
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

            register_shutdown_function([ static::class, 'exitHandler' ]);
            set_error_handler([ static::class, 'errorHandler' ], E_ALL & ~E_WARNING);

            try {
                $route->handle(static::$req, static::$res);
            } catch(\Exception $e) {
                static::handleException($e, $route);
            }
        }
    }