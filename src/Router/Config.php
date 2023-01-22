<?php
    namespace Router;

    use Router\Controllers\ConfigController;
    use Exception;

    final class Config {
        protected static array $overridesCache = [];

        public static function get(string $keypath, $fallback = null) {
            return ConfigController::find($keypath) ?? $fallback;
        }
    }