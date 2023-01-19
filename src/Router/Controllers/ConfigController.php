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
            self::edit('router.baseUrl', 
                '/'.trim(Lib::joinPaths(self::find('router.baseUrl')), '/'));
        }

        public static function populate() {}
    }