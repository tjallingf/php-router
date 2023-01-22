<?php
    namespace Router\Tests\Extensions;

    use PHPUnit\Runner\BeforeFirstTestHook;
    use Router\Loader;
    use Router\Lib;

    class Boot implements BeforeFirstTestHook {
        public function executeBeforeFirstTest(): void {
            $root_dir = Lib::joinPaths(dirname(__FILE__, 5), 'tests/suite');
            $config_file = Lib::joinPaths($root_dir, 'test_config.json');
            Loader::load($root_dir, json_decode(file_get_contents($config_file), true));
        }
    }