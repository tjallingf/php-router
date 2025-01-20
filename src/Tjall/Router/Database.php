<?php
    namespace Tjall\Router;

    use Tjall\Router\Config;

    class Database {
        public static \mysqli $mysqli;

        public static function connect() {
            $conf = Config::get('database');
            
            static::$mysqli = new \mysqli(
               $conf['hostname'],
               $conf['username'],
               $conf['password'],
               $conf['database'],
               @$conf['port']
            );

            $result = static::$mysqli->query("SELECT * FROM `form_submissions`");
        }
    }