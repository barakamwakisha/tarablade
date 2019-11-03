<?php

namespace Mwakisha\Tarablade;

use Illuminate\Support\Facades\File;
use League\Flysystem\Config;
use Mwakisha\Tarablade\Exceptions\FolderAlreadyExistsException;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateNamespaceAlreadyExistsException;

class Tarablade
{
    public static function getTemplateNamespace()
    {
        return self::getAbsolutePath(public_path(config('tarablade.template_namespace')));
    }

    public static function getImagesFolderPath()
    {
        return self::getAbsolutePath(
            self::getTemplateNamespace() . DIRECTORY_SEPARATOR . config('tarablade.images_folder')
        );
    }

    public static function getStylesFolderPath()
    {
        return self::getAbsolutePath(
            self::getTemplateNamespace() . DIRECTORY_SEPARATOR . config('tarablade.stylesheets_folder')
        );
    }

    public static function getScriptsFolderPath()
    {
        return self::getAbsolutePath(
            self::getTemplateNamespace() . DIRECTORY_SEPARATOR . config('tarablade.scripts_folder')
        );
    }

    public static function getAbsolutePath($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function validateFileExists($filepath)
    {
        if (!File::exists($filepath)) {
            throw new TemplateFileNotFoundException(
                'The file '.$filepath.' does not exists'
            );
        }
    }

    public static function validateDirectoryExists($path)
    {
        if (!File::isDirectory($path)) {
            throw new TemplateDirectoryNotFoundException(
                'Directory at '.$path.' does not exists'
            );
        }
    }

    public static function validateTemplateNamespace()
    {
        $namespacePath = self::getAbsolutePath(public_path(self::getTemplateNamespace()));
        if(File::isDirectory($namespacePath) || File::exists($namespacePath)) {
            throw new TemplateNamespaceAlreadyExistsException(
              'The template under the namespace ' . self::getTemplateNamespace() . ' has already been imported. Please change the template namespace.'
            );
        }
    }

    public static function validateAssetsDestinationFolders()
    {
        self::validateImagesDestinationFolder();
        self::validateStylesDestinationFolder();
        self::validateScriptsDestinationFolder();
    }

    public static function validateImagesDestinationFolder()
    {
        if (File::isDirectory(self::getImagesFolderPath())) {
            throw new FolderAlreadyExistsException(
                'A folder with the name ' . config('tarablade.images_folder') . ' already exists in the ' .
                'public folder. Please rename it or change the images_folder name in the config file.'
            );
        }
    }

    public static function validateStylesDestinationFolder()
    {
        if (File::exists(self::getStylesFolderPath())) {
            throw new FolderAlreadyExistsException(
                'A folder with the name ' . config('tarablade.stylesheets_folder') . ' already exists in the ' .
                'public folder. Please rename it or change the stylesheets_folder name in the config file.'
            );
        }
    }

    public static function validateScriptsDestinationFolder()
    {
        if (File::exists(self::getScriptsFolderPath())) {
            throw new FolderAlreadyExistsException(
                'A folder with the name ' . config('tarablade.scripts_folder') . ' already exists in the ' .
                'public folder. Please rename it or change the scripts_folder name in the config file.'
            );
        }
    }

    public static function createAssetsDestinationFolders()
    {
        self::createImagesDestinationFolder();
        self::createStylesDestinationFolder();
        self::createScriptsDestinationFolder();
    }

    public static function createImagesDestinationFolder()
    {
        if (!File::isDirectory(self::getImagesFolderPath())) {
            mkdir($_SERVER['DOCUMENT_ROOT']
                . DIRECTORY_SEPARATOR .
                self::getImagesFolderPath(), 0777, true);
        }
    }

    public static function createStylesDestinationFolder()
    {
        if (!File::isDirectory(self::getStylesFolderPath())) {
            mkdir($_SERVER['DOCUMENT_ROOT']
                . DIRECTORY_SEPARATOR .
                self::getStylesFolderPath(), 0777, true);
        }
    }

    public static function createScriptsDestinationFolder()
    {
        if (!File::isDirectory(self::getScriptsFolderPath())) {
            mkdir($_SERVER['DOCUMENT_ROOT']
                . DIRECTORY_SEPARATOR .
                self::getScriptsFolderPath(), 0777, true);
        }
    }

    public static function deleteFolder($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!self::deleteFolder($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }
}
