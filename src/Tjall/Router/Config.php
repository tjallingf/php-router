<?php
    namespace Tjall\Router;

    use Tjall\Router\Router;
    use Tjall\Router\Lib;

    class Config {
        protected static array $config;

        public static function store(?array $config = []) {
            static::$config = array_replace_recursive(static::DEFAULT, $config);
            static::handleBasePath();
            static::handleRootDir();
        }

        public static function set(string $path, $value) {
            return Lib::arraySetByPath(static::$config, $path, $value);
        }

        public static function get(string $path) {
            return Lib::arrayGetByPath(static::$config, $path);
        }

        protected static function handleBasePath() {
            $base_path = '/'.trim(static::get('router.base_path'), '/');
            Router::$router->setBasePath($base_path);
            static::set('router.base_path', $base_path);
        }

        protected static function handleRootDir() {
            $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
            $rootDir = dirname($reflection->getFileName(), 3);
            static::set('rootDir', $rootDir);
        }

        protected const DEFAULT = [
            'mode'      => 'prod',
            'views' => [
                'dir' => 'views'
            ],
            'router' => [
                'basePath' => '/'
            ],
            'vite' => [
                'mode'    => 'dev',
                'srcDir'  => 'client/src',
                'outDir'  => 'public/static/dist',
                'input'   => 'main.jsx',
                'devPort' => 5173
            ]
        ];
    }