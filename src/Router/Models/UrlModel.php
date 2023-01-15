<?php
    namespace Router\Models;

    use Router\Models\UrlTemplateModel;

    class UrlModel {
        protected array $partsList = [];

        public function __construct(string $path) {
            $this->partsList = $this->toPartsList($path);
        }

        public function parseParameters(UrlTemplateModel $template) {
            $parameters = [];
            $url_parts = $this->partsList;
            $template_parts = $template->partsList;

            foreach ($template_parts as $index => $template_part) {
                // Return if the url doesn't have this many parts
                $url_part = @$url_parts[$index];
                if(!isset($url_part) && $template_part['is_required']) 
                    break;

                if(@$url_part['name'] != $template_part['name'] && !$template_part['is_parameter'])
                    break;

                if($template_part['is_parameter']) {
                    $parameters[$template_part['name']] = @$url_part['name'] ?? null;
                }
            }

            return $parameters;
        }

        public function matchesTemplate(UrlTemplateModel $template) {
            $is_match = true;
            $url_parts = $this->partsList;
            $template_parts = $template->partsList;

            for ($i=0; $i < max(count($template_parts), count($url_parts)); $i++) { 
                // Return if the template doesn't have this many parts
                $template_part = @$template_parts[$i];
                if(!isset($template_part)) {
                    $is_match = false;
                    break;
                }

                // Return if the url doesn't have this many parts
                $url_part = @$url_parts[$i];
                if(!isset($url_part) && @$template_part['is_required']) {
                    $is_match = false;
                    break;
                }

                if(@$url_part['name'] != @$template_part['name'] && @!$template_part['is_parameter']) {
                    $is_match = false;
                    break;
                }
            }

            return $is_match;
        }

        protected function trim(string $path) {
            return trim($path, '/');
        }

        protected function toPartsList(string $path) {
            $parts = explode('/', $this->trim($path));
            $parts_list = [];
            
            foreach ($parts as $index => $part) {
                // Query string should not be considered part of the path
                $part = strtok($part, '?');
                
                $parts_list[$index] = [
                    'name' => $part
                ];
            }

            return $parts_list;
        }
    }