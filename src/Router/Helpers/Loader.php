<?php
    namespace Router\Helpers;

    use Router\Helpers\Route;
    use Router\Helpers\Response;
    use Router\Helpers\Request;
    use Router\Helpers\Config;
    use Router\Helpers\App;
    use Router\Controllers\ConfigController;
    use Router\Controllers\RouteController;
    use Router\Controllers\MiddlewareController as MwController;
    use Router\Controllers\UserController;
    use Router\Lib;

    require(__DIR__.'/../../polyfills.php');

    class Loader {
        protected static string $root_dir;

        public static function load(string $root_dir, array $config = []) {
            self::$root_dir = str_replace('\\', '/', realpath($root_dir));
            ConfigController::store($config);

            if(Config::get('development'))
                define('APP_CLIENT_SRC_DIR', realpath(Lib::joinPaths(
                    Lib::getRootDir(), 
                    Config::get('client.rootDir'), 
                    Config::get('client.srcDir'))));

            define('APP_CLIENT_OUT_DIR', realpath(Lib::joinPaths(
                Lib::getRootDir(), 
                Config::get('client.rootDir'), 
                Config::get('client.outDir'))));

            // Define middlewares
            MwController::update('Response', Response::class);
            MwController::update('Request', Request::class);
            MwController::update('Client', App::class);
            MwController::update('UserController', UserController::class);
            MwController::update('RouteController', RouteController::class);
        }

        public static function loadRouter() {
            // Load routes
            MwController::find('RouteController')::index();

            // If a request was sent, handle it
            if(isset($_SERVER['REQUEST_METHOD']) && isset($_SERVER['REQUEST_URI'])) {
                // Get request method
                $method = $_SERVER['REQUEST_METHOD'];
                
                // Get request uri
                $url_path = '/'.trim(str_replace(Lib::getRelativeRootDir(), '', $_SERVER['REQUEST_URI']), '/');

                MwController::find('RouteController')::handleRequest(
                    $method,
                    $url_path
                );
            }
        }

        public static function getRootDir() {
            return self::$root_dir;
        }
    }

