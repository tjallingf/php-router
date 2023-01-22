<?php 
    namespace Router\Controllers;

    use Router\Lib;

    abstract class Controller {
        public static string $dir;
        protected static array $data = [];

        public static function index() {
            // Code to fetch data should be put here
            // ...
            
            return self::$data;
        }

        public static function store(array $data) {
            static::$data = (array) $data;
        }

        public static function find(string $item) {
            static::index();
            return Lib::arrayGetByPath(static::$data, $item);
        }

        public static function edit(string $item, $props) {
            static::index();
            return Lib::arraySetByPath(static::$data, $item, $props);
        }
    }