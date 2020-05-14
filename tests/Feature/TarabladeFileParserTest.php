<?php

namespace Mwakisha\Tarablade\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Tarablade;
use Mwakisha\Tarablade\Parsers\TarabladeFileParser;
use Mwakisha\Tarablade\Util\FileSystemUtils;
use Mwakisha\Tarablade\Util\PathUtils;
use Orchestra\Testbench\TestCase;

class TarabladeFileParserTest extends TestCase
{

    private PathUtils $pathUtils;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pathUtils = Tarablade::getInstance()->pathUtils;
    }

    protected function tearDown(): void
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        if (File::isDirectory($this->pathUtils->getPublicPath())) {
            File::deleteDirectory($this->pathUtils->getPublicPath());
        }
        if (File::isDirectory(Tarablade::getViewsResourcePath())) {
            File::deleteDirectory(Tarablade::getViewsResourcePath());
        }
        unset($this->pathUtils);
        parent::tearDown();
    }

    /** @test */
    public function tarablade_can_import_images()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importImages(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists($this->pathUtils->getPublicPath());
        $this->assertFileExists($this->pathUtils->getPublicPath('img/logo.png'));
        $this->assertFileExists($this->pathUtils->getPublicPath('img/favicon.ico'));
    }

    /** @test */
    public function tarablade_can_import_stylesheets()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importStyles(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists($this->pathUtils->getPublicPath());
        $this->assertFileExists($this->pathUtils->getPublicPath('css/main.css'));
        $this->assertFileExists($this->pathUtils->getPublicPath('css/font-awesome.min.css'));
    }

    /** @test */
    public function tarablade_can_import_scripts()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::importScripts(__DIR__.'/TestAssets/index.html');

        $this->assertDirectoryExists($this->pathUtils->getPublicPath());
        $this->assertFileExists($this->pathUtils->getPublicPath('js/popper.min.js'));
    }

    /** @test */
    public function tarablade_can_import_assets_from_multiple_template_files()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        $parser = new TarabladeFileParser(__DIR__.'/TestAssets/index.html');
        $parser->importAssetsFromAllTemplates();

        $this->assertDirectoryExists($this->pathUtils->getPublicPath());
        $this->assertFileExists($this->pathUtils->getPublicPath('img/c-logo.png'));
        $this->assertFileExists($this->pathUtils->getPublicPath('img/favicon.ico'));
        $this->assertFileExists($this->pathUtils->getPublicPath('css/main.css'));
        $this->assertFileExists($this->pathUtils->getPublicPath('css/sassy.css'));
        $this->assertFileExists($this->pathUtils->getPublicPath('js/custom.js'));
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

        TarabladeFileParser::parseCssForAssets(__DIR__.'/TestAssets/css/main.css', __DIR__.'/TestAssets/index.html');
        $this->assertDirectoryExists($this->pathUtils->getPublicPath('img/blog'));
        $this->assertDirectoryExists($this->pathUtils->getPublicPath('img/elements'));

        TarabladeFileParser::parseCssForAssets(__DIR__.'/TestAssets/css/font-awesome.min.css', __DIR__.'/TestAssets/index.html');
        $this->assertDirectoryExists($this->pathUtils->getPublicPath('fonts'));
    }

    /** @test */
    public function tarablade_can_create_a_route()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        $file = __DIR__.'/TestAssets/index.html';
        $routesFile = base_path('routes'.DIRECTORY_SEPARATOR.'web.php');

        $this->assertNotNull(TarabladeFileParser::createRoute($file));
        $this->assertEquals('personal_blog.index', TarabladeFileParser::createRoute($file));
        $this->assertTrue(strpos(file_get_contents($routesFile), "->name('personal_blog.index');") !== false);
    }

    /** @test */
    public function tarablade_can_convert_html_files_to_blade_files()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        TarabladeFileParser::convertToBladeTemplate(__DIR__.'/TestAssets/index.html');

        $this->assertFileExists($this->pathUtils->getViewsResourcePath('index.blade.php'));
    }

    /** @test */
    public function tarablade_can_replace_text_in_file()
    {
        Config::set('tarablade.template_namespace', 'personal_blog');
        mkdir($this->pathUtils->getPublicPath());
        file_put_contents($this->pathUtils->getPublicPath('test.html'), 'this is a cool test');
        TarabladeFileParser::replaceTextInFile($this->pathUtils->getPublicPath('test.html'), 'cool', 'great');

        $this->assertTrue(strpos(file_get_contents($this->pathUtils->getPublicPath('test.html')), 'cool') == false);
        $this->assertTrue(strpos(file_get_contents($this->pathUtils->getPublicPath('test.html')), 'great') !== false);
    }
}
