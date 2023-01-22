<?php
    namespace Router\Tests\Data;

    class Pictures {
        protected static $data = [
            [
                'id' => '6593b92bec06',
                'description' => 'Dog'
            ],
            [
                'id' => '7db408d32dcb',
                'description' => 'Cat'
            ],
            [
                'id' => 'c5e462295a6c',
                'description' => 'Horse',
                'private' => true
            ]
        ];

        public static function get(bool $allow_private = false) {
            if($allow_private) return self::$data;

            return array_filter(self::$data, function($item) {
                return !!@$item['private'];
            });
        }

        public static function getOne(string $id, bool $allow_private = false) {
            return @array_column(self::get($allow_private), null, 'id')[$id];
        }
    }