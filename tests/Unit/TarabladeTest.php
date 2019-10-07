<?php

namespace Mwakisha\Tarablade\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Exceptions\FolderAlreadyExistsException;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;
use Mwakisha\Tarablade\Tarablade;
use mysql_xdevapi\Exception;
use Orchestra\Testbench\TestCase;

class TarabladeTest extends TestCase
{

    /** @test */
    public function tarablade_can_get_absolute_path()
    {
        $rawPath = 'this/is/../a/./test/.///is';
        $this->assertEquals('this/a/test/is', Tarablade::getAbsolutePath($rawPath));
    }

    /** @test */
    public function tarablade_returns_non_null_images_folder_path()
    {
        $this->assertNotNull(Tarablade::getImagesFolderPath());
    }

    /** @test */
    public function tarablade_returns_images_folder_path_in_config_file()
    {
        Config::set("tarablade.images_folder", "images");
        $this->assertEquals("images", basename(Tarablade::getImagesFolderPath()));
    }

    /** @test */
    public function tarablade_returns_non_null_styles_folder_path()
    {
        $this->assertNotNull(Tarablade::getStylesFolderPath());
    }

    /** @test */
    public function tarablade_returns_styles_folder_path_in_config_file()
    {
        Config::set("tarablade.stylesheets_folder", "css");
        $this->assertEquals("css", basename(Tarablade::getStylesFolderPath()));
    }

    /** @test */
    public function tarablade_returns_non_null_scripts_folder_path()
    {
        $this->assertNotNull(Tarablade::getScriptsFolderPath());
    }

    /** @test */
    public function tarablade_returns_scripts_folder_path_in_config_file()
    {
        Config::set("tarablade.scripts_folder", "scripts");
        $this->assertEquals("scripts", basename(Tarablade::getScriptsFolderPath()));
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
    public function tarablade_can_successfully_create_a_folder()
    {
        Tarablade::createFolder(__DIR__.'/../Feature/TestAssets/test_folder');
        $this->assertDirectoryExists(__DIR__.'/../Feature/TestAssets/test_folder');
    }

    /** @test */
    public function tarablade_can_successfully_delete_a_folder()
    {
        Tarablade::deleteFolder(__DIR__.'/../Feature/TestAssets/test_folder');
        $this->assertDirectoryNotExists(__DIR__.'/../Feature/TestAssets/test_folder');
    }

    /** @test */
    public function tarablade_can_validate_assets_destination_folders()
    {
        Config::set("tarablade.images_folder", "images");
        Config::set("tarablade.scripts_folder", "scripts");
        Config::set("tarablade.stylesheets_folder", "css");

        $this->assertNull(Tarablade::validateAssetsDestinationFolders());
    }

    /** @test */
    public function tarablade_can_create_assets_destination_folders()
    {
        Tarablade::deleteFolder(Tarablade::getImagesFolderPath());
        Tarablade::deleteFolder(Tarablade::getScriptsFolderPath());
        Tarablade::deleteFolder(Tarablade::getStylesFolderPath());

        Config::set("tarablade.images_folder", "images");
        Config::set("tarablade.scripts_folder", "scripts");
        Config::set("tarablade.stylesheets_folder", "css");

        $this->assertNull(Tarablade::createAssetsDestinationFolders());
        $this->assertNull(Tarablade::validateDirectoryExists(Tarablade::getImagesFolderPath()));
        $this->assertNull(Tarablade::validateDirectoryExists(Tarablade::getScriptsFolderPath()));
        $this->assertNull(Tarablade::validateDirectoryExists(Tarablade::getStylesFolderPath()));

        Tarablade::deleteFolder(Tarablade::getImagesFolderPath());
        Tarablade::deleteFolder(Tarablade::getScriptsFolderPath());
        Tarablade::deleteFolder(Tarablade::getStylesFolderPath());
    }
}
