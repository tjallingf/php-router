<?php 
    namespace Router\Controllers;

    abstract class Controller {
        protected static array $data = [];

        public static function index(): array {
            return static::$data;
        }
        
        public static function store(array $data): static {
            static::$data = $data;

            return new static();
        }

        public static function find(string $id) {
            return @static::$data[$id];
        }

        public static function scan(string $id): bool {
            return !is_null(static::find($id));
        }

        public static function edit(string $id, $value): static {
            if(isset(static::$data[$id]) && is_array($value))
                $value = array_replace_recursive(static::$data[$id], $value);

            static::update($id, $value);

            return new static();
        }

        public static function create(string $id, $value): static {
            if(!isset(static::$data[$id]))
                static::update($id, $value);

            return new static();
        }

        public static function update(string $id, $value): static{
            static::$data[$id] = $value;

            return new static();
        }
    }