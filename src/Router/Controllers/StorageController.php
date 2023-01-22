<?php
    namespace Router\Controllers;

    use Router\Controllers\Controller;

    use Router\Lib;

    class StorageController extends Controller {
        public static array $data = [];

        public static function find(string $path) {
            list($filepath, $keypath) = self::splitPath($path);
            if(!isset($filepath)) return null;

            $contents = self::getContents($filepath);
            if(!isset($keypath)) return $contents;

            return Lib::arrayGetByPath($contents, $keypath);
        }

        public static function edit(string $path, $value): static {
            list($filepath, $keypath) = self::splitPath($path);
            if(!isset($filepath)) return new static();

            $contents = self::getContents($filepath);

            if(isset($keypath)) {
                Lib::arraySetByPath($contents, $keypath, array_replace_recursive(
                    Lib::arrayGetByPath($contents, $keypath), Lib::arrayGetByPath($value, $keypath)));
            } else {
                $contents = array_replace_recursive($contents, $value);
            }

            self::setContents($filepath, $contents);

            return new static();
        }

        public static function update(string $path, $value): static {
            list($filepath, $keypath) = self::splitPath($path);
            if(!isset($filepath)) return new static();

            if(isset($keypath)) {
                $contents = self::getContents($filepath);
                Lib::arraySetByPath($contents, $keypath, Lib::arrayGetByPath($value, $keypath));
            } else {
                $contents = $value;
            }
            
            self::setContents($filepath, $contents);

            return new static();
        }

        protected static function setContents(string $filepath, array $contents): void {            
            $dirpath = dirname($filepath);
            
            if(!is_dir($dirpath))
                mkdir($dirpath, 0777, true);
        }

        protected static function getContents(string $filepath): array {
            if(!is_null(self::find($filepath)))
                return self::find($filepath);
            
            $contents = @json_decode(file_get_contents($filepath), true);
            self::update($filepath, $contents);

            return $contents ?? []; 
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

            return [ $filepath, $keypath ];
        }
    }