<?php 
    namespace Router;

    use Router\Controllers\ViewController;
    use Router\Exception;

    class View {
        static function get(string $name, array $data = []) {               
            return ViewController::find($name, $data);
        }
    }
?>