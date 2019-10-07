<?php

namespace Mwakisha\Tarablade;

use Illuminate\Support\Facades\File;
use Mwakisha\Tarablade\Exceptions\FolderAlreadyExistsException;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;

class Tarablade
{
    public static function getImagesFolderPath()
    {
        return self::getAbsolutePath(public_path(config('tarablade.images_folder')));
    }

    public static function getStylesFolderPath()
    {
        return self::getAbsolutePath(public_path(config('tarablade.stylesheets_folder')));
    }

    public static function getScriptsFolderPath()
    {
        return self::getAbsolutePath(public_path(config('tarablade.scripts_folder')));
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
        if (!File::isDirectory($path) || ! File::exists($path)) {
            throw new TemplateDirectoryNotFoundException(
                'Directory at '.$path.' does not exists'
            );
        }
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
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function createFolder($folderPath)
    {
        if(!file_exists($folderPath)) {
            mkdir($folderPath, 0777);
        }
    }

    public static function deleteFolder($folderPath)
    {
        if(file_exists($folderPath)) {
            rmdir($folderPath);
        }
    }

    public static function validateAssetsDestinationFolders()
    {
        self::validateImagesDestinationFolder();
        self::validateStylesDestinationFolder();
        self::validateScriptsDestinationFolder();
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
            File::makeDirectory(self::getImagesFolderPath(), 0777, true, true);
        }
    }

    public static function createStylesDestinationFolder()
    {
        if (!File::isDirectory(self::getStylesFolderPath())) {
            File::makeDirectory(self::getStylesFolderPath(), 0777, true, true);
        }
    }

    public static function createScriptsDestinationFolder()
    {
        if (!File::isDirectory(self::getScriptsFolderPath())) {
            File::makeDirectory(self::getScriptsFolderPath(), 0777, true, true);
        }
    }

    public static function validateImagesDestinationFolder()
    {
        if (file_exists(self::getImagesFolderPath()) && is_dir(self::getImagesFolderPath())) {
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
}
