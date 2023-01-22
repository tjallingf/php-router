<?php
    namespace Router\Models;

    use Router\Models\UrlTemplateModel;
    
    class UrlModel {
        protected string $path;
        protected array $valuesMap = [];

        public function __construct(string $path) {
            $this->path = $path;
            $this->valuesMap = $this->pathToValuesMap($path);
        }

        public function __toString(): string {
            return $this->path;
        }

        public function matchesTemplate(UrlTemplateModel $template): bool {
            $is_match = true;
            $i_max = max(count($template->getPartsMap()), count($this->getValuesMap()));

            for ($i=0; $i < $i_max; $i++) { 
                $template_part = $template->getPart($i);
                $value = $this->getValue($i);

                if(!$this->partMatches($template_part, $value)) {
                    $is_match = false;
                    break;
                }
            }

            return $is_match;
        }

        public function getValue(int $index) {
            return @$this->getValuesMap()[$index];
        }

        public function getValuesMap() {
            return $this->valuesMap;
        }

        public function isFile() {
            return (strpos($this->path, '.') !== false);
        }

        protected function pathToValuesMap(string $path): array {
            $values = explode('/', trim(strtok($path, '?'), '/'));            
            return $values;
        }

        protected function partMatches($template_part, $value): bool {
            if(!isset($template_part))
                return false;

            if(!$template_part['is_required']) 
                return true;
                
            // Remove the query string
            if(strlen($value) > 0)
                $value = strtok($value, '?');

            if(!isset($value) || $value === false)
                return false;
                
            if(isset($template_part['validation']['expect_value']) &&
               $template_part['validation']['expect_value'] != $value)
                return false;

            return true;
        }
    }