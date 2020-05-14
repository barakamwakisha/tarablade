<?php

namespace Mwakisha\Tarablade\Parsers;

use DOMDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mwakisha\Tarablade\Tarablade;
use Mwakisha\Tarablade\Util\FileSystemUtils;

class TarabladeFileParser
{
    protected $filename;

    private static $pathUtils;

    private static $fsUtils;


    public function __construct($filename)
    {
        $this->filename = $filename;
        self::$pathUtils = Tarablade::getInstance()->pathUtils;
        self::$fsUtils = Tarablade::getInstance()->fsUtils;
    }

    public static function importImages($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('img') as $image) {
            if ($image->src && !self::isRemoteUri($image->src)) {
                $sourceTemplateDirectory = dirname(self::$pathUtils->getAbsolutePath($templatePath));
                $sourceImagePath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$image->src;
                $sourceImageDirectory = explode($sourceTemplateDirectory, $sourceImagePath)[1];

                if (
                    !File::exists(self::$pathUtils->getPublicPath($sourceImageDirectory))
                    && File::exists($sourceImagePath)
                ) {
                    self::$fsUtils->copy(
                        $sourceImagePath,
                        self::$pathUtils->getPublicPath($sourceImageDirectory)
                    );
                }
            }
        }

        foreach ($html->find('link') as $favicon) {
            if (
                $favicon->href
                && !self::isRemoteUri($favicon->href)
                && ($favicon->rel == 'shortcut icon' || $favicon->rel == 'icon')
            ) {
                $sourceTemplateDirectory = dirname(self::$pathUtils->getAbsolutePath($templatePath));
                $sourceImagePath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$favicon->href;
                $sourceImageDirectory = explode($sourceTemplateDirectory, $sourceImagePath)[1];

                if (
                    !File::exists(self::$pathUtils->getPublicPath($sourceImageDirectory))
                    && File::exists($sourceImagePath)
                ) {
                    self::$fsUtils->copy(
                        $sourceImagePath,
                        self::$pathUtils->getPublicPath($sourceImageDirectory)
                    );
                }
            }
        }
    }

    public static function importStyles($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('link') as $style) {
            if (
                $style->href
                && !self::isRemoteUri($style->href)
                && $style->rel == 'stylesheet'
            ) {
                $sourceTemplateDirectory = dirname(self::$pathUtils->getAbsolutePath($templatePath));
                $sourceStylePath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$style->href;
                $sourceStyleDirectory = explode($sourceTemplateDirectory, $sourceStylePath)[1];

                if (
                    !File::exists(self::$pathUtils->getPublicPath($sourceStyleDirectory))
                    && File::exists($sourceStylePath)
                ) {
                    self::parseCssForAssets($sourceStylePath, $templatePath);
                    self::$fsUtils->copy(
                        $sourceStylePath,
                        self::$pathUtils->getPublicPath($sourceStyleDirectory)
                    );
                }
            }
        }
    }

    public static function importScripts($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('script') as $script) {
            if ($script->src && !self::isRemoteUri($script->src)) {
                $sourceTemplateDirectory = dirname(self::$pathUtils->getAbsolutePath($templatePath));
                $sourceScriptPath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$script->src;
                $sourceScriptDirectory = explode($sourceTemplateDirectory, $sourceScriptPath)[1];

                if (
                    !File::exists(self::$pathUtils->getPublicPath($sourceScriptDirectory))
                    && File::exists($sourceScriptPath)
                ) {
                    self::$fsUtils->copy(
                        $sourceScriptPath,
                        self::$pathUtils->getPublicPath($sourceScriptDirectory)
                    );
                }
            }
        }
    }

    public static function parseCssForAssets($filePath, $templatePath)
    {
        $content = file_get_contents($filePath);
        preg_match_all(
            '/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i',
            $content,
            $matches,
            PREG_PATTERN_ORDER
        );

        if ($matches) {
            foreach ($matches[3] as $match) {
                if (self::isRemoteUri($match)) {
                    continue;
                }

                $assetFilePath = strpos(basename($match), '?') ? explode('?', $match)[0] : $match;
                $absolutePath = self::$pathUtils->getAbsolutePath(dirname($filePath).DIRECTORY_SEPARATOR.$assetFilePath);
                $sourceAssetDirectory = ltrim(explode(self::$pathUtils->getAbsolutePath(dirname($templatePath)), $absolutePath)[1], "\.\/\\");
                if (
                    !File::exists(self::$pathUtils->getPublicPath($sourceAssetDirectory))
                    && File::exists($absolutePath)
                ) {
                    self::$fsUtils->copy(
                        $absolutePath,
                        self::$pathUtils->getPublicPath($sourceAssetDirectory)
                    );
                }
            }
        }
    }

    public static function createRoute($filepath)
    {
        $filename = Str::snake(pathinfo($filepath)['filename']);
        $routeName = ltrim(self::$pathUtils->getTemplateNamespace(), "\.\/\\").'.'.$filename;
        $routePath = self::$pathUtils->getTemplateNamespace().'/'.$filename;
        $viewName = self::$pathUtils->getTemplateNamespace().'.'.$filename;
        $routesFile = base_path('routes'.DIRECTORY_SEPARATOR.'web.php');

        // Orchestra testbench does not have the routes file, this makes the test pass
        if (!File::exists($routesFile)) {
            mkdir(base_path('routes'), 0777, true);
            $handle = fopen($routesFile, 'a+');
            fwrite($handle, "<?php\n");
            fclose($handle);
        }

        $routes = file_get_contents($routesFile);

        if (strpos($routes, "->name('".$routeName."');") !== false) {
            return $routeName;
        }

        $route = "Route::get('".$routePath."', function () {return view('".$viewName."');})->name('".$routeName."');";
        file_put_contents($routesFile, $route.PHP_EOL, FILE_APPEND | LOCK_EX);

        return $routeName;
    }

    public static function convertToBladeTemplate($filePath)
    {
        $filename = Str::snake(pathinfo($filePath)['filename']).'.blade.php';
        $outputFilepath = self::$pathUtils->getViewsResourcePath($filename);
        if (File::exists($outputFilepath)) {
            return;
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile($filePath);
        libxml_clear_errors();
        $dom->formatOutput = true;
        $dom->saveHTMLFile($filePath);

        self::$fsUtils->copy($filePath, $outputFilepath);

        $html = DomParser::getHtml($outputFilepath);

        foreach ($html->find('img') as $image) {
            if ($image->src && !self::isRemoteUri($image->src)) {
                $sourceTemplateDirectory = dirname(self::$pathUtils->getAbsolutePath($filePath));
                $sourceImagePath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$image->src;
                $sourceImageDirectory = ltrim(explode($sourceTemplateDirectory, $sourceImagePath)[1], "\.\/\\");

                $oldMarkup = $image->outertext;
                $image->src = "{{asset('".self::$pathUtils->getTemplateNamespace($sourceImageDirectory)."')}}";

                self::replaceTextInFile($outputFilepath, $oldMarkup, $image->outertext);
            }
        }

        foreach ($html->find('link') as $link) {
            if ($link->href && !self::isRemoteUri($link->href)) {
                $sourceTemplateDirectory = dirname(self::$pathUtils->getAbsolutePath($filePath));
                $sourceLinkPath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$link->href;
                $sourceLinkDirectory = ltrim(explode($sourceTemplateDirectory, $sourceLinkPath)[1], "\.\/\\");

                $oldMarkup = $link->outertext;
                $link->href = "{{asset('".self::$pathUtils->getTemplateNamespace($sourceLinkDirectory)."')}}";

                self::replaceTextInFile($outputFilepath, $oldMarkup, $link->outertext);
            }
        }

        foreach ($html->find('script') as $script) {
            if ($script->src && !self::isRemoteUri($script->src)) {
                $sourceTemplateDirectory = dirname(self::$pathUtils->getAbsolutePath($filePath));
                $sourceScriptPath = $sourceTemplateDirectory.DIRECTORY_SEPARATOR.$script->src;
                $sourceScriptDirectory = ltrim(explode($sourceTemplateDirectory, $sourceScriptPath)[1], "\.\/\\");

                $oldMarkup = $script->outertext;
                $script->src = "{{asset('".self::$pathUtils->getTemplateNamespace($sourceScriptDirectory)."')}}";

                self::replaceTextInFile($outputFilepath, $oldMarkup, $script->outertext);
            }
        }

        foreach ($html->find('a') as $anchorLink) {
            if (
                preg_match('/^(www|https|http)/', $anchorLink->href) === 0
                && $anchorLink->href != ''
                && $anchorLink->href != '#'
            ) {
                $templatePath = realpath(self::$pathUtils->getAbsolutePath(dirname($filePath)
                    .DIRECTORY_SEPARATOR.
                    $anchorLink->href));

                if ($templatePath) {
                    $oldMarkup = $anchorLink->outertext;
                    $anchorLink->href = "{{route('".self::createRoute(basename($anchorLink->href))."')}}";

                    self::replaceTextInFile($outputFilepath, $oldMarkup, $anchorLink->outertext);
                }
            }
        }
    }

    public function importAssetsFromAllTemplates()
    {
        self::importImages($this->filename);
        self::importStyles($this->filename);
        self::importScripts($this->filename);
        self::convertToBladeTemplate($this->filename);

        $html = DomParser::getHtml($this->filename);

        foreach ($html->find('a') as $anchorLink) {
            if (
                preg_match('/^(www|https|http)/', $anchorLink->href) === 0
                && $anchorLink->href != ''
                && $anchorLink->href != '#'
            ) {
                $templatePath = realpath(self::$pathUtils->getAbsolutePath(dirname($this->filename)
                    .DIRECTORY_SEPARATOR.
                    $anchorLink->href));

                if ($templatePath) {
                    self::importImages($templatePath);
                    self::importStyles($templatePath);
                    self::importScripts($templatePath);
                    self::convertToBladeTemplate($templatePath);
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

    public static function replaceTextInFile($filepath, $originalText, $newText)
    {
        $str = file_get_contents($filepath);
        $str = str_replace($originalText, $newText, $str);
        file_put_contents($filepath, $str);
    }
}
