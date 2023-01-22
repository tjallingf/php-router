<?php
    namespace Router;

    use Router\Models\CookieModel;
    use Router\Helpers\Overridable;
    use stdClass;

    abstract class Message extends Overridable {
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

        public function hasCookie(string $name): bool {
            return !is_null($this->getCookie($name)?->getValue());
        }

        public function getHeader(string $name): array {
            $name = strtolower(trim($name));
            $value = @$this->headers[$name];

            return is_array($value) ? $value : [];
        }

        public function getHeaderLine(string $name, string $seperator = ', '): string {
            return implode($seperator, $this->getHeader($name));
        }

        public function hasHeader(string $name): bool {
            return !empty($this->getHeader($name));
        }

        public function getBody(): string {
            return implode("\r\n", $this->body);
        }
    }