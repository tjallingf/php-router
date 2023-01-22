<?php
    namespace Router;

    use Router\Config;
    use Router\Lib;
    use Router\Overrides;
    use Router\Models\RouteModel;
    use Router\Models\UrlTemplateModel;
    use Router\Controllers\RouteController;

    class Route {
        protected static $globalMiddleware = [];

        public static function listen(
            string $method, 
            string $url_template_path, 
            callable $callback
        ): RouteModel {
            // Transform method
            $method = trim(strtolower($method));

            // Prepend 'router.baseUrl' to url template path
            $url_template_path = '/'.trim(Lib::joinPaths(Config::get('router.baseUrl'), $url_template_path), '/');
            
            $url_template = new (Overrides::get(UrlTemplateModel::class))($url_template_path);

            $route = new (Overrides::get(RouteModel::class))($method, $url_template, $callback);

            // Add RouteModel to index of RouteController
            (Overrides::get(RouteController::class))::create(null, $route);

            return $route;
        }

        /* Aliases for Route::listen() */
        public static function any(string $path, callable $callback): RouteModel {
            return self::listen('any', $path, $callback);
        }
        
        public static function delete(string $path, callable $callback): RouteModel {
            return self::listen('delete', $path, $callback);
        }

        public static function get(string $path, callable $callback): RouteModel {
            return self::listen('get', $path, $callback);
        }

        public static function options(string $path, callable $callback): RouteModel {
            return self::listen('options', $path, $callback);
        }

        public static function patch(string $path, callable $callback): RouteModel {
            return self::listen('patch', $path, $callback);
        }

        public static function post(string $path, callable $callback): RouteModel {
            return self::listen('post', $path, $callback);
        }

        public static function put(string $path, callable $callback): RouteModel {
            return self::listen('put', $path, $callback);
        }

        public static function update(string $path, callable $callback): RouteModel {
            return self::listen('update', $path, $callback);
        }
    }