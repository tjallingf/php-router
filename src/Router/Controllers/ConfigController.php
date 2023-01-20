<?php
    namespace Router\Controllers;

    use Router\Controllers\Controller;
    use Router\Lib;

    class ConfigController extends Controller {
        const DEFAULT = [
            'name' => 'My App',
            'development' => false,
            'client' => [
                'rootDir'                   => 'client/',
                'srcDir'                    => 'src/',
                'outDir'                    => '../public/static/dist/',
                'inputFile'                 => 'main.js',
                'port'                      => 5173,
                'buildCommand'              => 'npm run build',
                'startCommandWithInstall'   => 'npm run install-dev',
                'startCommand'              => 'npm run dev',
                'statusCheckEnabled'        => true
            ],
            'router' => [
                'baseUrl'                   => '/',
                'errorView'                 => 'error'
            ]
        ];
        
        static array $data = [];

        public static function store(array $config) {
            self::$data = array_replace_recursive(self::DEFAULT, $config);
            $formatted_base_url = '/'.trim(Lib::joinPaths(self::find('router.baseUrl')), '/');
            self::edit('router.baseUrl', strlen($formatted_base_url) > 1 ? $formatted_base_url : '');
        
            define('APP_MODE', in_array(self::find('mode'), ['dev', 'development', 'local'])
                ? 'dev' : 'prod');
            define('APP_MODE_DEV', APP_MODE === 'dev');
            define('APP_MODE_PROD', APP_MODE === 'prod');
        }

        public static function index(): ?array {
            return self::$data;
        }
    }