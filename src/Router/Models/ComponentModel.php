<?php
    namespace Router\Models;

    use PHPHtmlParser\Dom;
    use PHPHtmlParser\Dom\Node\TextNode;
    use Router\Controllers\ComponentController;
    use PHPHtmlParser\Options;

    class ComponentModel {
        protected array $data;
        protected string $path;

        function __construct(string $path, array $data = []) {
            $this->path = $path;
            $this->data = $data; 
        }

        function render(): string {
            // The $_DATA variable can be used inside the component
            $_DATA = $this->data;

            ob_start();
            include($this->path);
            $output = ob_get_contents();
            ob_end_clean();

            $parsed_output = $this->parseComponents($output);

            return $parsed_output;
        }

        function parseComponents(string $output) {
            // If the output doesn't contain any tag names starting with a
            // capital letter, return the plain output.
            preg_match('/<\s*[A-Z]{1}\w*/', $output, $matches);
            if(!count($matches)) return $output;

            $dom = new Dom();
            $dom->setOptions((new Options())->setCleanupInput(false));
            $dom->loadStr($output);
            self::parseNode($dom->find('html'));

            return $dom->__toString();
        }

        function parseNode($parent_node) {
            if($parent_node instanceof TextNode)
                return $parent_node;

            $children = $parent_node->getChildren();
            
            foreach ($children as $child_node) {
                $child_node = self::parseNode($child_node);

                // Check if the tag name starts with a capital letter
                if(!ctype_upper(substr($child_node->tag->name(), 0, 1))) continue;

                $dom = new Dom();
                $dom->setOptions((new Options())->setCleanupInput(false));
                $dom->loadStr(self::parseComponentNode($child_node));
                
                $new_content = (count($dom->getChildren()) 
                    ? $dom->firstChild()->getParent() 
                    : new TextNode(''));

                $parent_node->replaceChild($child_node->id(), $new_content);
            }

            return $parent_node;
        }

        function parseComponentNode($node) {
            $name = $node->tag->name();
            $data = $node->getAttributes();
            $data['children'] = $node->innerHtml;

            $component = ComponentController::find($name, $data);

            return $component->render();
        }
    }