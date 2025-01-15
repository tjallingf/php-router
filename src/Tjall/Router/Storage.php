<?php

namespace Tjall\Router;

use Tjall\Router\Lib;
use Tjall\Router\Config;

class Storage {
    public static function scan(string $dir, callable $callback) {
        $pattern = static::filepath([$dir, '*']);
        $filepaths = glob($pattern);

        return array_map(function ($filepath) use ($callback) {
            $id = pathinfo($filepath, PATHINFO_FILENAME);
            return $callback($id);
        }, $filepaths);
    }

    public static function filepath(string|array $path) {
        $path = is_array($path) ? $path : [$path];
        return Lib::joinPaths(Config::get('rootDir'), 'storage/'.implode('/', $path).'.json');
    }

    public static function get(string|array $path) {
        $contents = self::getContents(static::filepath($path));
        return $contents;
    }

    public static function set(string|array $path, $contents): static {
        self::setContents(static::filepath($path), $contents);
        return new static();
    }

    protected static function setContents(string $filepath, array $contents): void {
        $dirpath = dirname($filepath);

        if (!is_dir($dirpath))
            mkdir($dirpath, 0777, true);

        file_put_contents($filepath, json_encode($contents));
    }

    protected static function getContents(string $filepath): array {
        $contents = @json_decode(file_get_contents($filepath), true);

        return $contents ?? [];
    }

    protected static function splitPath(string $item): array {
        $filename = strtok($item, '.');
        $keypath = substr($item, strlen($filename) + 1);
        if (empty($keypath)) $keypath = null;

        $base_dir = Lib::joinPaths(Config::get('rootDir'), 'storage');
        $filepath = Lib::joinPaths($base_dir, $filename . '.json');

        // Don't allow $filepath to be outside of the $base_dir directory
        if (!str_starts_with($filepath, $base_dir))
            return [null, null];

        return [$filepath, $keypath];
    }
}
