<?php
    namespace Router\Helpers;

    use Router\Models\UrlTemplateModel;
    use Router\Models\RouteModel;
    use Router\Controllers\RouteController;
    use Router\Helpers\Config;
    use Router\Lib;

    class Route {
        public static function listen(string $method, string $url_template_path, callable $callback) {
            // Transform method
            $method = trim(strtolower($method));

            // Prepend 'router.baseUrl' to url template path
            $url_template_path = '/'.trim(Lib::joinPaths(Config::get('router.baseUrl'), $url_template_path), '/');
            
            $url_template = new UrlTemplateModel($url_template_path);

            RouteController::create(new RouteModel(
                $method, $url_template, $callback
            ));
        }

        /* Aliases for self::listen() */
        public static function any(string $path, callable $callback) {
            return self::listen('any', $path, $callback);
        }
        
        public static function delete(string $path, callable $callback) {
            return self::listen('delete', $path, $callback);
        }

        public static function get(string $path, callable $callback) {
            return self::listen('get', $path, $callback);
        }

        public static function options(string $path, callable $callback) {
            return self::listen('options', $path, $callback);
        }

        public static function patch(string $path, callable $callback) {
            return self::listen('patch', $path, $callback);
        }

        public static function post(string $path, callable $callback) {
            return self::listen('post', $path, $callback);
        }

        public static function put(string $path, callable $callback) {
            return self::listen('put', $path, $callback);
        }

        public static function update(string $path, callable $callback) {
            return self::listen('update', $path, $callback);
        }
    }