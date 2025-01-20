<?php
    namespace Tjall\Router;

    use Tjall\Router\Config;
    use Tjall\Router\Lib;
    use Tjall\Router\Http\Request;
    use Tjall\Router\Http\Response;
    use Tjall\Router\Http\Status;
    use Exception;
    use Tjall\Router\RoutesGroup;
    use Tjall\Router\Handlers\ErrorHandler;
    use Tjall\Router\Handlers\RouteHandler;

    class Router {
        protected static array $routes = [];
        public static array $errorRoutes = [];
        public static ?RoutesGroup $currentRoutesGroup = null;
        public static Request $request;
        public static Response $response;
        public static object $addMiddleware;

        static function run(?array $config = []) {
            // Store config
            Config::store($config);

            // Connect to database
            if(Config::get('database.hostname')) {
                Database::connect();
            }

            // Load routes
            $routes_dir = Lib::joinPaths(Config::get('rootDir'), Config::get('routes.dir'));
            Lib::requireAll($routes_dir);

            // Setup error handler in production mode
            if(Config::get('mode') !== 'dev') {
                set_error_handler([ ErrorHandler::class, 'handle' ]);
            }

            try {
                RouteHandler::handle(static::$routes);
            } catch(\Exception $e) {
                ErrorHandler::handle($e);
            }

            if(!isset(static::$response)) {
                ErrorHandler::handle(new RouteException("No route found for url '".RouteHandler::getCurrentUri()."'", Status::NOT_FOUND));
            }

            if(isset(static::$response)) {
                static::$response->end();
            } else {
                throw new Exception('Failed to end response.');
            }

        }

        static function url(string $url) {
            return Lib::formatUrlPath(Config::get('routes.basePath').'/'.$url);
        }

        static function group(callable $add_routes): RoutesGroup {
            return new RoutesGroup($add_routes);
        }

        static function error(int $status, callable $callback): void {
            $route = new Route([], null, $callback, null);
            static::$errorRoutes[$status] = static::$errorRoutes[$status] ?? [];

            array_push(static::$errorRoutes[$status], $route);
        }

        static function match(string $methods, string $url, callable $callback): Route {
            $methods = explode('|', $methods);
            $route = new Route($methods, $url, $callback, static::$currentRoutesGroup);

            foreach ($methods as $method) {
                static::$routes[$method][] = $route;
            }
            
            return $route;
        }

        static function all(string $url, callable $callback): void {
            static::match('GET|POST|PUT|PATCH|OPTIONS|DELETE', $url, $callback);
        }
        
        static function get(string $url, callable $callback): void {
            static::match('GET', $url, $callback);
        }

        static function post(string $url, callable $callback): void {
            static::match('POST', $url, $callback);
        }

        static function put(string $url, callable $callback): void {
            static::match('PUT', $url, $callback);
        }

        static function patch(string $url, callable $callback): void {
            static::match('PATCH', $url, $callback);
        }

        static function options(string $url, callable $callback): void {
            static::match('OPTIONS', $url, $callback);
        }

        static function delete(string $url, callable $callback): void {
            static::match('DELETE', $url, $callback);
        }
    }