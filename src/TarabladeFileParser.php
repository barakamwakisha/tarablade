<?php

namespace Mwakisha\Tarablade;

use Illuminate\Support\Facades\File;

class TarabladeFileParser
{
    // TODO: Font import
    // TODO: Favicon import

    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public static function importImages($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('img') as $image) {
            if (preg_match('/^(www|https|http)/', $image->src) === 0) {

                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceImagePath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$image->src;
                $sourceImageDirectory = explode($sourceTemplateDirectory, $sourceImagePath)[1];

                if (!File::exists(Tarablade::getTemplateNamespace($sourceImageDirectory))
                    && File::exists($sourceImagePath)) {
                    Tarablade::copy($sourceImagePath,
                        Tarablade::getTemplateNamespace($sourceImageDirectory));
                }
            }
        }
    }

    public static function importStyles($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('link') as $style) {
            if($style->href
                && preg_match('/^(www|https|http)/', $style->href) === 0
                && $style->rel == "stylesheet") {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceStylePath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$style->href;
                $sourceStyleDirectory = explode($sourceTemplateDirectory, $sourceStylePath)[1];

                if (!File::exists(Tarablade::getTemplateNamespace($sourceStyleDirectory))
                    && File::exists($sourceStylePath)) {
                    Tarablade::copy($sourceStylePath,
                        Tarablade::getTemplateNamespace($sourceStyleDirectory));
                }
            }
        }
    }

    public static function importScripts($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('script') as $script) {
            if(preg_match('/^(www|https|http)/', $script->src) === 0 && $script->src) {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceScriptPath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$script->src;
                $sourceScriptDirectory = explode($sourceTemplateDirectory, $sourceScriptPath)[1];

                if (!File::exists(Tarablade::getTemplateNamespace($sourceScriptDirectory))
                    && File::exists($sourceScriptPath)) {
                    Tarablade::copy($sourceScriptPath,
                        Tarablade::getTemplateNamespace($sourceScriptDirectory));
                }
            }
        }
    }

    public function importAssetsFromAllTemplates()
    {
        self::importImages($this->filename);
        self::importStyles($this->filename);
        self::importScripts($this->filename);

        $html = DomParser::getHtml($this->filename);

        foreach ($html->find('a') as $anchorLink) {
            if (preg_match('/^(www|https|http)/', $anchorLink->href) === 0
                && $anchorLink->href != ''
                && $anchorLink->href != '#') {

                $templatePath = realpath(Tarablade::getAbsolutePath(dirname($this->filename)
                    .DIRECTORY_SEPARATOR.
                    $anchorLink->href));

                if($templatePath) {
                    self::importImages($templatePath);
                    self::importStyles($templatePath);
                    self::importScripts($templatePath);
                }
            }
        }
    }
}
