<?php
    namespace Router;

    use Router\Config;
    use Router\Overrides;
    use Router\Response;
    use Router\Models\UrlModel;
    use Router\Models\RouteModel;
    use Router\Models\RouteMiddlewareModel;
    use Router\Controllers\RouteController;
    use Exception;

    class Router {
        public static bool $closed = false;
        public static array $globalMiddlewares = [];

        public static function use(RouteMiddlewareModel $middleware): static {
            if(self::$closed)
                throw new Exception("Cannot add middleware to {self::CLASS} when it's closed.");

            array_push(self::$globalMiddlewares, $middleware);

            return new static();
        }

        public static function handleRequest(
            string $method, 
            string $url_path, 
            ?array $headers = [], 
            ?string $body = ''
        ): void {
            self::$closed = true;

            $url = new (Overrides::get(UrlModel::class))($url_path);
            $method = trim(strtolower($method));

            // Find route
            $found_route = (Overrides::get(RouteController::class))::find($method, $url);
            
            if($found_route) {
                static::handleRoute($found_route, $method, $url, $headers, $body);
                return;
            }
                
            // Throw 404 error if no route can be found.
            (Overrides::get(Response::class))::get()->sendError('Route not found.', 404);
        }
 
        protected static function handleRoute(
            RouteModel $route,
            string $method, 
            UrlModel $url, 
            array $headers, 
            string $body
        ): void {
            $res = (Overrides::get(Response::class))::get();
            $req = (Overrides::get(Request::class))::get(
                $method, 
                $url, 
                $headers, 
                $body, 
                $route->getParams($url)
            );

            $err = $route->handle($req, $res);

            if($err) {
                $res->sendError($err);
                return;
            }

            $res->end();
        }
    }