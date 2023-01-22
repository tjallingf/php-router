<?php
    namespace Router\Models;

    use Router\Dom;
    use Router\Controllers\ComponentController;
    use PHPHtmlParser\Dom\Node\HtmlNode;
    use PHPHtmlParser\Dom\Node\AbstractNode;
    use Exception;

    class ComponentModel {
        protected array $data;
        protected string $path;

        public function __construct(string $path, array $data = []) {
            $this->path = $path;
            $this->data = $data; 
        }

        public function render(): string {
            $_PROPS = $this->data;

            ob_start();
            include($this->path);
            $output = ob_get_clean();

            $dom = $this->parseOutput($output);

            return trim($dom->__toString());
        }

        public static function parseOutput(string $output): HtmlNode {
            // // If the output doesn't contain any tag names starting with a
            // // capital letter, return the plain output.
            // preg_match('/<\s*[A-Z]{1}\w*/', $output, $matches);
            // if(!count($matches)) return $output;

            $dom = new Dom($output);
            
            if($dom->root instanceof HtmlNode) 
                self::parseChildNodes($dom->root);

            return $dom->root;
        }

        public static function parseChildNodes(HtmlNode &$parent_node): void {
            $children = $parent_node->getChildren();

            foreach ($children as $child) {
                if($child instanceof HtmlNode)
                    self::parseChildNodes($child);

                // Return if the tag name does not start with a capital letter
                if(!ctype_upper(substr($child->tag->name(), 0, 1))) 
                    continue;

                $component_output_node = (new Dom(self::parseComponentNode($child)))->root;

                // Return if the component did not output a node
                if(!$component_output_node instanceof AbstractNode) 
                    continue;

                $parent_node->replaceChild($child->id(), $component_output_node);
            }
        }

        public static function parseComponentNode($node) {
            $name = $node->tag->name();
            $props = self::getProps($node);

            $component = ComponentController::find($name, $props);

            if($component instanceof Exception)
                return '<span>'.$component->getMessage().'</span>';

            return $component->render();
        }

        /**
         * Finds the first child that is of type HtmlNode.
         */
        public static function getMainChild(HtmlNode $node) {
            foreach ($node->getChildren() as $child) {
                if($child instanceof HtmlNode)
                    return $child;
            }
        }

        /**
         * Creates a properties array for a given node.
         */
        public static function getProps(HtmlNode $node) {
            $props = $node->getAttributes();

            foreach ($props as $key => $value) {
                // Attributes with no value should be considered boolean
                // values, assign them the the value of 'true'.
                if(is_null($value)) 
                    $props[$key] = true;
            }

            $props['children'] = $node->innerHtml;

            return $props;
        }
    }