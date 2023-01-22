<?php
    namespace Router\Models;

    use Router\Helpers\ClientPreamble;
    use PHPHtmlParser\Dom\Node\HtmlNode;

    class ViewModel extends ComponentModel {
        public static bool $preambleInjected = false;

        public function render(): string {
            $output = parent::render();
            return "<!DOCTYPE html>\r\n".$output;
        }

        public static function parseOutput(string $output): HtmlNode {
            $node = parent::parseOutput($output);

            $head = $node->find('head')[0];
            $body = $node->find('body')[0];
            if(!self::$preambleInjected) {
                self::$preambleInjected = true;

                // Add head preambless to start of <head>
                if($head instanceof HtmlNode)
                    $head->insertBefore(ClientPreamble::toNode(ClientPreamble::getHeadCode()), $head->firstChild()->id());

                // Add body preambles to end of <body>
                if($body instanceof HtmlNode)
                    $body->addChild(ClientPreamble::toNode(ClientPreamble::getBodyCode()));
            }

            return $node;
        }
    }