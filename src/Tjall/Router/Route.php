<?php
    namespace Tjall\Router;

    use Tjall\Router\Router;
    use Tjall\Router\Lib;
    use Tjall\Router\Http\Request;
    use Tjall\Router\Http\Response;
    use Tjall\Router\Config;
    use Tjall\Router\RoutesGroup;

    class Route {
        public $callback;
        public ?string $url;
        public ?string $method;
        public ?RoutesGroup $group;

        function __construct(?string $method, ?string $url, callable $callback, ?RoutesGroup $group) {
            $this->method = $method;
            $this->callback = $callback;
            $this->url = '/'.trim(Lib::joinPaths(Config::get('routes.basePath'), $url), '/');
            $this->group = $group;
        }
        
        function call(?array $params = []) {
            if(!isset(Router::$request)) {
                Router::$request = new Request($params);
                Router::$response = new Response(Router::$request);
            }

            $this->group->call($this->group->middlewares['before']);

            call_user_func_array($this->callback, [
                Router::$request,
                Router::$response
            ]);

            $this->group->call($this->group->middlewares['after']);
        }
    }