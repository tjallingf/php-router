<?php
    namespace Router\Controllers;

    use Router\Controllers\Controller;
    use Router\Lib;
    use Router\Controllers\ViewController;
    use Router\Controllers\MiddlewareController as MwController;
    use Router\Models\UrlModel;
    use Router\Models\RouteModel;
    use Router\Helpers\Config;
    use Router\Helpers\Response;

    class RouteController extends Controller {
        public static string $dir = '/resources/routes';
        public static Response $res;

        public static function create(RouteModel $route) {
            array_push(static::$data, $route);
        }

        public static function populate() {
            Lib::requireAll(lib::joinPaths(Lib::getRootDir(), self::$dir));

            self::$res = MwController::construct('Response');
            
            return [];
        }

        public static function findRoute(string $method, UrlModel $url) {
            $found_route = null;

            foreach (self::index() as $route) {
                if(!$route->matchesMethod($method))
                    continue;
                
                if(!$route->matchesUrl($url))
                    continue;

                $found_route = $route;
                break;
            }

            return $found_route;
        }
     
        protected static function handleRoute(RouteModel $route, string $method, UrlModel $url) {
            $req = MwController::construct('Request', [ $route, $method, $url ]);
            $res = MwController::construct('Response');

            $next = function (...$args) use ($res) { 
                return $res->sendError(...$args); 
            };

            $err = $route->handle($req, $res, $next);

            if($err)
                return $res->sendError($err);

            return $res->end();
        }
        
        public static function handleRequest(string $method, string $url_path) {
            $url = new UrlModel($url_path);
            $method = trim(strtolower($method));

            // Find listener
            $route = self::findRoute($method, $url);
            
            // Throw 404 error if no route can be found.
            if(!$route)
                return self::$res->sendError('Route not found.', 404);

            return self::handleRoute($route, $method, $url);
        }
    }