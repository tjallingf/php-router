<?php
    namespace Router;

    use Router\Controllers\ConfigController;

    class Config {
        static function get(string $key, $fallback = null) {
            return ConfigController::find($key) ?? $fallback;
        }
    }