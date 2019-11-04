<?php

namespace Mwakisha\Tarablade\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Tarablade;
use Mwakisha\Tarablade\TarabladeFileParser;
use Orchestra\Testbench\TestCase;

class TarabladeFileParserTest extends TestCase
{
    protected function tearDown(): void
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        if (File::isDirectory(Tarablade::getTemplateNamespace())) {
            File::deleteDirectory(Tarablade::getTemplateNamespace());
        }
    }

    /** @test */
    public function tarablade_can_import_images()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importImages(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists(Tarablade::getTemplateNamespace());
        $this->assertFileExists(Tarablade::getTemplateNamespace('img/logo.png'));
        $this->assertFileExists(Tarablade::getTemplateNamespace('img/favicon.ico'));
    }

    /** @test */
    public function tarablade_can_import_stylesheets()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importStyles(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists(Tarablade::getTemplateNamespace());
        $this->assertFileExists(Tarablade::getTemplateNamespace('css/main.css'));
        $this->assertFileExists(Tarablade::getTemplateNamespace('css/font-awesome.min.css'));
    }

    /** @test */
    public function tarablade_can_import_scripts()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importScripts(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists(Tarablade::getTemplateNamespace());
        $this->assertFileExists(Tarablade::getTemplateNamespace('js/popper.min.js'));
    }

    /** @test */
    public function tarablade_can_import_assets_from_multiple_template_files()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        $parser = new TarabladeFileParser(__DIR__.'/TestAssets/index.html');
        $parser->importAssetsFromAllTemplates();

        $this->assertDirectoryExists(Tarablade::getTemplateNamespace());
        $this->assertFileExists(Tarablade::getTemplateNamespace('img/c-logo.png'));
        $this->assertFileExists(Tarablade::getTemplateNamespace('img/favicon.ico'));
        $this->assertFileExists(Tarablade::getTemplateNamespace('css/main.css'));
        $this->assertFileExists(Tarablade::getTemplateNamespace('css/sassy.css'));
        $this->assertFileExists(Tarablade::getTemplateNamespace('js/custom.js'));
    }

    /** @test */
    public function tarablade_can_check_whether_uri_is_remote_or_not()
    {
        $this->assertFalse(TarabladeFileParser::isRemoteUri(__DIR__.'/TestAssets/index.html'));
        $this->assertTrue(TarabladeFileParser::isRemoteUri('www.google.com'));
        $this->assertTrue(TarabladeFileParser::isRemoteUri('https://stackoverflow.com/'));
        $this->assertTrue(TarabladeFileParser::isRemoteUri('http://ztwmbfcnhdkrsxlv.neverssl.com/online'));
    }

    /** @test */
    public function tarablade_can_parse_css_for_asset_urls()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');

        TarabladeFileParser::parseCssForAssets(__DIR__.'/TestAssets/css/main.css');
        $this->assertDirectoryExists(Tarablade::getTemplateNamespace('img/blog'));
        $this->assertDirectoryExists(Tarablade::getTemplateNamespace('img/elements'));

        TarabladeFileParser::parseCssForAssets(__DIR__.'/TestAssets/css/font-awesome.min.css');
        $this->assertDirectoryExists(Tarablade::getTemplateNamespace('fonts'));
    }
}
