<?php
    namespace Router;

    use Router\Config;
    use Router\Lib;
    use Router\Models\RouteModel;
    use Router\Models\UrlPathTemplateModel;
    use Router\Controllers\RouteController;
    use Router\Exceptions\ResponseException;
    use Router\RouteApiHandler;

    class Route {
        protected static $globalMiddleware = [];

        public static function listen(
            string $method, 
            string $url_path_template, 
            callable $callback,
            array $options
        ): RouteModel {
            // Transform method
            $method = trim(strtolower($method));

            // Trim slashes form url path template
            $url_path_template = trim($url_path_template, '/');
        
            $route = new (RouteModel::getOverride())($method, $url_path_template, $callback, $options);

            // Add RouteModel to index of RouteController
            RouteController::getOverride()::create(null, $route);

            return $route;
        }

        public static function api(
            string $path_template_base,
            object|string $controller,
            array $options
        ) {
            $handler = new RouteApiHandler($path_template_base, $controller, $options);
            $handler->registerRoutes();
            // if(isset($methods['index'])) {
            //     static::get($path, function($req, $res) use ($controller, $methods) {
            //         $data = call_user_func([ $controller, $methods['index']]);
            //         return $res->sendJson($data);
            //     });
            // }

            // if(isset($methods['find'])) {
            //     static::get($path.'/{id}', function($req, $res) use ($controller, $methods) {
            //         $value = call_user_func([ $controller, $methods['find']], $req->getParam('id'));
            //         if(!isset($value)) throw new ResponseException(null, 404);
                    
            //         return $res->sendJson($value);
            //     });
            // }

            // if(isset($methods['create'])) {
            //     static::post($path, function($req, $res) use ($controller, $methods) {
            //         call_user_func([ $controller, $methods['create']], $req->getParam('id'), $req->getBody());
            //         $value = (array) call_user_func([ $controller, $methods['find']], $req->getParam('id'));

            //         return $res->sendJson($value);
            //     });
            // }

            // if(isset($methods['edit'])) {
            //     static::patch($path.'/{id}', function($req, $res) use ($controller, $methods) {
            //         call_user_func([ $controller, $methods['edit']], $req->getParam('id'), $req->getBody());
            //         $value = (array) call_user_func([ $controller, $methods['find']], $req->getParam('id'));
                    
            //         return $res->sendJson($value);
            //     });
            // }

            // if(isset($methods['update'])) {
            //     static::put($path.'/{id}', function($req, $res) use ($controller, $methods) {
            //         call_user_func([ $controller, $methods['update']], $req->getParam('id'), $req->getBody());
            //         $value = (array) call_user_func([ $controller, $methods['find']], $req->getParam('id'));
 
            //         return $res->sendJson($value);
            //     });
            // }
        }

        /* Aliases for Route::listen() */
        public static function any(string $path_template, callable $callback, array $options = []): RouteModel {
            return static::listen('any', $path_template, $callback, $options);
        }
        
        public static function delete(string $path_template, callable $callback, array $options = []): RouteModel {
            return static::listen('delete', $path_template, $callback, $options);
        }

        public static function get(string $path_template, callable $callback, array $options = []): RouteModel {
            return static::listen('get', $path_template, $callback, $options);
        }

        public static function options(string $path_template, callable $callback, array $options = []): RouteModel {
            return static::listen('options', $path_template, $callback, $options);
        }

        public static function patch(string $path_template, callable $callback, array $options = []): RouteModel {
            return static::listen('patch', $path_template, $callback, $options);
        }

        public static function post(string $path_template, callable $callback, array $options = []): RouteModel {
            return static::listen('post', $path_template, $callback, $options);
        }

        public static function put(string $path_template, callable $callback, array $options = []): RouteModel {
            return static::listen('put', $path_template, $callback, $options);
        }

        public static function update(string $path_template, callable $callback, array $options = []): RouteModel {
            return static::listen('update', $path_template, $callback, $options);
        }
    }