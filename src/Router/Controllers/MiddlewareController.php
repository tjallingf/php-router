<?php 
    namespace Router\Controllers;

    class MiddlewareController {
        protected static $middlewares = [];
        
        public static function update(string $id, string $class) {
            self::$middlewares[$id] = $class;
        }

        public static function find(string $id) {
            if(!isset(self::$middlewares[$id]))
                throw new \Exception("No midlleware was set for '{$id}'.");

            return self::$middlewares[$id];
        }

        public static function construct(string $id, array $args = []) {
            $middleware = self::find($id);
            return new $middleware(...$args);
        }
    }
?>