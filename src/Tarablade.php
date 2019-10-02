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
        return public_path(config('tarablade.images_folder'));
    }

    public static function getStylesFolderPath()
    {
        return public_path(config('tarablade.stylesheets_folder'));
    }

    public static function getScriptsFolderPath()
    {
        return public_path(config('tarablade.scripts_folder'));
    }

    public static function validateFileExists($filepath)
    {
        if (!File::exists($filepath)) {
            throw new TemplateFileNotFoundException(
                'The file '.$filepath.' does not exists'
            );
        }
    }

    public static function validateSourceDirectory($path)
    {
        if (!File::isDirectory($path)) {
            throw new TemplateDirectoryNotFoundException(
                'Directory at '.$path.' does not exists'
            );
        }
    }

    public static function validateAssetDestinationFolders()
    {
        if (File::isDirectory(self::getImagesFolderPath())) {
            throw new FolderAlreadyExistsException(
                'A folder with the name '.config('tarablade.images_folder').' already exists in the '.
                'public folder. Please rename it or change the images_folder name in the config file.'
            );
        } elseif (File::isDirectory(self::getStylesFolderPath())) {
            throw new FolderAlreadyExistsException(
                'A folder with the name '.config('tarablade.stylesheets_folder').' already exists in the '.
                'public folder. Please rename it or change the stylesheets_folder name in the config file.'
            );
        } elseif (File::isDirectory(self::getScriptsFolderPath())) {
            throw new FolderAlreadyExistsException(
                'A folder with the name '.config('tarablade.scripts_folder').' already exists in the '.
                'public folder. Please rename it or change the scripts_folder name in the config file.'
            );
        }
    }

    public static function createAssetDestinationFolders()
    {
        if (!File::isDirectory(self::getImagesFolderPath())) {
            File::makeDirectory(self::getImagesFolderPath(), 0777, true, true);
        }

        if (!File::isDirectory(self::getStylesFolderPath())) {
            File::makeDirectory(self::getStylesFolderPath(), 0777, true, true);
        }

        if (!File::isDirectory(self::getScriptsFolderPath())) {
            File::makeDirectory(self::getScriptsFolderPath(), 0777, true, true);
        }
    }

    public static function cleanPath($path)
    {
        return rtrim($path, '/\\');
    }

    public static function createFolder($folderPath)
    {
        if (!mkdir($folderPath, 0777)) {
            throw new \Exception('Unable to create folder at '.$folderPath);
        }
    }
}
