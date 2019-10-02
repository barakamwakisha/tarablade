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

    public function importImages()
    {
        $html = DOMParser::getHtml($this->filename);

        foreach ($html->find('img') as $element) {
            if (preg_match('/^(www|https|http)/', $element->src) === 0) {
                $imagePath = ltrim($element->src, '\.\/');

                $pathPieces = explode('/', $imagePath);

                if (count($pathPieces) == 0) {
                    // Image is in template directory

                    if (!File::exists(Tarablade::getImagesFolderPath().$element->src)) {
                        File::move($this->filename.'/../'.$element->src,
                            Tarablade::getImagesFolderPath().'/'.$element->src);
                    }
                } elseif (count($pathPieces) > 0) {
                    // Image is in sub-folder

                    $imageName = end($pathPieces);
                    if (!File::exists(Tarablade::getImagesFolderPath().'/'.$imageName)) {
                        File::move(dirname($this->filename).'/'.$imagePath,
                            Tarablade::getImagesFolderPath().'/'.$imageName);
                    }
                }
            }
        }
    }

    public function importStyles()
    {
    }

    public function importScripts()
    {
    }

    public function getExternalResources()
    {
        $resources = [];

        $html = DOMParser::getHtml($this->filename);

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
