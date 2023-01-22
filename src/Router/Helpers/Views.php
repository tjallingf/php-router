<?php 
    namespace Router\Helpers;

    use Router\Controllers\ViewController;

    class Views {
        static function find(string $name, array $data = []) {
            if(!ViewController::exists($name))
                return new \Exception("Cannot find view '{$name}'.", 404);
                
            return ViewController::find($name, $data);
        }
    }
?>