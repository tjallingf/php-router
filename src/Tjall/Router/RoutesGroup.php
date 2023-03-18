<?php
    namespace Tjall\Router;

    use Tjall\Router\Router;

    class RoutesGroup {
        public array $middlewares = ['before' => [], 'after' => []];

        function __construct(callable $add_routes_callback) {
            Router::$currentRoutesGroup = $this;
            call_user_func($add_routes_callback);
            Router::$currentRoutesGroup = null;
        }

        public function before(callable $handler): self {
            array_push($this->middlewares['before'], new Middleware($handler));
            return $this;
        }

        public function after(callable $handler): self {
            array_push($this->middlewares['after'], new Middleware($handler));
            return $this;
        }

        function call(array $middlewares) {
            foreach ($middlewares as $middleware) {
                if(!$middleware instanceof Middleware) continue;
                
                $middleware->call([ 
                    Router::$request, Router::$response
                ]);
            }
        }
    }