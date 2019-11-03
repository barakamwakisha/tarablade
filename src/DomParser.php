<?php

namespace Mwakisha\Tarablade;

use HungCP\PhpSimpleHtmlDom\HtmlDomParser;
use Illuminate\Support\Facades\File;

class DomParser
{
    public static function getHtml($filename)
    {
        return HtmlDomParser::file_get_html($filename);
    }
}
