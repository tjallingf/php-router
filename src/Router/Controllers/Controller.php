<?php 
    namespace Router\Controllers;

    use Router\Lib;

    class Controller {
        public static string $dir;
        protected static array $data = [];

        public static function index() {
            if(!isset(static::$data)) 
                static::$data = static::populate();

            return static::$data;
        }

        public static function store(array $data) {
            self::$data = (array) $data;
        }

        public static function find(string $item) {
            static::index();
            return Lib::arrayGetByPath(static::$data, $item);
        }

        public static function edit(string $item, $props) {
            static::index();
            return Lib::arraySetByPath(static::$data, $item, $props);
        }

        public static function populate() {}
    }