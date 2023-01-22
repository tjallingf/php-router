<?php
    namespace Router\Tests\Extensions;

    class TestCase extends \PHPUnit\Framework\TestCase {
        public static function captureOutputString(callable $callback) {
            ob_start();
            call_user_func($callback);
            $output = ob_get_clean();

            return $output;
        }

        public static function captureOutputJSON(callable $callback) {
            $string = self::captureOutputString($callback);
            return @json_decode($string, true);
        }
    }