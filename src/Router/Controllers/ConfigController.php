<?php
    namespace Router\Controllers;

    use Router\Controllers\Controller;

    class ConfigController extends Controller {
        static public function populate() {
            self::$data = json_decode(file_get_contents(APP_CONFIG_FILE), true) ?? [];
        }
    }