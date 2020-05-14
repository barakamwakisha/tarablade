<?php


namespace Mwakisha\Tarablade\Util;


use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateNamespaceAlreadyExistsException;

class FileSystemUtils
{

    private PathUtils $pathUtils;

    private static $instance = null;

    private function __construct(PathUtils $utils)
    {
        $this->pathUtils = $utils;
    }

    public static function getInstance(PathUtils $utils)
    {
        if (self::$instance == null)
        {
            self::$instance = new self($utils);
        }

        return self::$instance;
    }

    public function validateTemplateNamespace()
    {
        $publicPath = $this->pathUtils->getPublicPath();
        if (File::isDirectory($publicPath)) {
            throw new TemplateNamespaceAlreadyExistsException(
                'The template under the namespace '.$publicPath.' has already been imported. Please change the template namespace.'
            );
        }
    }

    public function validateFileExists($filepath)
    {
        if (!File::exists($filepath)) {
            throw new TemplateFileNotFoundException(
                'The file '.$filepath.' does not exists'
            );
        }
    }

    public function validateDirectoryExists($path)
    {
        if (!File::isDirectory($path)) {
            throw new TemplateDirectoryNotFoundException(
                'Directory at '.$path.' does not exists'
            );
        }
    }

    public function copy($source, $target)
    {
        $path = pathinfo($target);
        if (!file_exists($path['dirname'])) {
            mkdir($path['dirname'], 0777, true);
        }

        if (!copy($source, $target)) {
            throw new Exception(
                'Could not copy a file to the destination folder'
            );
        }
    }

    public function deleteFolder($dir)
    {
        if (!file_exists($dir)) return true;

        if (!is_dir($dir)) return unlink($dir);

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;

            if (!self::deleteFolder($dir.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
}