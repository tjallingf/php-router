<?php 
    namespace Router\Models;

    use Router\Lib;

    class ComponentPropsModel {
        protected array $data;

        public function __construct(array $data) {
            // Remap keys from kebab-case to underscore_case
            $keys = array_keys($data);
            foreach($keys as &$key) $key = str_replace('-', '_', $key);

            $this->data = array_combine($keys, array_values($data));
        }

        public function omit(array $omit_keys): ComponentPropsModel {
            $data = array_diff_key($this->data, array_flip($omit_keys));
            return new static($data);
        }

        public function all(): array {
            return $this->data;
        }

        public function __toString(): string {
            $data = $this->omit(['children'])->all();
            return Lib::htmlBuildAttributes($data);
        }

        public function __get(string $name) {
            return @$this->data[$name];
        }
    }
?>