<?php
    namespace Router\Controllers;

    use Router\Controllers\Controller;
    use Router\Lib;

    class ConfigController extends Controller {
        protected const DEFAULT = [
            'name'                => 'My App',
            'mode'               => 'prod',
            'client' => [
                'rootDir'                   => 'client/',
                'srcDir'                    => 'src/',
                'outDir'                    => '../public/static/dist/',
                'inputFile'                 => 'main.js',
                'port'                      => 5173,
                'startCommandWithInstall'   => 'npm run install-dev',
                'startCommand'              => 'npm run dev',
                'statusCheckEnabled'        => true,
                'developmentModeEnabled'    => false
            ],
            'router' => [
                'baseUrl'                   => '/',
                'errorView'                 => 'error'
            ],
            'overrides' => [
                'enabled'                   => false,
                'namespace'                 => null
            ]
        ];

        public static function find(string $keypath) {
            return @Lib::arrayGetByPath(self::$data, $keypath);
        }
        
        public static function store(array $config): static {
            self::$data = array_replace_recursive(self::DEFAULT, $config);
            
            $base_url_formatted = '/'.trim(Lib::joinPaths(self::find('router.baseUrl')), '/');
            self::edit('router', ['baseUrl' => strlen($base_url_formatted) > 1 ? $base_url_formatted : '']);
            
            $app_mode_dev = (in_array(self::find('mode'), ['dev', 'development', 'local']));
            self::edit('mode', $app_mode_dev ? 'dev' : 'prod');

            define('APP_MODE', self::find('mode'));
            define('APP_MODE_DEV', $app_mode_dev);
            define('APP_MODE_PROD', !$app_mode_dev);

            return new static();
        }

        public static function index(): array {
            return self::$data;
        }
    }