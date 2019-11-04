<?php

namespace Mwakisha\Tarablade;

use Illuminate\Support\Facades\File;

class TarabladeFileParser
{
    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public static function importImages($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('img') as $image) {
            if (!self::isRemoteUri($image->src)) {
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

        foreach ($html->find('link') as $favicon) {
            if ($favicon->href
                && !self::isRemoteUri($favicon->href)
                && $favicon->rel == 'shortcut icon') {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceImagePath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$favicon->href;
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
            if ($style->href
                && !self::isRemoteUri($style->href)
                && $style->rel == 'stylesheet') {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceStylePath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$style->href;
                $sourceStyleDirectory = explode($sourceTemplateDirectory, $sourceStylePath)[1];

                if (!File::exists(Tarablade::getTemplateNamespace($sourceStyleDirectory))
                    && File::exists($sourceStylePath)) {
                    self::parseCssForAssets($sourceStylePath);
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
            if ($script->src && !self::isRemoteUri($script->src)) {
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

    public static function parseCssForAssets($filePath)
    {
        $content = file_get_contents($filePath);
        preg_match_all('/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i', $content,
            $matches,
            PREG_PATTERN_ORDER);

        if ($matches) {
            foreach ($matches[3] as $match) {
                if (self::isRemoteUri($match)) {
                    continue;
                }

                $assetFilePath = strpos(basename($match), '?') ? explode('?', $match)[0] : $match;
                $absolutePath = Tarablade::getAbsolutePath(dirname($filePath).DIRECTORY_SEPARATOR.$assetFilePath);
                $sourceAssetDirectory = ltrim(explode($absolutePath, $assetFilePath)[0], "\.\/\\");

                if (!File::exists(Tarablade::getTemplateNamespace($sourceAssetDirectory))
                    && File::exists($absolutePath)) {
                    Tarablade::copy($absolutePath,
                        Tarablade::getTemplateNamespace($sourceAssetDirectory));
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

                if ($templatePath) {
                    self::importImages($templatePath);
                    self::importStyles($templatePath);
                    self::importScripts($templatePath);
                }
            }
        }
    }

    public static function isRemoteUri($uri)
    {
        if (preg_match('/^(www|https|http)/', $uri) === 0) {
            return false;
        }

        return true;
    }
}
