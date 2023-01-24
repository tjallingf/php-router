<?php
    namespace Router\Models;

    use Router\Models\Model;

    class UrlPathTemplateModel extends Model {
        protected array $partsMap = [];

        public function __construct(string $path) {
            $this->partsMap = $this->pathToPartsMap($path);
        }

        public function __toString(): string {
            $string = '';

            foreach ($this->partsMap as $part) {
                if($part['type'] == 'parameter') {
                    $name = '{'.$part['parameter_name'].($part['is_required'] ? '' : '?').'}';
                } else {
                    $name = $part['validation']['expect_value'];
                }

                $string .= $name.'/';
            }

            return '/'.rtrim($string, '/');
        }

        public function getPartsMap() {
            return $this->partsMap;
        }

        public function getPart($index) {
            return @$this->getPartsMap()[$index];
        }

        protected function pathToPartsMap(string $template_path) {
            $items = preg_split('/\/+/', trim($template_path, '/'));
            $map = [];
            
            foreach ($items as $index => $item) {
                $item = trim($item);

                // Determine whether the item is an url {parameter}
                $is_parameter = (str_starts_with($item, '{') && str_ends_with($item, '}'));

                // Determine whether the part is required to exist in 
                // order for an url to match
                $is_required = $is_parameter 
                    ? !str_ends_with(rtrim($item, '}'), '?')
                    : true;

                $parameter_name = $is_parameter 
                    ? rtrim(ltrim($item, '{'), '?}')
                    : null;

                $part = [
                    'type' => $is_parameter 
                        ? 'parameter' 
                        : 'text',
                    'is_required' => $is_required,
                    'parameter_name' => $parameter_name,
                    'validation'  => [
                        'expect_value' => $is_parameter 
                            ? null 
                            : $item
                    ]
                ];



                $map[$index] = $part;
            }

            return $map;
        }
    }