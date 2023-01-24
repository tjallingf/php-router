<?php 
    namespace Router\Controllers;

    use Router\Helpers\Overridable;

    abstract class Controller extends Overridable {
        protected static array $data = [];

        public static function index(): array {
            return static::$data;
        }
        
        public static function store(array $data): void {
            static::$data = $data;
        }

        public static function find(string $id) {
            return @static::$data[$id];
        }

        public static function scan(string $id): bool {
            return !is_null(static::find($id));
        }

        public static function edit(string $id, $value): void {
            if(isset(static::$data[$id]) && is_array($value))
                $value = array_replace_recursive(static::$data[$id], $value);

            static::update($id, $value);
        }

        public static function create(string $id, $value): void {
            if(!isset(static::$data[$id]))
                static::update($id, $value);
        }

        public static function update(string $id, $value): void {
            static::$data[$id] = $value;
        }
    }