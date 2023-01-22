<?php
    namespace Router;

    use PHPHtmlParser\Options;

    class Dom extends \PHPHtmlParser\Dom {
        function __construct(string $html_string = '') {
            parent::__construct();
            parent::setOptions((new Options())
                ->setRemoveScripts(false)
                ->setRemoveStyles(false)
                ->setRemoveSmartyScripts(false)
                ->setPreserveLineBreaks(true));

            if(!empty($html_string))
                parent::loadStr($html_string);
        }
    }