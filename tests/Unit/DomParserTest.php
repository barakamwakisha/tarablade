<?php

namespace Mwakisha\Tarablade\Tests;

use Mwakisha\Tarablade\DomParser;
use Orchestra\Testbench\TestCase;

class DomParserTest extends TestCase
{
    /** @test */
    public function dom_parser_can_parse_html_text()
    {
        $html = DomParser::getHtml("
          <html>
                <head>
                <title>Cool Html</title>
                 <link type='text/css' rel='stylesheet' href='style.css'>
                 <link type='text/css' rel='stylesheet' href='another.css'>
                 <link rel='shortcut icon' href='favicon.png'>
                </head>
               
                <h1>Cool header</h1>
                <p>Cool paragraph</p>
                <img src='cool.jpg'>
                <img src='cooler.png'>
                <img src='even-cooler.jpg'>
                <a href='cool.html'>Cool Link</a>
                <a href='cooler.html'>Cool Link</a>
                <a href='coolest.html'>Cool Link</a>
                <a href='miles-davis.html'>Cool Link</a>
                
                <script>console.log(true)</script>
                <script src='script.js'></script>
                <script src='another.js'></script>
                <script src='jquery.js'></script>
                <script src='scripts/toast.js'></script>
                </html>
        ");
        list($stylesheetsNumber, $imagesNumber, $anchorLinksNumber, $scriptsNumber) = $this->getResourcesCount($html);

        $this->assertEquals(2, $stylesheetsNumber);
        $this->assertEquals(3, $imagesNumber);
        $this->assertEquals(4, $anchorLinksNumber);
        $this->assertEquals(5, $scriptsNumber);
    }

    /** @test */
    public function dom_parser_can_parse_html_file()
    {
        $html = DomParser::getHtml(__DIR__.'/../Feature/TestAssets/index.html');
        list($stylesheetsNumber, $imagesNumber, $anchorLinksNumber, $scriptsNumber) = $this->getResourcesCount($html);

        $this->assertEquals(10, $stylesheetsNumber);
        $this->assertEquals(28, $imagesNumber);
        $this->assertEquals(49, $anchorLinksNumber);
        $this->assertEquals(19, $scriptsNumber);
    }

    /**
     * @param $html
     *
     * @return array
     */
    public function getResourcesCount($html)
    {
        $stylesheetsNumber = 0;
        $imagesNumber = 0;
        $anchorLinksNumber = 0;
        $scriptsNumber = 0;

        foreach ($html->find('link') as $link) {
            if ($link->type == 'text/css' || $link->rel == 'stylesheet') {
                $stylesheetsNumber++;
            }
        }

        foreach ($html->find('img') as $image) {
            $imagesNumber++;
        }

        foreach ($html->find('a') as $anchorLink) {
            $anchorLinksNumber++;
        }

        foreach ($html->find('script') as $anchorLink) {
            $scriptsNumber++;
        }

        return [$stylesheetsNumber, $imagesNumber, $anchorLinksNumber, $scriptsNumber];
    }
}
