<?php 
    namespace Router\Controllers;

    use Router\Models\ComponentModel;
    use Router\Controllers\Controller;

    class ComponentController extends Controller {
        const TYPE = 'component';
        const DIR = 'components';
        const MODEL = ComponentModel::class;

        public static function exists(string $name): bool {
            return is_file(self::getPath($name));
        }

        protected static function getPath(string $name): string {
            // return @self::index()[$name];
            return root_dir().'/resources/'.static::DIR."/$name.php";
        }

        public static function populate() {
            $files = glob(root_dir().'/resources/'.static::DIR."/*.{php,html}", GLOB_BRACE);
            var_dump($files);
        }

        public static function find(string $name, array $data = []) {
            if(!self::exists($name))
                throw new \Exception('Cannot find '.self::TYPE. " '$name'.");

            $model = self::MODEL;
            return new $model(self::getPath($name), $data);
        }
    }
?>