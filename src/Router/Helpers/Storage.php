<?php
    namespace Router\Helpers;

    use Router\Controllers\StorageController;

    class Storage {
        public static function get(string $path) {
            return StorageController::find($path);
        }

        public static function set(string $path, $value) {
            return StorageController::edit($path, $value);
        }
    }