<?php
    namespace Router\Controllers;

    use Router\Lib;
    use Router\Models\UrlModel;
    use Router\Controllers\Controller;

    class RouteController extends Controller {
        protected const DIR = '/resources/routes';

        protected static array $data = [];

        public static function index(): array {
            if(empty(static::$data))
                Lib::requireAll(lib::joinPaths(Lib::getRootDir(), static::DIR));
            
            return static::$data ?? [];
        }

        public static function find(string $method, UrlModel $url = null) {
            $found_route = null;

            foreach (static::index() as $route) {
                if(!$route->matchesMethod($method))
                    continue;
                
                if(!$route->matchesUrl($url))
                    continue;

                $found_route = $route;
                break;
            }

            return $found_route;
        }
    
        public static function create(?string $id = null, $route): void {
            array_push(static::$data, $route);
        }
    }