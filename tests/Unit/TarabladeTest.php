<?php

namespace Mwakisha\Tarablade\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateNamespaceAlreadyExistsException;
use Mwakisha\Tarablade\Tarablade;
use Orchestra\Testbench\TestCase;

class TarabladeTest extends TestCase
{
    protected function tearDown(): void
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        if (File::isDirectory(Tarablade::getPublicPath())) {
            File::deleteDirectory(Tarablade::getPublicPath());
        }
    }

    /** @test */
    public function tarablade_can_get_template_namespace()
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        $this->assertNotNull(Tarablade::getPublicPath());
        $this->assertEquals('admin_panel', basename(Tarablade::getPublicPath()));
    }

    /** @test */
    public function tarablade_can_get_views_resource_path()
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        $this->assertNotNull(Tarablade::getViewsResourcePath());
        $this->assertEquals('admin_panel', basename(Tarablade::getViewsResourcePath()));
    }

    /** @test */
    public function tarablade_can_get_absolute_path()
    {
        $rawPath = 'this/is/../a/./test/.///is';

        if (Tarablade::runningOnUnix()) {
            $this->assertEquals($_SERVER['DOCUMENT_ROOT']
                .DIRECTORY_SEPARATOR.
                'this'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'is', Tarablade::getAbsolutePath($rawPath));
        } else {
            $this->assertEquals('this'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'is', Tarablade::getAbsolutePath($rawPath));
        }
    }

    /** @test */
    public function tarablade_can_delete_directories()
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        File::makeDirectory(Tarablade::getPublicPath());
        $this->assertDirectoryExists(Tarablade::getPublicPath());

        Tarablade::deleteFolder(Tarablade::getPublicPath());
        $this->assertDirectoryNotExists(Tarablade::getPublicPath());
    }

    /** @test */
    public function tarablade_can_check_if_it_is_running_on_unix_or_not()
    {
        $this->assertNotNull(Tarablade::runningOnUnix());
    }

    /** @test */
    public function tarablade_can_copy_files_and_directories()
    {
        Tarablade::copy(
            __DIR__.'/../Feature/TestAssets/index.html',
            __DIR__.'/TestAssets/index.html'
        );

        $this->assertFileExists(__DIR__.'/../Feature/TestAssets/index.html');
        $this->assertFileExists(__DIR__.'/TestAssets/index.html');

        Tarablade::deleteFolder(__DIR__.'/TestAssets');
    }

    /** @test */
    public function tarablade_can_validate_that_a_file_exists()
    {
        $this->assertNull(Tarablade::validateFileExists(__DIR__.'/../Feature/TestAssets/index.html'));

        $this->expectException(TemplateFileNotFoundException::class);
        $this->assertNotNull(Tarablade::validateFileExists(__DIR__.'/../Feature/TestAssets/does-not-exist.html'));
    }

    /** @test */
    public function tarablade_can_validate_that_a_directory_exists()
    {
        $this->assertNull(Tarablade::validateDirectoryExists(__DIR__.'/../Feature/TestAssets/'));

        $this->expectException(TemplateDirectoryNotFoundException::class);
        $this->assertNotNull(Tarablade::validateDirectoryExists(__DIR__.'/../Feature/NonExistent/'));
    }

    /** @test */
    public function tarablade_can_validate_template_namespace()
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        File::makeDirectory(Tarablade::getPublicPath());
        $this->assertDirectoryExists(Tarablade::getPublicPath());

        $this->expectException(TemplateNamespaceAlreadyExistsException::class);
        $this->assertNotNull(Tarablade::validateTemplateNamespace());

        Tarablade::deleteFolder(Tarablade::getPublicPath());
        $this->assertDirectoryNotExists(Tarablade::getPublicPath());
        $this->assertNull(Tarablade::validateTemplateNamespace());
    }
}
