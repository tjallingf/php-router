<?php
    if(!function_exists('str_contains')) {
        function str_contains(string $haystack, string $needle) {
            return empty($needle) || strpos($haystack, $needle) !== false;
        }
    }

    if(!function_exists('str_starts_with')) {
        function str_starts_with(string $haystack, string $needle) {
            return empty($needle) || strpos($haystack, $needle) === 0;
        }
    }

    if(!function_exists('str_ends_with')) {
        function str_ends_with(string $haystack, string $needle) {
            return empty($needle) || substr($haystack, -strlen($needle)) === $needle;
        }
    }