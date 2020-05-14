<?php

namespace Mwakisha\Tarablade\Parsers;

use HungCP\PhpSimpleHtmlDom\HtmlDomParser;
use Illuminate\Support\Facades\File;

class DomParser
{
    public static function getHtml($filename)
    {
        return File::exists($filename) ? HtmlDomParser::file_get_html($filename)
                                    : HtmlDomParser::str_get_html($filename);
    }
}
