<?php
    namespace Router\Models;

    use Router\Request;
    use Router\Response;
    use Router\Middleware;
    use Router\Interfaces\MiddlewareInterface;
    use Exception;

    class RouteMiddlewareModel {
        public string $id;
        public string $treatAsMethod;
        protected static $ids = [];
        protected $handler;

        public function __construct(string $id, string|callable|MiddlewareInterface $handler, string $treat_as_method) {
            if(isset($this->ids[$id]))
                throw new Exception("Cannot create middleware with id '$id', because the id is already in use.");

            $this->id = $id;
            $this->handler = $handler;
            $this->treatAsMethod = $treat_as_method;

            self::$ids[$id] = true;
        }

        public function handle(string $method, Request $req, Response $res): Request|Response|null {
            // Return if the handler methods don't match and
            // the method of the handler is set.
            if($method != $this->treatAsMethod && $this->treatAsMethod != Middleware::NONE) 
                return null;

            if($this->treatAsMethod == Middleware::NONE) {
                if(!method_exists($this->handler, $method))
                    throw new Exception("Handler '{$this->handler}' does not have method '$method()'.");
                
                $result = call_user_func([ $this->handler, $method ], $req, $res);
            } else {
                if(!is_callable($this->handler))
                    throw new Exception("Handler '{$this->handler}' is not callable.");
                
                $result = call_user_func($this->handler, $req, $res);
            }
            
            return $result instanceof Request || $result instanceof Response ? $result : null;
        }
    }