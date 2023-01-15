<?php 
    namespace Router\Controllers;

    abstract class Controller {
        protected static array $data;

        static public function index() {
            if(!isset(self::$data)) static::populate();

            return self::$data;
        }

        static public function find(string $item) {
            return array_get_path(static::index(), $item);
        }

        abstract static public function populate();
    }