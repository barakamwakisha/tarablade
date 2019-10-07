<?php

namespace Mwakisha\Tarablade\Tests\Feature;

use Mwakisha\Tarablade\TarabladeFileParser;
use Orchestra\Testbench\TestCase;

class TarabladeFileParserTest extends TestCase
{
    /** @test */
    public function file_parser_can_parse_html_file()
    {
        $parser = new TarabladeFileParser(__DIR__.'/TestAssets/index.html');

        $this->assertNotEquals(count($parser->getExternalResources()), 0);
    }
}
