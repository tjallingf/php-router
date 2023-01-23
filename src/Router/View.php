<?php 
    namespace Router;

    use Router\Controllers\ViewController;

    class View {
        static function get(string $name, array $data = []) {               
            return ViewController::find($name, $data);
        }
    }
?>