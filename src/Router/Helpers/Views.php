<?php 
    namespace Router\Helpers;

    use Router\Controllers\ViewController;

    class Views {
        static function find(string $name, array $data = []) {
            return ViewController::find($name, $data);
        }
    }
?>