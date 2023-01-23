<?php
    namespace Router;

    use Router\Helpers\Overridable;
    use Router\Models\MiddlewareObjectModel;
    use Router\Models\MiddlewareCallableModel;

    class Middleware extends Overridable {
        public static array $ids = [];

        public static function mapRequest(string $id, callable $handler) {
            return static::build($id, $handler, static::MAP_REQUEST);
        }

        public static function mapResponse(string $id, callable $handler) {
            return static::build($id, $handler, static::MAP_RESPONSE);
        }

        public static function create(string $id, string|object $handler) {
            // If $handler is a class string, construct it
            if(is_string($handler)) {
                if(!class_exists($handler))
                    throw new \Exception("Class '$handler' does not exist");
                
                $handler = new $handler();
            }

            return static::build($id, $handler);
        }

        protected static function build(
            string $id, 
            callable|object $handler, 
            ?string $callable_as_method = null
        ) {  
            if(array_key_exists($id, static::$ids))
                throw new \Exception("Middleware with id '$id' already exists");
            
            static::$ids[$id] = true;
            if(is_callable($handler)) {
                return new MiddlewareCallableModel($id, $handler, $callable_as_method);
            } else {
                return new MiddlewareObjectModel($id, $handler);
            }
        }

        public const MAP_REQUEST  = 'mapRequest';
        public const MAP_RESPONSE = 'mapResponse';
    }