<?php
    namespace Router;

    use Router\Loader;

    class Lib {
        public static function requireAll($dir) {
            foreach (self::recursiveGlob($dir, '*.php') as $file) require_once $file;
        }

        public static function recursiveGlob($base, $pattern, $flags = 0) {
            $flags = $flags & ~GLOB_NOCHECK;
            
            if (substr($base, -1) !== DIRECTORY_SEPARATOR) {
                $base .= DIRECTORY_SEPARATOR;
            }

            $files = glob($base.$pattern, $flags);
            if (!is_array($files)) {
                $files = [];
            }

            $dirs = glob($base.'*', GLOB_ONLYDIR|GLOB_NOSORT|GLOB_MARK);
            if (!is_array($dirs)) {
                return $files;
            }
            
            foreach ($dirs as $dir) {
                $dirFiles = self::recursiveGlob($dir, $pattern, $flags);
                $files = array_merge($files, $dirFiles);
            }

            return $files;
        }

        public static function joinPaths(...$paths) {
            return preg_replace('~[/\\\\]+~', '/', implode('/', $paths));
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
        
        public static function getRootDir() {       
            return Loader::getRootDir();
        }

        public static function getPackageDir() {
            return dirname(__FILE__, 3);
        }

        public static function getRelativeRootDir() {
            $document_root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
            
            return substr(self::getRootDir(), strlen($document_root));
        }
    }