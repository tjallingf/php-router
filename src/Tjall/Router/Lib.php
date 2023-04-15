<?php
    namespace Tjall\Router;

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

        public static function formatUrlPath(string $url_path, ?bool $leading_slash = true, ?bool $trailing_slash = false): string {
            $url_path = str_replace('public', '', $url_path);
            $url_path = trim(Lib::joinPaths($url_path), '/');
            return ($leading_slash ? '/' : '').$url_path.($trailing_slash ? '/' : '');
        }
        
        public static function getProjectDir(): string {
            $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
            $vendor_dir = dirname($reflection->getFileName(), 2);
            $project_root = dirname($vendor_dir, 1);

            return Lib::relativePath($_SERVER['DOCUMENT_ROOT'], $project_root);
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