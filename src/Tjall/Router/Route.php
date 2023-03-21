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
        public ?string $pattern;
        public ?RoutesGroup $group;
        public ?array $methods;

        function __construct(array $methods, ?string $pattern, callable $callback, ?RoutesGroup $group) {
            $this->methods = $methods;
            $this->callback = $callback;
            $this->pattern = Lib::formatUrlPath(Config::get('routes.basePath').'/'.$pattern);
            $this->group = $group;
        }
        
        function call(?array $params = []) {
            if(!isset(Router::$request)) {
                Router::$request = new Request($params);
                Router::$response = new Response(Router::$request);
            }

            if(isset($this->group))
                $this->group->call($this->group->middlewares['before']);

            call_user_func_array($this->callback, [
                Router::$request,
                Router::$response
            ]);

            if(isset($this->group))
                $this->group->call($this->group->middlewares['after']);
        }
    }