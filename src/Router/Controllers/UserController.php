<?php
    namespace Router\Controllers;

    use Router\Controllers\ConfigController;

    class User {
        protected static array $data = [];

        public static function index() {
            self::populate();
            return self::$data;
        }

        public static function populate() {
            $path = join_paths(root_dir(), 'storage', 'router', 'users.json');
            
            self::$data = json_decode(file_get_contents($path), true);
        }

        public static function find(string $username, bool $hide_scret = true) {
            $user = self::index()[$username];
            
            if($hide_scret !== false)
                unset($user['password_hash']);

            return $user;
        }
    }