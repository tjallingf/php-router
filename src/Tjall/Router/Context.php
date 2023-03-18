<?php 
    namespace Tjall\Router;

    use Exception;

    class Context {
        protected static ?array $context = null;

        public static function store(array $context) {
            static::$context = $context;
        }

        public static function clear() {
            static::$context = null;
        }

        public static function getOrFail(string $key) {
            if(array_key_exists($key, static::$context))
                return static::$context[$key];

            return null;
        }

        public static function get(string $key) {
            if(array_key_exists($key, static::$context))
                return static::$context[$key];

            throw new Exception("Cannot find context value by key '$key'.");
        }
    }
?>