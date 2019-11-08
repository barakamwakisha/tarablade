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
        if (File::isDirectory(Tarablade::getPublicPath())) {
            File::deleteDirectory(Tarablade::getPublicPath());
        }

        if(File::isDirectory(Tarablade::getViewsResourcePath())) {
            File::deleteDirectory(Tarablade::getViewsResourcePath());
        }
    }

    /** @test */
    public function tarablade_can_import_images()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importImages(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists(Tarablade::getPublicPath());
        $this->assertFileExists(Tarablade::getPublicPath('img/logo.png'));
        $this->assertFileExists(Tarablade::getPublicPath('img/favicon.ico'));
    }

    /** @test */
    public function tarablade_can_import_stylesheets()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importStyles(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists(Tarablade::getPublicPath());
        $this->assertFileExists(Tarablade::getPublicPath('css/main.css'));
        $this->assertFileExists(Tarablade::getPublicPath('css/font-awesome.min.css'));
    }

    /** @test */
    public function tarablade_can_import_scripts()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importScripts(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists(Tarablade::getPublicPath());
        $this->assertFileExists(Tarablade::getPublicPath('js/popper.min.js'));
    }

    /** @test */
    public function tarablade_can_import_assets_from_multiple_template_files()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        $parser = new TarabladeFileParser(__DIR__.'/TestAssets/index.html');
        $parser->importAssetsFromAllTemplates();

        $this->assertDirectoryExists(Tarablade::getPublicPath());
        $this->assertFileExists(Tarablade::getPublicPath('img/c-logo.png'));
        $this->assertFileExists(Tarablade::getPublicPath('img/favicon.ico'));
        $this->assertFileExists(Tarablade::getPublicPath('css/main.css'));
        $this->assertFileExists(Tarablade::getPublicPath('css/sassy.css'));
        $this->assertFileExists(Tarablade::getPublicPath('js/custom.js'));
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
        $this->assertDirectoryExists(Tarablade::getPublicPath('img/blog'));
        $this->assertDirectoryExists(Tarablade::getPublicPath('img/elements'));

        TarabladeFileParser::parseCssForAssets(__DIR__.'/TestAssets/css/font-awesome.min.css');
        $this->assertDirectoryExists(Tarablade::getPublicPath('fonts'));
    }

    /** @test */
    public function tarablade_can_convert_html_files_to_blade_files()
    {
        // TODO: Test conversion functionality
        Config::set('tarablade.template_namespace', 'personal_blog');
        $this->assertNull(TarabladeFileParser::convertToBladeTemplate(__DIR__.'/TestAssets/index.html'));
    }

    /** @test */
    public function tarablade_can_replace_text_in_file()
    {
        // TODO: Test replacement functionality
        $this->assertNull(TarabladeFileParser::replaceTextInFile(__DIR__.'/TestAssets/index.html', "", ""));
    }
}
