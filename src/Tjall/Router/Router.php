<?php
    namespace Tjall\Router;

    use Tjall\Router\Config;
    use Tjall\Router\Request;
    use Tjall\Router\Response;

    class Router {
        static array $routes = [];
        public static $router;
        protected static $request;
        protected static $response;

        static function _init() {
            static::$router = new \Bramus\Router\Router();
        }

        static function run(?array $config = []) {
            Config::store($config);
            @static::$router->run();

            if(isset(static::$response)) {
                static::$response->end();
                return;
            }

            echo('404');
        }

        static function match(string $methods, string $url, $callback) {
            static::$router->match($methods, $url, function(...$params) use($callback) {
                static::$request = new Request($params);
                static::$response = new Response(static::$request);

                return call_user_func_array($callback, [
                    static::$request,
                    static::$response
                ]);
            });
        }

        static function all(string $url, $callback) {
            return static::match('GET|POST|PUT|PATCH|OPTIONS|DELETE', $url, $callback);
        }
        
        static function get(string $url, $callback) {
            return static::match('GET', $url, $callback);
        }

        static function post(string $url, $callback) {
            return static::match('POST', $url, $callback);
        }

        static function put(string $url, $callback) {
            return static::match('PUT', $url, $callback);
        }

        static function patch(string $url, $callback) {
            return static::match('PATCH', $url, $callback);
        }

        static function options(string $url, $callback) {
            return static::match('OPTIONS', $url, $callback);
        }

        static function delete(string $url, $callback) {
            return static::match('DELETE', $url, $callback);
        }
    }

    Router::_init();