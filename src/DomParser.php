<?php

namespace Mwakisha\Tarablade;

use HungCP\PhpSimpleHtmlDom\HtmlDomParser;

class DomParser
{
    public static function getHtml($filename)
    {
        return HtmlDomParser::file_get_html($filename);
    }
}
