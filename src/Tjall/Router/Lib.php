<?php
    namespace Tjall\Router;

    class Lib {
        public static function joinPaths(...$paths) {
            return preg_replace('~[/\\\\]+~', '/', implode('/', $paths));
        }

        public static function relativePath(string $relative_to, string $path) {
            return substr(str_replace('\\', '/', realpath($path)), strlen(str_replace('\\', '/', realpath($relative_to))));
        }

        public static function arrayGetByPath(array $arr, string $path) {
            $path_exploded = explode('.', $path);

            $value = $arr;

            foreach ($path_exploded as $key) {
                $value = @$value[$key];
            }

            return $value;
        }

        public static function arraySetByPath(array &$arr, string $path, $data) {
            $path_exploded = explode('.', $path);

            $current = &$arr;
            foreach($path_exploded as $key) {
                $current = &$current[$key];
            }

            $current = $data;

            return $arr;
        }
    }