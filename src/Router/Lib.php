<?php
    namespace Router;

    use Router\Loader;

    final class Lib {
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

        /* Source: https://github.com/mcaskill/php-html-build-attributes */
        public static function htmlBuildAttributes($attr, callable $callback = null) {
            if (is_object($attr) && !($attr instanceof \Traversable))
                $attr = get_object_vars($attr);

            if (!is_array($attr) || !count($attr))
                return '';

            $html = [];
            foreach ($attr as $key => $val) {
                if (is_string($key)) {
                    $key = trim($key);

                    if (strlen($key) === 0) {
                        continue;
                    }
                }

                if(is_object($val) && is_callable($val))
                    $val = $val();

                if (is_null($val))
                    continue;

                if (is_object($val)) {
                    if (is_callable([ $val, 'toArray' ])) {
                        $val = $val->toArray();
                    } elseif (is_callable([ $val, '__toString' ])) {
                        $val = strval($val);
                    }
                }

                if (is_bool($val)) {
                    if ($val)
                        $html[] = $key;

                    continue;
                } elseif (is_array($val)) {
                    $val = implode(' ', array_reduce($val, function ($tokens, $token) {
                        if (is_string($token)) {
                            $token = trim($token);

                            if (strlen($token) > 0) {
                                $tokens[] = $token;
                            }
                        } elseif (is_numeric($token)) {
                            $tokens[] = $token;
                        }

                        return $tokens;
                    }, []));

                    if (strlen($val) === 0) {
                        continue;
                    }
                } elseif (!is_string($val) && !is_numeric($val)) {
                    $val = json_encode($val, (JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
                }

                if (is_callable($callback)) {
                    $val = $callback($val);
                } else {
                    $val = htmlspecialchars($val, ENT_QUOTES);
                }

                $html[] = sprintf('%1$s="%2$s"', $key, $val);
            }

            return implode(' ', $html);
        }
    }