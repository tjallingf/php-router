<?php
    namespace Tjall\Router\Handlers;

    use Tjall\Router\Config;
    use Tjall\Router\Lib;
    use TypeError;

    class RouteHandler {
        public static function handle(array $routes): int {
            // Counter to keep track of the number of routes we've handled
            $count_handled = 0;

            $method = static::getCurrentMethod();
            $uri = static::getCurrentUri();

            $routes_for_method = @$routes[$method];
            if(!isset($routes_for_method)) 
                return 0;

            // Loop all routes
            foreach ($routes_for_method as $route) {
                // get routing matches
                $is_match = static::patternMatches($route->pattern, $uri, $matches, PREG_OFFSET_CAPTURE);

                if(!$is_match)
                    continue;

                // Rework matches to only contain the matches, not the orig string
                $matches = array_slice($matches, 1);

                // Extract the matched URL parameters (and only the parameters)
                $params = array_map(function ($match, $index) use ($matches) {

                    // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                    if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                        if ($matches[$index + 1][0][1] > -1) {
                            return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                        }
                    } // We have no following parameters: return the whole lot

                    return isset($match[0][0]) && $match[0][1] != -1 ? trim($match[0][0], '/') : null;
                }, $matches, array_keys($matches));

                // Call the handling function with the URL parameters if the desired input is callable
                $route->call($params);

                $count_handled++;
            }

            return $count_handled;
        }

        public static function getCurrentMethod() : string {
            return trim(strtoupper($_SERVER['REQUEST_METHOD']));
        }

        public static function getCurrentUri(): string {
            return Lib::formatUrlPath($_SERVER['REQUEST_URI']);
        }
        
        /**
         * Replace all curly braces matches {} into word patterns (like Laravel).
         * Checks if there is a routing match.
         *
         * @param $pattern
         * @param $uri
         * @param $matches
         * @param $flags
         *
         * @return bool Whether the pattern matches the uri.
         */
        protected static function patternMatches($pattern, $uri, &$matches, $flags) {
            if(!is_string($pattern)) return false;

            // Replace all curly braces matches {} into word patterns (like Laravel)
            // and remove trailing and leading slashes.
            $pattern = trim(preg_replace('/\/{(.*?)}/', '/(.*?)', $pattern), '/');

            $uri_path = trim(parse_url($uri, PHP_URL_PATH), '/');

            $rewrites = Config::get('routes.rewrite');
            if(isset($rewrites) && count($rewrites) > 0) {
                foreach ($rewrites as $from => $to) {
                    $from = Lib::formatUrlPath(Config::get('routes.basePath').$from, false, false);
                    $to = Lib::formatUrlPath(Config::get('routes.basePath').$to, false, false);
                    
                    // Rewrite the url base if it matches
                    if(str_starts_with($uri_path, $from.'/') || strlen($uri_path) === strlen($from))
                        $uri_path = $to.'/'.substr($uri_path, strlen($from)+1);
                }
            }

            // we may have a match!
            return boolval(preg_match_all('#^' . $pattern . '$#', $uri_path, $matches, PREG_OFFSET_CAPTURE));
        }
    }