<?php 
    namespace Router;

    use Router\Controllers\ViewController;

    class View {
        static function get(string $name, array $data = []) {
            if(!ViewController::exists($name))
                return new \Exception("Cannot find view '{$name}'.", 404);
                
            return ViewController::find($name, $data);
        }
    }
?>