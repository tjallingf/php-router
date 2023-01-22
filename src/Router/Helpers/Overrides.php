<?php
    namespace Router\Helpers;

    use Router\Config;
    use Router\Helpers\Overridable;
    use Exception;

    final class Overrides {
        protected static array $cache = [];
        protected static ?bool $enabled = null;
        protected static string $localNamespace;
        protected static string $overridesNamespace;
        protected const APP_NAMESPACE = 'Router';

        public static function init(): bool {
            if(self::$enabled === false) return false;
            if(isset(self::$overridesNamespace)) return true;
            
            $overrides_namespace = trim(Config::get('overrides.namespace'), '\\');
            if(!is_string($overrides_namespace)) {
                self::$enabled = false;
                return false;
            }

            if(strtolower($overrides_namespace) == strtolower(self::APP_NAMESPACE)) {
                throw new Exception("Config field 'overrides.namespace' can not be '".self::APP_NAMESPACE."'.");
                return false;
            }

            self::$overridesNamespace = $overrides_namespace;
            self::$localNamespace = trim(self::APP_NAMESPACE, '\\');
            self::$enabled = true;

            return true;
        }

        public static function get(string $base_class): string {
            if(!is_subclass_of($base_class, Overridable::class))
                throw new Exception("Class '{$base_class}' can not be overridden.");

            // Return base class if overrides failed to initialize
            if(self::init() === false) 
                return $base_class;

            // Return from cache if item exists. If item is NULL, return base class.
            if(array_key_exists($base_class, self::$cache))
                return self::$cache[$base_class] ?? $base_class;

            // Get override class name
            $override_class = self::replaceNamespace($base_class);

            // Return base class if override class does not exist
            if(!class_exists($override_class)) {
                self::$cache[$base_class] = null;
                return $base_class;
            }

            // Throw exception if override class does not extend base class
            if(!is_subclass_of($override_class, $base_class)) {
                self::$cache[$base_class] = null;
                throw new Exception("Class '{$override_class}' is not a subclass of '{$base_class}'.");
            }

            // Store in cache for later use
            self::$cache[$base_class] = $override_class;
            
            return $override_class;
        }

        protected static function replaceNamespace(string $class) {
            return self::$overridesNamespace.substr($class, strlen(self::$localNamespace));
        }

        protected static function joinToNamespace(...$parts): string {
            foreach ($parts as &$part) {
                $part = trim($part, '\\');
            }

            return '\\'.implode('\\', $parts);
        }
    }