<?php

namespace Mwakisha\Tarablade\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Exceptions\FolderAlreadyExistsException;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;
use Mwakisha\Tarablade\Tarablade;
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

//     TODO: Test Tarablade asset folder creation

//     TODO: Test Tarablade destination asset folders validation

//    /** @test */
//    public function tarablade_throws_exception_if_specified_destination_image_asset_folder_already_exists()
//    {
//
//        $this->withoutExceptionHandling();
//        Config::set("tarablade.images_folder", "images");
//
//
//        Tarablade::createImagesDestinationFolder();
//        $this->expectException(FolderAlreadyExistsException::class);
//        $this->assertNotNull(Tarablade::validateAssetDestinationFolders());
//    }

//    /** @test */
//    public function tarablade_does_not_throw_exception_if_specified_destination_image_asset_folder_does_not_exists()
//    {
//        Config::set("tarablade.images_folder", "images");
//
////        $this->assertNull(Tarablade::validateAssetDestinationFolders());
//    }




}
