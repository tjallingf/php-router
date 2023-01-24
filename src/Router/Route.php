<?php
    namespace Router;

    use Router\Config;
    use Router\Lib;
    use Router\Models\RouteModel;
    use Router\Models\UrlPathTemplateModel;
    use Router\Controllers\RouteController;
    use Router\Exceptions\ResponseException;
    use Router\ApiHandler;

    class Route {
        protected static $globalMiddleware = [];

        public static function listen(
            string $method, 
            string $url_template_path, 
            callable $callback
        ): RouteModel {
            // Transform method
            $method = trim(strtolower($method));

            // Prepend 'router.baseUrl' to url template path
            $url_template_path = '/'.trim(Lib::joinPaths(Config::get('router.baseUrl'), $url_template_path), '/');
            
            $url_template = new (UrlPathTemplateModel::getOverride())($url_template_path);

            $route = new (RouteModel::getOverride())($method, $url_template, $callback);

            // Add RouteModel to index of RouteController
            RouteController::getOverride()::create(null, $route);

            return $route;
        }

        public static function api(
            string $base_path_template,
            ApiHandler $handler
        ) {
            $handler->setBasePathTemplate($base_path_template);
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
        public static function any(string $path_template, callable $callback): RouteModel {
            return static::listen('any', $path_template, $callback);
        }
        
        public static function delete(string $path_template, callable $callback): RouteModel {
            return static::listen('delete', $path_template, $callback);
        }

        public static function get(string $path_template, callable $callback): RouteModel {
            return static::listen('get', $path_template, $callback);
        }

        public static function options(string $path_template, callable $callback): RouteModel {
            return static::listen('options', $path_template, $callback);
        }

        public static function patch(string $path_template, callable $callback): RouteModel {
            return static::listen('patch', $path_template, $callback);
        }

        public static function post(string $path_template, callable $callback): RouteModel {
            return static::listen('post', $path_template, $callback);
        }

        public static function put(string $path_template, callable $callback): RouteModel {
            return static::listen('put', $path_template, $callback);
        }

        public static function update(string $path_template, callable $callback): RouteModel {
            return static::listen('update', $path_template, $callback);
        }
    }