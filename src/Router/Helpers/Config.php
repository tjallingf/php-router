<?php
    namespace Router\Helpers;

    use Router\Controllers\ConfigController;

    class Config {
        static function get(string $key) {
            return ConfigController::find($key);
        }
    }