<?php
    namespace Router;

    use Router\Models\CookieModel;
    use Router\Config;
    use Exception;
    use stdClass;

    abstract class Message {
        private static $instance;
        private static bool $invokedExtend = false;
        public array $headers = [];
        public array $cookies = [];
        public array $body    = [];
        public stdClass $data;

        public function __construct() {            
            $this->data = new stdClass();
        }

        public function getCookie(string $name): CookieModel|null {
            return @$this->cookies[trim($name)];
        }

        public function getHeader(string $name): array {
            $name = strtolower(trim($name));
            $value = $this->headers[$name];

            return is_array($value) ? $value : [];
        }

        public function getHeaderLine(string $name, string $seperator = ', '): string {
            return implode($seperator, $this->getHeader($name));
        }

        public function hasHeader(string $name): bool {
            return !empty($this->getHeader($name));
        }

        public function getBodyAsString(): string {
            return implode("\r\n", $this->body);
        }

        public static function get(...$args) {
            if(!isset(static::$instance) || static::$instance::class != static::class) {
                static::$instance = new static(...$args);
            }

            if(!static::$invokedExtend && method_exists(static::$instance, 'extend')) {
                call_user_func([ static::$instance, 'extend']);
                static::$invokedExtend = true;
            }

            return static::$instance;
        }
    }