<?php 
    namespace Router;

    use Router\Controllers\ViewController;

    class View {
        static function get(string $id, array $data = []) {
            if(!ViewController::scan($id))
                return new \Exception("Cannot find view '{$id}'.", 404);
                
            return ViewController::findAndConstruct($id, $data);
        }
    }
?>