<?php
    namespace Router\Models;

    use Router\Models\RequestCookieModel;
    use Router\Models\Model;

    abstract class CookieModel extends Model {
        protected array $data = [
            'name'      => null,
            'value'     => null,
            'expires'   => null,
            'domain'    => null,
            'path'      => null,
            'secure'    => false,
            'httponly'  => false
        ];

        public function __construct(?array $data = null) {
            // Replace $this->data with options
            if(isset($data))
                $this->data = array_replace($this->data, $data);
            
            if($this instanceof RequestCookieModel && is_string($this->getValue()))
                $this->setValue($this->deserializeValue($this->getValue()));
        }

        public function getName(): string|null {
            return $this->data['name'];
        }

        public function setName(string $name): self {
            $this->data['name'] = $name;
            return $this;
        }

        public function getValue(): string|null {
            return $this->data['value'];
        }

        public function setValue(string $value): self {
            $this->data['value'] = $value;
            return $this;
        }

        public static function fromString(string $cookie_string): static {
            parse_str(str_replace(';', '&', $cookie_string), $attributes);
            $name = array_key_first($attributes);

            $data = [
                'name' => $name,
                'value' => $attributes[$name]
            ];

            foreach ($attributes as $key => $value) {
                $key = strtolower(trim($key));
                if(!in_array($key, self::VALID_ATTRIBUTES))
                    continue;

                if($key === 'expires')
                    $value = strtotime($value);

                if($value === false || $value === null) 
                    continue;

                $data[$key] = (in_array($key, self::BOOLEAN_ATTRIBUTES)) ? true : $value;
            }

            return new static($data);
        }

        public function __toString(): string {
            $string = "{$this->getName()}=".$this->serializeValue($this->getValue()).'; ';

            foreach ($this->data as $key => $value) {
                if($key == 'name' || $key == 'value' || is_null($value) || $value === false)
                    continue;

                if($key == 'expires') {
                    $string .= 'expires='.gmdate('D, d M Y H:i:s \G\M\T', $value).'; ';
                } else {
                    $string .= ($value === true ? $key : "$key=$value").'; ';
                }
            }

            return rtrim($string, '; ');
        }

        protected function serializeValue($value): string {
            if(is_object($value))
                return '';
            
            if(!is_array($value))
                return urlencode(strval($value));

            $serialized = self::SERIALIZE_PREFIX_JSON.json_encode($value);

            return urlencode($serialized);
        } 

        protected function deserializeValue(string $serialized) {
            $serialized = urldecode($serialized);
            if(!str_starts_with($serialized, self::SERIALIZE_PREFIX_JSON))
                return $serialized;

            return json_decode(substr($serialized, strlen(self::SERIALIZE_PREFIX_JSON)), true);
        }

        protected const VALID_ATTRIBUTES = ['expires', 'domain', 'path', 'secure', 'httponly'];
        protected const BOOLEAN_ATTRIBUTES = ['httponly', 'secure'];
        protected const SERIALIZE_PREFIX_JSON = 'application/json:';
    }