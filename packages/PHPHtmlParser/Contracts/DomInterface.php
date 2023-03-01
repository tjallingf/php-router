<?php

declare(strict_types=1);

namespace PHPHtmlParser\Contracts;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;

interface DomInterface
{
    public function loadFromFile(string $file, ?Options $options = null): Dom;

    public function loadStr(string $str, ?Options $options = null): Dom;

    public function setOptions(Options $options): Dom;

    public function find(string $selector, int $nth = null);
}
