<?php

namespace Mwakisha\Tarablade;

use DOMDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
            if ($image->src && !self::isRemoteUri($image->src)) {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceImagePath = $sourceTemplateDirectory . DIRECTORY_SEPARATOR . $image->src;
                $sourceImageDirectory = explode($sourceTemplateDirectory, $sourceImagePath)[1];

                if (
                    !File::exists(Tarablade::getPublicPath($sourceImageDirectory))
                    && File::exists($sourceImagePath)
                ) {
                    Tarablade::copy(
                        $sourceImagePath,
                        Tarablade::getPublicPath($sourceImageDirectory)
                    );
                }
            }
        }

        foreach ($html->find('link') as $favicon) {
            if (
                $favicon->href
                && !self::isRemoteUri($favicon->href)
                && ($favicon->rel == 'shortcut icon' || $favicon->rel == "icon")
            ) {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceImagePath = $sourceTemplateDirectory . DIRECTORY_SEPARATOR . $favicon->href;
                $sourceImageDirectory = explode($sourceTemplateDirectory, $sourceImagePath)[1];

                if (
                    !File::exists(Tarablade::getPublicPath($sourceImageDirectory))
                    && File::exists($sourceImagePath)
                ) {
                    Tarablade::copy(
                        $sourceImagePath,
                        Tarablade::getPublicPath($sourceImageDirectory)
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
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceStylePath = $sourceTemplateDirectory . DIRECTORY_SEPARATOR . $style->href;
                $sourceStyleDirectory = explode($sourceTemplateDirectory, $sourceStylePath)[1];

                if (
                    !File::exists(Tarablade::getPublicPath($sourceStyleDirectory))
                    && File::exists($sourceStylePath)
                ) {
                    self::parseCssForAssets($sourceStylePath, $templatePath);
                    Tarablade::copy(
                        $sourceStylePath,
                        Tarablade::getPublicPath($sourceStyleDirectory)
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
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceScriptPath = $sourceTemplateDirectory . DIRECTORY_SEPARATOR . $script->src;
                $sourceScriptDirectory = explode($sourceTemplateDirectory, $sourceScriptPath)[1];

                if (
                    !File::exists(Tarablade::getPublicPath($sourceScriptDirectory))
                    && File::exists($sourceScriptPath)
                ) {
                    Tarablade::copy(
                        $sourceScriptPath,
                        Tarablade::getPublicPath($sourceScriptDirectory)
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
                $absolutePath = Tarablade::getAbsolutePath(dirname($filePath) . DIRECTORY_SEPARATOR . $assetFilePath);
                $sourceAssetDirectory = ltrim(explode(Tarablade::getAbsolutePath(dirname($templatePath)), $absolutePath)[1], "\.\/\\");
                if (
                    !File::exists(Tarablade::getPublicPath($sourceAssetDirectory))
                    && File::exists($absolutePath)
                ) {
                    Tarablade::copy(
                        $absolutePath,
                        Tarablade::getPublicPath($sourceAssetDirectory)
                    );
                }
            }
        }
    }

    public static function createRoute($filepath)
    {
        $filename = Str::snake(pathinfo($filepath)['filename']);
        $routeName = Tarablade::getTemplateNamespace(). "." . $filename;
        $routePath = Tarablade::getTemplateNamespace(). "/" . $filename;
        $viewName = Tarablade::getTemplateNamespace().".".$filename;
        $routesFile = base_path("routes".DIRECTORY_SEPARATOR."web.php");

        // Orchestra testbench does not have the routes file, this makes the test pass
        if(!File::exists($routesFile)) {
            mkdir(base_path("routes"), 0777, true);
            $handle = fopen($routesFile,"a+");
            fwrite($handle,"<?php\n");
            fclose($handle);
        }

        $routes = file_get_contents($routesFile);

        if(strpos($routes, "->name('".$routeName."');") !== FALSE) {
            return $routeName;
        }
        
        $route = "Route::get('". $routePath ."', function () {return view('". $viewName ."');})->name('". $routeName ."');";
        file_put_contents($routesFile, $route.PHP_EOL , FILE_APPEND | LOCK_EX);

        return $routeName;
    }

    public static function convertToBladeTemplate($filePath)
    {
        $filename = Str::snake(pathinfo($filePath)['filename']) . ".blade.php";
        $outputFilepath = Tarablade::getViewsResourcePath($filename);
        if(File::exists($outputFilepath)) {
            return;
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = FALSE;
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile($filePath);
        libxml_clear_errors();
        $dom->formatOutput = TRUE;
        $dom->saveHTMLFile($filePath);
        
        Tarablade::copy($filePath, $outputFilepath);
        
        $html = DomParser::getHtml($outputFilepath);

        foreach ($html->find('img') as $image) {
            if ($image->src && !self::isRemoteUri($image->src)) {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($filePath));
                $sourceImagePath = $sourceTemplateDirectory . DIRECTORY_SEPARATOR . $image->src;
                $sourceImageDirectory = ltrim(explode($sourceTemplateDirectory, $sourceImagePath)[1], "\.\/\\");

                $oldMarkup = $image->outertext;
                $image->src = "{{asset('" . Tarablade::getTemplateNamespace($sourceImageDirectory) . "')}}";
                   
                self::replaceTextInFile($outputFilepath, $oldMarkup, $image->outertext);
            }
        }

        foreach ($html->find('link') as $link) {
            if ($link->href && !self::isRemoteUri($link->href)) {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($filePath));
                $sourceLinkPath = $sourceTemplateDirectory . DIRECTORY_SEPARATOR . $link->href;
                $sourceLinkDirectory = ltrim(explode($sourceTemplateDirectory, $sourceLinkPath)[1], "\.\/\\");

                $oldMarkup = $link->outertext;
                $link->href = "{{asset('" . Tarablade::getTemplateNamespace($sourceLinkDirectory) . "')}}";

                self::replaceTextInFile($outputFilepath, $oldMarkup, $link->outertext);
            }
        }

        foreach ($html->find('script') as $script) {
            if ($script->src && !self::isRemoteUri($script->src)) {
                $sourceTemplateDirectory = dirname(Tarablade::getAbsolutePath($filePath));
                $sourceScriptPath = $sourceTemplateDirectory . DIRECTORY_SEPARATOR . $script->src;
                $sourceScriptDirectory = ltrim(explode($sourceTemplateDirectory, $sourceScriptPath)[1], "\.\/\\");

                $oldMarkup = $script->outertext;
                $script->src = "{{asset('" . Tarablade::getTemplateNamespace($sourceScriptDirectory) . "')}}";

                self::replaceTextInFile($outputFilepath, $oldMarkup, $script->outertext);
            }
        }

        foreach ($html->find('a') as $anchorLink) {
            if (
                preg_match('/^(www|https|http)/', $anchorLink->href) === 0
                && $anchorLink->href != ''
                && $anchorLink->href != '#'
            ) {
                $templatePath = realpath(Tarablade::getAbsolutePath(dirname($filePath)
                    . DIRECTORY_SEPARATOR .
                    $anchorLink->href));

                if ($templatePath) {
                    $oldMarkup = $anchorLink->outertext;
                    $anchorLink->href = "{{route('". self::createRoute(basename($anchorLink->href)) ."')}}";

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
                $templatePath = realpath(Tarablade::getAbsolutePath(dirname($this->filename)
                    . DIRECTORY_SEPARATOR .
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
