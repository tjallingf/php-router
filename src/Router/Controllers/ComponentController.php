<?php 
    namespace Router\Controllers;

    use Router\Models\ComponentModel;
    use Router\Controllers\Controller;
    use Router\Lib;
    use Exception;

    class ComponentController extends Controller {
        protected const DIR        = '/resources/components';
        protected const TYPE_NAME  = 'component';
        protected const MODEL      = ComponentModel::class;

        protected static array $data = [];

        public static function find(string $id) {
            $filename = Lib::joinPaths(Lib::getRootDir(), static::DIR, $id);
            
            if(file_exists("$filename.php")) return "$filename.php";
            if(file_exists("$filename.html")) return "$filename.html";
            
            return null;
        }

        public static function findAndConstruct(string $id, array $data = []) {
            if(!self::scan($id))
                return new Exception('Cannot find '.static::TYPE_NAME. " '$id'.",);

            $model = static::MODEL;
            return new $model(self::find($id), $data);
        }
    }
?>