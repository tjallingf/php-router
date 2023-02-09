<?php
    namespace Router;

    use Router\Config;
    use Router\Router;
    use Router\Controllers\RouteController;
    use Router\Controllers\ConfigController;

    final class Loader {
        protected static string $rootDir;

        public static function load(string $root_dir, array $config = []) {
            self::$rootDir = str_replace('\\', '/', realpath($root_dir));
            ConfigController::store($config);
                    
            define('APP_MODE', in_array(Config::get('mode'), ['dev', 'development', 'local']) ? 'dev' : 'prod');
            define('APP_MODE_DEV', APP_MODE === 'dev');
            define('APP_MODE_PROD', APP_MODE === 'prod');

            if(APP_MODE_DEV)
                define('APP_CLIENT_SRC_DIR', realpath(Lib::joinPaths(
                    Lib::getRootDir(), 
                    Config::get('client.srcDir'))));

            define('APP_CLIENT_OUT_DIR', realpath(Lib::joinPaths(
                Lib::getRootDir(),  
                Config::get('client.outDir'))));
        }

        public static function loadRouter() {
            // Load routes
            RouteController::getOverride()::index();

            // If a request was sent, handle it
            if(isset($_SERVER['REQUEST_METHOD']) && isset($_SERVER['REQUEST_URI'])) {
                // Get request method
                $method = $_SERVER['REQUEST_METHOD'];
                
                // Get request uri
                $url_path = $_SERVER['REQUEST_URI'];
                
                // Get headers array
                $headers = getallheaders();

                // Get body
                $body = file_get_contents('php://input');

                Router::getOverride()::handleRequest(
                    $method,
                    $url_path,
                    $headers,
                    $body
                );
            }
        }

        public static function getRootDir() {
            return self::$rootDir;
        }
    }

