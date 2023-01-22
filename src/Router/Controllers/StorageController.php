<?php
    namespace Router\Controllers;

    use Router\Controllers\Controller;

    use Router\Lib;

    class StorageController extends Controller {
        public static function index(): array {
            return [];
        }

        public static function find(string $path) {
            list($filepath, $keypath) = static::splitPath($path);
            if(!isset($filepath)) return null;

            $contents = @json_decode(file_get_contents($filepath), true);

            if(!isset($keypath))
                return $contents;

            return Lib::arrayGetByPath($contents, $keypath);
        }

        public static function edit(string $path, $value): string {
            list($filepath, $keypath, $filename) = static::splitPath($path);
            if(!isset($filepath)) return static::class;
            
            $dirpath = dirname($filepath);
            
            if(!is_dir($dirpath))
                mkdir($dirpath, 0777, true);
            
            if(!isset($keypath)) {
                $contents = $value;
            } else {
                $contents = (array) static::find($filename);
                Lib::arraySetByPath($contents, $keypath, $value);
            }

            file_put_contents($filepath, json_encode($contents));

            return static::class;
        }

        protected static function splitPath(string $item): array {
            $filename = strtok($item, '.');
            $keypath = substr($item, strlen($filename)+1);
            if(empty($keypath)) $keypath = null;

            $base_dir = Lib::joinPaths(Lib::getRootDir(), 'storage');
            $filepath = Lib::joinPaths($base_dir, $filename.'.json');

            // Don't allow $filepath to be outside of the $base_dir directory
            if(!str_starts_with($filepath, $base_dir))
                return [ null, null ];

            return [ $filepath, $keypath, $filename ];
        }
    }