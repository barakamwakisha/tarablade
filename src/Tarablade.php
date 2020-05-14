<?php

namespace Mwakisha\Tarablade;

use Exception;
use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Context\ApplicationContext;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateNamespaceAlreadyExistsException;
use Mwakisha\Tarablade\Util\FileSystemUtils;
use Mwakisha\Tarablade\Util\PathUtils;

class Tarablade
{

    public ApplicationContext $applicationContext;

    public PathUtils $pathUtils;

    public FileSystemutils $fsUtils;

    private static $instance = null;

    private function __construct()
    {
        $this->initializeApplication();
    }

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function initializeApplication()
    {
        $applicationContext = ApplicationContext::getInstance();
        $pathUtils = PathUtils::getInstance($applicationContext);
        $fsUtils = FileSystemUtils::getInstance($pathUtils);

        $this->applicationContext = $applicationContext;
        $this->pathUtils = $pathUtils;
        $this->fsUtils = $fsUtils;
    }
}
