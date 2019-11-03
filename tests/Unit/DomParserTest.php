<?php

namespace Mwakisha\Tarablade\Tests\Unit;

use Mwakisha\Tarablade\DomParser;
use Orchestra\Testbench\TestCase;

class DomParserTest extends TestCase
{
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
