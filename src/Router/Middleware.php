<?php
    namespace Router;

    use Router\Models\RouteMiddlewareModel;
    use Router\Interfaces\MiddlewareInterface;
    use Exception;

    class Middleware {
        public const MAP_REQUEST  = 'mapRequest';
        public const MAP_RESPONSE = 'mapResponse';
        public const NONE         = 'none';

        public static function mapRequest(string $id, callable $handler) {
            return self::create($id, $handler, self::MAP_REQUEST);
        }

        public static function mapResponse(string $id, callable $handler) {
            return self::create($id, $handler, self::MAP_REQUEST);
        }

        public static function create(string $id, string|callable|MiddlewareInterface $handler, ?string $type = null) {  
            // If $handler is a class string, construct it
            if(is_string($handler)) {
                if(!class_exists($handler))
                    throw new Exception("Cannot find class '$handler'.");

                $handler = new $handler();
            }
            
            // If $handler is an instance of a class. Throw an exception if
            // that class does not implement MiddlewareInterface.
            if(is_object($handler) && !is_callable($handler)) {
                if(!@class_implements($handler)[MiddlewareInterface::class])
                    throw new Exception("Handler '{$handler}' does not implement '".MiddlewareInterface::class."'.");
            
                $type = self::NONE;
            }

            return new RouteMiddlewareModel($id, $handler, $type);
        }
    }