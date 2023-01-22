<?php 
    namespace Router\Controllers;

    use Router\Models\ComponentModel;
    use Router\Controllers\Controller;
    use Router\Lib;
    use Router\Exception;

    class ComponentController extends Controller {
        protected static string $dir = '/resources/components';
        protected static string $model = ComponentModel::class;

        protected static function getPath(string $name): string|null {
            $filename = lib::joinPaths(Lib::getRootDir(), static::$dir, $name);
            
            if(file_exists("$filename.php")) return "$filename.php";
            if(file_exists("$filename.html")) return "$filename.html";

            return null;
        }

        public static function find(string $name, array $data = []) {
            $filepath = static::getPath($name);
            
            if(!isset($filepath))
                throw new Exception('Cannot find '.static::TYPE_NAME. " '$name'", 404);

            return new (static::$model::getOverride())($filepath, $data);
        }

        protected const TYPE_NAME = 'component';
    }
?>