<?php
    namespace Router;

    use Router\Config;
    use Router\Response;
    use Router\Request;
    use Router\Router;
    use Router\Controllers\RouteController;
    use Router\Models\UrlModel;
    use Router\Models\UrlTemplateModel;
    use Router\Models\RouteModel;
    use Exception;

    final class Overrides {
        protected static array $cache = [];
        protected static ?bool $enabled = null;
        protected static string $ovNamespace;
        protected static string $localNamespace;

        const ALLOW_OVERRIDE = [ 
            Response::class, 
            Request::class, 
            Router::class,
            Router::class,
            UrlModel::class,
            UrlTemplateModel::class,
            RouteController::class,
            RouteModel::class
        ];

        public static function get(string $base_class): string {
            if(!in_array($base_class, self::ALLOW_OVERRIDE))
                throw new Exception("Class '{$base_class}' can not be overridden.");

            if(!self::storeNamespaces()) return $base_class;

            if(array_key_exists($base_class, self::$cache))
                return self::$cache[$base_class] ?? $base_class;

            $override_class = self::replaceWithOvNamespace($base_class);

            if(!class_exists($override_class)) {
                self::$cache[$base_class] = null;
                return $base_class;
            }

            if(!is_subclass_of($override_class, $base_class)) {
                self::$cache[$base_class] = null;
                throw new Exception("Class '{$override_class}' is not a subclass of '{$base_class}'.");
            }

            self::$cache[$base_class] = $override_class;
            
            return $override_class;
        }

        protected static function storeNamespaces(): bool {
            if(self::$enabled === false) return false;
            
            $ov_namespace = Config::get('overrides.namespace');
            if(!is_string($ov_namespace)) {
                self::$enabled = false;
                return false;
            }

            self::$ovNamespace = trim($ov_namespace, '\\');
            self::$localNamespace = trim(__NAMESPACE__, '\\');
            self::$enabled = true;

            return true;
        }

        protected static function replaceWithOvNamespace(string $base_class) {
            return self::$ovNamespace.substr($base_class, strlen(self::$localNamespace));
        }

        protected static function joinToNamespace(...$parts): string {
            foreach ($parts as &$part) {
                $part = trim($part, '\\');
            }

            return '\\'.implode('\\', $parts);
        }
    }