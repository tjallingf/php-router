<?php 
    namespace Router\Controllers;

    use Router\Models\ComponentModel;
    use Router\Controllers\Controller;
    use Router\Lib;
    use Exception;

    class ComponentController extends Controller {
        public static string $dir = '/resources/components';
        const TYPE = 'component';
        const MODEL = ComponentModel::class;

        public static function exists(string $name): bool {
            return is_file(self::getPath($name));
        }

        protected static function getPath(string $name): string {
            $filename = lib::joinPaths(Lib::getRootDir(), static::$dir, $name);
            
            if(file_exists("$filename.php")) return "$filename.php";
            return "$filename.html";
        }

        public static function find(string $name, array $data = []) {
            if(!self::exists($name))
                return new Exception('Cannot find '.static::TYPE. " '$name'.",);

            $model = static::MODEL;
            return new $model(self::getPath($name), $data);
        }

        public static function populate() {}
    }
?>