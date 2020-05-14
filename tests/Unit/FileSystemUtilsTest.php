<?php

namespace Mwakisha\Tarablade\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateNamespaceAlreadyExistsException;
use Mwakisha\Tarablade\Tarablade;
use Mwakisha\Tarablade\Util\FileSystemUtils;
use Mwakisha\Tarablade\Util\PathUtils;
use Orchestra\Testbench\TestCase;

class FileSystemUtilsTest extends TestCase
{

    private PathUtils $pathUtils;

    private FileSystemUtils $fsUtils;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pathUtils = Tarablade::getInstance()->pathUtils;
        $this->fsUtils = Tarablade::getInstance()->fsUtils;
    }

    protected function tearDown(): void
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        if (File::isDirectory($this->pathUtils->getPublicPath())) {
            File::deleteDirectory($this->pathUtils->getPublicPath());
        }
        unset($this->pathUtils);
        unset($this->fsUtils);
        parent::tearDown();
    }

    /** @test */
    public function tarablade_can_validate_template_namespace()
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        File::makeDirectory($this->pathUtils->getPublicPath());
        $this->assertDirectoryExists($this->pathUtils->getPublicPath());

        $this->expectException(TemplateNamespaceAlreadyExistsException::class);
        $this->assertNotNull($this->pathUtils->getPublicPath());

        $this->fsUtils->deleteFolder($this->pathUtils->getPublicPath());
        $this->assertDirectoryNotExists($this->pathUtils->getPublicPath());
        $this->assertNull($this->fsUtils->validateTemplateNamespace());
    }

    /** @test */
    public function tarablade_can_validate_that_a_file_exists()
    {
        $this->assertNull($this->fsUtils->validateFileExists(__DIR__.'/../Feature/TestAssets/index.html'));

        $this->expectException(TemplateFileNotFoundException::class);
        $this->assertNotNull($this->fsUtils->validateFileExists(__DIR__.'/../Feature/TestAssets/does-not-exist.html'));
    }

    /** @test */
    public function tarablade_can_validate_that_a_directory_exists()
    {
        $this->assertNull($this->fsUtils->validateDirectoryExists(__DIR__.'/../Feature/TestAssets/'));

        $this->expectException(TemplateDirectoryNotFoundException::class);
        $this->assertNotNull($this->fsUtils->validateDirectoryExists(__DIR__.'/../Feature/NonExistent/'));
    }

    /** @test */
    public function tarablade_can_delete_directories()
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        $publicPath = $this->pathUtils->getPublicPath();

        File::makeDirectory($publicPath);
        $this->assertDirectoryExists($publicPath);

        $this->fsUtils->deleteFolder($publicPath);
        $this->assertDirectoryNotExists($publicPath);
    }

    /** @test */
    public function tarablade_can_copy_files_and_directories()
    {
        $this->fsUtils->copy(
            __DIR__.'/../Feature/TestAssets/index.html',
            __DIR__.'/TestAssets/index.html'
        );

        $this->assertFileExists(__DIR__.'/../Feature/TestAssets/index.html');
        $this->assertFileExists(__DIR__.'/TestAssets/index.html');

        $this->fsUtils->deleteFolder(__DIR__.'/TestAssets');
    }
}
