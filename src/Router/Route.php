<?php
    namespace Router;

    use Router\Config;
    use Router\Lib;
    use Router\Models\RouteModel;
    use Router\Models\UrlTemplateModel;
    use Router\Controllers\RouteController;
    use Router\Exceptions\ResponseException;

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
            
            $url_template = new (UrlTemplateModel::getOverride())($url_template_path);

            $route = new (RouteModel::getOverride())($method, $url_template, $callback);

            // Add RouteModel to index of RouteController
            RouteController::getOverride()::create(null, $route);

            return $route;
        }

        public static function api(
            string $path,
            string|object $controller, 
            array $methods = [ 
                'index'  => 'index', 
                'find'   => 'find', 
                'create' => 'create',
                'edit'   => 'edit',
                'update' => 'update'
            ]
        ) {
            if(isset($methods['index'])) {
                static::get($path, function($req, $res) use ($controller, $methods) {
                    $data = call_user_func([ $controller, $methods['index']]);
                    return $res->sendJson($data);
                });
            }

            if(isset($methods['find'])) {
                static::get($path.'/{id}', function($req, $res) use ($controller, $methods) {
                    $value = call_user_func([ $controller, $methods['find']], $req->getParam('id'));
                    if(!isset($value)) throw new ResponseException(null, 404);
                    
                    return $res->sendJson($value);
                });
            }

            if(isset($methods['create'])) {
                static::post($path, function($req, $res) use ($controller, $methods) {
                    call_user_func([ $controller, $methods['create']], $req->getParam('id'), $req->getBody());
                    $value = (array) call_user_func([ $controller, $methods['find']], $req->getParam('id'));

                    return $res->sendJson($value);
                });
            }

            if(isset($methods['edit'])) {
                static::patch($path.'/{id}', function($req, $res) use ($controller, $methods) {
                    call_user_func([ $controller, $methods['edit']], $req->getParam('id'), $req->getBody());
                    $value = (array) call_user_func([ $controller, $methods['find']], $req->getParam('id'));
                    
                    return $res->sendJson($value);
                });
            }

            if(isset($methods['update'])) {
                static::put($path.'/{id}', function($req, $res) use ($controller, $methods) {
                    call_user_func([ $controller, $methods['update']], $req->getParam('id'), $req->getBody());
                    $value = (array) call_user_func([ $controller, $methods['find']], $req->getParam('id'));
 
                    return $res->sendJson($value);
                });
            }
        }

        /* Aliases for Route::listen() */
        public static function any(string $path, callable $callback): RouteModel {
            return static::listen('any', $path, $callback);
        }
        
        public static function delete(string $path, callable $callback): RouteModel {
            return static::listen('delete', $path, $callback);
        }

        public static function get(string $path, callable $callback): RouteModel {
            return static::listen('get', $path, $callback);
        }

        public static function options(string $path, callable $callback): RouteModel {
            return static::listen('options', $path, $callback);
        }

        public static function patch(string $path, callable $callback): RouteModel {
            return static::listen('patch', $path, $callback);
        }

        public static function post(string $path, callable $callback): RouteModel {
            return static::listen('post', $path, $callback);
        }

        public static function put(string $path, callable $callback): RouteModel {
            return static::listen('put', $path, $callback);
        }

        public static function update(string $path, callable $callback): RouteModel {
            return static::listen('update', $path, $callback);
        }
    }