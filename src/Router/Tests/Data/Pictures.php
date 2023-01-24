<?php
    namespace Router\Tests\Data;

    use Router\Router;

    class Pictures {
        public static function get() {
            return [
                [
                    'id' => '6593b92bec06',
                    'description' => 'Dog',
                    'url' => '/pictures/6593b92bec06/'
                ],
                [
                    'id' => '7db408d32dcb',
                    'description' => 'Cat',
                    'url' => '/pictures/7db408d32dcb/'
                ]
            ];
        }

        public static function getOne(string $id) {
            return @array_column(self::get(), null, 'id')[$id];
        }
    }