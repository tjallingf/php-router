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
                $is_match = static::patternMatches($route->pattern, $uri, $params);

                if(!$is_match)
                    continue;

                // // Rework matches to only contain the matches, not the orig string
                // $matches = array_slice($matches, 1);

                // // Extract the matched URL parameters (and only the parameters)
                // $params = array_map(function ($match, $index) use ($matches) {

                //     // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                //     if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                //         if ($matches[$index + 1][0][1] > -1) {
                //             return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                //         }
                //     } // We have no following parameters: return the whole lot

                //     return isset($match[0][0]) && $match[0][1] != -1 ? trim($match[0][0], '/') : null;
                // }, $matches, array_keys($matches));

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
         * @param $params
         *
         * @return bool Whether the pattern matches the uri.
         */
        protected static function patternMatches(string $pattern, string $uri, &$params): bool {
            if(!is_string($pattern)) return false;
            $params = [];

            // Do some rewrites as specified in config
            $rewrites = Config::get('routes.rewrite');
            if(isset($rewrites) && count($rewrites) > 0) {
                foreach ($rewrites as $from => $to) {
                    $from = Lib::formatUrlPath(Config::get('routes.basePath').$from, true, false);
                    $to = Lib::formatUrlPath(Config::get('routes.basePath').$to, true, false);
                    
                    // Rewrite the url base if it matches
                    if(str_starts_with($uri, $from.'/') || strlen($uri) === strlen($from))
                        $uri = $to.'/'.substr($uri, strlen($from)+1);
                }
            }

            // Only keep the path from the url (remove query etc.).
            $uri = parse_url($uri, PHP_URL_PATH);

            // Check if the rewritten uri matches
            $pattern_parts = explode('/', $pattern);
            $uri_parts = explode('/', $uri);

            // If the number of uri parts is greater than the number of pattern parts
            // the pattern does not match.
            if(count($uri_parts) > count($pattern_parts))
                return false;

            foreach ($pattern_parts as $i => $pattern_part) {
                $type = str_starts_with($pattern_part, '{') && str_ends_with($pattern_part, '}')
                    ? 'parameter'
                    : 'exact';

                // Get the value that corresponds to the parameter
                $uri_part = @$uri_parts[$i];

                if($type === 'exact') {
                    if($uri_part !== $pattern_part) {
                        return false;
                    }
                } 
                else if($type === 'parameter') {
                    // Whether the parameter is optional
                    $is_optional = false;

                    // Remove first and last character from the string, to
                    // remove the curly braces around the name.
                    $name = substr($pattern_part, 1, -1);

                    // Check whether the parameter is optional
                    if(str_ends_with($name, '?')) {
                        $is_optional = true;

                        // Remove the ? from the end of the name
                        $name = substr($name, 0, -1);
                    }

                    // If a required parameter does not have a value, the route does not match
                    if(!isset($uri_part) && !$is_optional) {
                        return false;
                    }

                    $params[$name] = $uri_part;
                }
            }

            return true;
        }
    }