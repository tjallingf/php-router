<?php
    namespace Router\Controllers;

    use Router\Controllers\Controller;
    use Router\Lib;
    use Router\Controllers\MiddlewareController as MwController;
    use Router\Models\UrlModel;
    use Router\Models\RouteModel;
    use Router\Helpers\Response;

    class RouteController extends Controller {
        public static string $dir = '/resources/routes';
        public static Response $res;

        public static function create(RouteModel $route) {
            array_push(self::$data, $route);
        }

        public static function index(): ?array {
            if(empty($data)) {
                Lib::requireAll(lib::joinPaths(Lib::getRootDir(), self::$dir));
                self::$res = MwController::construct('Response');
            }
            
            return self::$data;
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
     
        protected static function handleRoute(RouteModel $route, string $method, UrlModel $url): void {
            $req = MwController::construct('Request', [ $route, $method, $url ]);
            $res = MwController::construct('Response');

            $next = function (...$args) use ($res) { 
                return $res->sendError(...$args); 
            };

            $err = $route->handle($req, $res, $next);

            if($err) {
                $res->sendError($err);
                return;
            }

            $res->end();
        }
        
        public static function handleRequest(string $method, string $url_path): void {
            $url = new UrlModel($url_path);
            $method = trim(strtolower($method));

            // Find route
            $found_route = self::findRoute($method, $url);
            
            if($found_route)
                self::handleRoute($found_route, $method, $url);
                
            // Throw 404 error if no route can be found.
            self::$res->sendError('Route not found.', 404);
        }
    }