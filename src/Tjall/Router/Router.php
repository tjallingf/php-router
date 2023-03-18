<?php
    namespace Tjall\Router;

    use Tjall\Router\Config;
    use Tjall\Router\Request;
    use Tjall\Router\Response;
    use Tjall\Router\Lib;
    use Tjall\Router\Http\Status;
    use Exception;

    class Router {
        protected static array $routes = [];
        protected static array $errorRoutes = [];
        protected static Request $request;
        protected static Response $response;
        protected static $router;

        static function run(?array $config = []) {
            // Store config
            Config::store($config);

            // Load routes
            $routes_dir = Lib::joinPaths(Config::get('rootDir'), Config::get('routes.dir'));
            Lib::requireAll($routes_dir);

            // Start router
            @static::$router->run();

            // Throw 404 if no matching route was found
            if(!isset(static::$response)) {
                static::handleErrorRoutes(Status::NOT_FOUND);
            }

            static::$response->end();
        }

        protected static function handleErrorRoutes(int $status) {
            if(!isset(static::$errorRoutes[$status]))
                throw new Exception("Failed with status code $status.");


            // Set response status
            static::handleRoute(function(Request $req, Response $res) use($status) {
                $res->status($status);
            });

            foreach (static::$errorRoutes[$status] as $callback) {
                static::handleRoute($callback);
            }
        }

        protected static function handleRoute($callback, array $params = []): void {
            if(!isset(static::$request)) {
                static::$request = new Request($params);
                static::$response = new Response(static::$request);
            }

            call_user_func_array($callback, [
                static::$request,
                static::$response
            ]);
        }

        /*
         * Used for setting up error routes
         */
        static function error(int $status, $callback): void {
            static::$errorRoutes[$status] = static::$errorRoutes[$status] ?? [];
            array_push(static::$errorRoutes[$status], $callback);
        }

        /*
         * Used for setting up routes 
         */
        static function match(string $methods, string $url, $callback): void {
            $prefixed_url = Lib::joinPaths(Config::get('routes.basePath'), $url);
            static::$router->match($methods, $prefixed_url, function(...$params) use($callback) {
                return static::handleRoute($callback, $params);
            });
        }

        static function all(string $url, $callback): void {
            static::match('GET|POST|PUT|PATCH|OPTIONS|DELETE', $url, $callback);
        }
        
        static function get(string $url, $callback): void {
            static::match('GET', $url, $callback);
        }

        static function post(string $url, $callback): void {
            static::match('POST', $url, $callback);
        }

        static function put(string $url, $callback): void {
            static::match('PUT', $url, $callback);
        }

        static function patch(string $url, $callback): void {
            static::match('PATCH', $url, $callback);
        }

        static function options(string $url, $callback): void {
            static::match('OPTIONS', $url, $callback);
        }

        static function delete(string $url, $callback): void {
            static::match('DELETE', $url, $callback);
        }
        
        static function _init() {
            static::$router = new \Bramus\Router\Router();
        }
    }

    Router::_init();