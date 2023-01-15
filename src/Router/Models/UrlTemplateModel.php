<?php
    namespace Router\Models;

    class UrlTemplateModel {
        public array $partsList = [];

        public function __construct(string $template_path) {
            $this->partsList = $this->toPartsList($template_path);
        }

        protected function trim(string $template_path) {
            return trim($template_path, '/');
        }

        protected function toPartsList(string $template_path) {
            $parts = explode('/', $this->trim($template_path));
            $parts_list = [];
            
            foreach ($parts as $index => $part) {
                $is_parameter = (str_starts_with($part, '{') && str_ends_with($part, '}'));
                $part_name = ltrim(rtrim($part, '?}'), '{');
                $is_required = ($is_parameter ? ($is_parameter && !str_ends_with(rtrim($part, '}'), '?')) : true);

                $parts_list[$index] = [
                    'is_parameter' => $is_parameter,
                    'is_required' => $is_required,
                    'name' => $part_name
                ];
            }

            return $parts_list;
        }
    }