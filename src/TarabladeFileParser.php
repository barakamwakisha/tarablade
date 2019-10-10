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

    public function importImagesFromAllTemplates()
    {
        $this->importImages($this->filename);

        $html = DomParser::getHtml($this->filename);

        foreach($html->find('a') as $anchorLink) {
            if(preg_match('/^(www|https|http)/', $anchorLink->href) === 0 && $anchorLink->href != ""  && $anchorLink->href != "#") {
                $this->importImages('/' . Tarablade::getAbsolutePath(dirname($this->filename).'/'.$anchorLink->href));
            }
        }
    }

    public function importImages($templatePath)
    {
        $html = DomParser::getHtml($templatePath);

        foreach ($html->find('img') as $image) {
            if (preg_match('/^(www|https|http)/', $image) === 0) {

                $sourceImageDirectory = dirname(Tarablade::getAbsolutePath($templatePath));
                $sourceImagePath = '/'. $sourceImageDirectory . '/' . $image->src;
                $sourceImageName = basename($sourceImagePath);

                if( ! File::exists(Tarablade::getImagesFolderPath(). '/'. $sourceImageName)) {
                    File::copy($sourceImagePath, Tarablade::getImagesFolderPath().'/'.$sourceImageName);
                }
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
