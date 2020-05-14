<?php

namespace Mwakisha\Tarablade\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Constants\OperatingSystems;
use Mwakisha\Tarablade\Context\ApplicationContext;
use Mwakisha\Tarablade\Tarablade;
use Mwakisha\Tarablade\Util\PathUtils;
use Orchestra\Testbench\TestCase;


class PathUtilsTest extends TestCase
{

    private PathUtils $pathUtils;

    private ApplicationContext $applicationContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pathUtils = Tarablade::getInstance()->pathUtils;
        $this->applicationContext = Tarablade::getInstance()->applicationContext;
    }

    protected function tearDown(): void
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        if (File::isDirectory($this->pathUtils->getPublicPath())) {
            File::deleteDirectory($this->pathUtils->getPublicPath());
        }
        unset($this->pathUtils);
        unset($this->applicationContext);
        parent::tearDown();
    }

    /** @test */
    public function tarablade_can_get_template_namespace()
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        $publicPath = $this->pathUtils->getPublicPath();
        $this->assertNotNull($publicPath);
        $this->assertEquals('admin_panel', basename($publicPath));
    }

    /** @test */
    public function tarablade_can_get_views_resource_path()
    {
        Config::set('tarablade.template_namespace', 'admin_panel');
        $viewsResourcePath = $this->pathUtils->getViewsResourcePath();
        $this->assertNotNull($viewsResourcePath);
        $this->assertEquals('admin_panel', basename($viewsResourcePath));
    }

    /** @test */
    public function tarablade_can_get_absolute_path()
    {
        $rawPath = 'this/is/../a/./test/.///is';

        $rawAbsolutePath = $this->pathUtils->getAbsolutePath($rawPath);

        switch ($this->applicationContext->hostOs) {
            case OperatingSystems::WINDOWS:
                $this->assertEquals($_SERVER['DOCUMENT_ROOT']
                    .DIRECTORY_SEPARATOR.
                    'this'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'is', $rawAbsolutePath);
                break;

            default:
                $this->assertEquals('this'.DIRECTORY_SEPARATOR.'a'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'is', $rawAbsolutePath);
                break;
        }
    }
}
