<?php
    namespace Router;

    use Router\Config;
    use Router\Overrides;
    use Router\Router;
    use Router\Controllers\RouteController;
    use Router\Controllers\ConfigController;

    final class Loader {
        protected static string $root_dir;

        public static function load(string $root_dir, array $config = []) {
            self::$root_dir = str_replace('\\', '/', realpath($root_dir));
            ConfigController::store($config);

            if(APP_MODE_DEV)
                define('APP_CLIENT_SRC_DIR', realpath(Lib::joinPaths(
                    Lib::getRootDir(), 
                    Config::get('client.rootDir'), 
                    Config::get('client.srcDir'))));

            define('APP_CLIENT_OUT_DIR', realpath(Lib::joinPaths(
                Lib::getRootDir(), 
                Config::get('client.rootDir'), 
                Config::get('client.outDir'))));
        }

        public static function loadRouter() {
            // Load routes
            (Overrides::get(RouteController::class))::index();

            // If a request was sent, handle it
            if(isset($_SERVER['REQUEST_METHOD']) && isset($_SERVER['REQUEST_URI'])) {
                // Get request method
                $method = $_SERVER['REQUEST_METHOD'];
                
                // Get request uri
                $url_path = '/'.trim(str_replace(Lib::getRelativeRootDir(), '', $_SERVER['REQUEST_URI']), '/');
                
                // Get headers array
                $headers = getallheaders();

                // Get body
                $body = file_get_contents('php://input');

                (Overrides::get(Router::class))::handleRequest(
                    $method,
                    $url_path,
                    $headers,
                    $body
                );
            }
        }

        public static function getRootDir() {
            return self::$root_dir;
        }
    }

