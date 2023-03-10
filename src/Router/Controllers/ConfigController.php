<?php
    namespace Router\Controllers;

    use Router\Controllers\Controller;
    use Router\Lib;

    final class ConfigController extends Controller {
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
            ],
            'overrides' => [
                'namespace'                 => null
            ]
        ];
        
        static array $data = [];

        public static function store(array $data): void {
            $formatted_base_url = trim(Lib::joinPaths(Lib::arrayGetByPath($data, 'router.baseUrl')), '/');
            $formatted_base_url = strlen($formatted_base_url) == 0 ? '' : '/'.$formatted_base_url;
            Lib::arraySetByPath($data, 'router.baseUrl', $formatted_base_url);

            self::$data = array_replace_recursive(self::DEFAULT, $data);
        }

        public static function edit(string $keypath, $value): void {
            return;
        }

        public static function find(string $keypath) {
            return Lib::arrayGetByPath(self::$data, $keypath);
        }

        public static function index(): array {
            return self::$data;
        }
    }