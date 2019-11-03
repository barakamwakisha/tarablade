<?php

namespace Mwakisha\Tarablade;

use Illuminate\Support\Facades\File;

class TarabladeFileParser
{

    // TODO: Maintain folder structure when copying assets
    // TODO: Write tests for added code

    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function importImages($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('img') as $image) {
            if (preg_match('/^(www|https|http)/', $image->src) === 0) {
                $sourceImageDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceImagePath = $sourceImageDirectory.DIRECTORY_SEPARATOR.$image->src;
                $sourceImageName = basename($sourceImagePath);

                if (!File::exists(Tarablade::getImagesFolderPath().DIRECTORY_SEPARATOR.$sourceImageName)
                    && File::exists($sourceImagePath)) {
                    File::copy($sourceImagePath,
                        Tarablade::getImagesFolderPath().DIRECTORY_SEPARATOR.$sourceImageName);
                }
            }
        }
    }

    public function importStyles($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('link') as $style) {
            if($style->href
                && preg_match('/^(www|https|http)/', $style->href) === 0
                && $style->rel == "stylesheet") {
                $sourceStyleDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceStylePath = $sourceStyleDirectory.DIRECTORY_SEPARATOR.$style->href;
                $sourceStyleName = basename($sourceStylePath);

                if (!File::exists(Tarablade::getStylesFolderPath().DIRECTORY_SEPARATOR.$sourceStyleName)
                    && File::exists($sourceStylePath)) {
                    File::copy($sourceStylePath,
                        Tarablade::getStylesFolderPath().DIRECTORY_SEPARATOR.$sourceStyleName);
                }
            }
        }
    }

    public function importScripts($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('script') as $script) {
            if(preg_match('/^(www|https|http)/', $script->src) === 0 && $script->src) {
                $sourceScriptDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceScriptPath = $sourceScriptDirectory.DIRECTORY_SEPARATOR.$script->src;
                $sourceScriptName = basename($sourceScriptPath);

                if (!File::exists(Tarablade::getScriptsFolderPath().DIRECTORY_SEPARATOR.$sourceScriptName)
                    && File::exists($sourceScriptPath)) {
                    File::copy($sourceScriptPath,
                        Tarablade::getScriptsFolderPath().DIRECTORY_SEPARATOR.$sourceScriptName);
                }
            }
        }
    }

    public function importAssetsFromAllTemplates()
    {
        $this->importImages($this->filename);
        $this->importStyles($this->filename);
        $this->importScripts($this->filename);

        $html = DomParser::getHtml($this->filename);

        foreach ($html->find('a') as $anchorLink) {
            if (preg_match('/^(www|https|http)/', $anchorLink->href) === 0
                && $anchorLink->href != ''
                && $anchorLink->href != '#') {

                $templatePath = realpath(Tarablade::getAbsolutePath(dirname($this->filename)
                    .DIRECTORY_SEPARATOR.
                    $anchorLink->href));

                $this->importImages($templatePath);
                $this->importStyles($templatePath);
                $this->importScripts($templatePath);
            }
        }
    }

    public function getExternalResources()
    {
        $resources = [];
        $html = DomParser::getHtml($this->filename);
        // Find all <a> tags
        foreach ($html->find('a') as $element) {
            $resources[] = $element->href;
        }
        // Find all <img> tags
        foreach ($html->find('img') as $element) {
            $resources[] = $element->src;
        }
        // Find all <link> tags
        foreach ($html->find('link') as $element) {
            $resources[] = $element->href;
        }
        // Find all <script> tags
        foreach ($html->find('script') as $element) {
            $resources[] = $element->src;
        }

        return $resources;
    }
}
