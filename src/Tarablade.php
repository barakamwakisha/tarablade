<?php

namespace Mwakisha\Tarablade;

use Illuminate\Support\Facades\File;
use League\Flysystem\Config;
use Mwakisha\Tarablade\Exceptions\FolderAlreadyExistsException;
use Mwakisha\Tarablade\Exceptions\TemplateDirectoryNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateFileNotFoundException;
use Mwakisha\Tarablade\Exceptions\TemplateNamespaceAlreadyExistsException;
use mysql_xdevapi\Exception;

class Tarablade
{
    public static function getTemplateNamespace($path = '')
    {
        return self::getAbsolutePath(public_path(config('tarablade.template_namespace')))
                . ($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }

    public static function getAbsolutePath($path)
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $absolutes);
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
        $namespacePath = self::getTemplateNamespace();
        if (File::isDirectory($namespacePath)) {
            throw new TemplateNamespaceAlreadyExistsException(
              'The template under the namespace '.self::getTemplateNamespace().' has already been imported. Please change the template namespace.'
            );
        }
    }

    public static function copy($source, $target)
    {
        $path = pathinfo($target);
        if(!file_exists($path['dirname'])) {
            mkdir($path['dirname'], 0777, true);
        }

        if(!copy($source, $target)) {
            throw new Exception(
                "Could not copy a file to the destination folder"
            );
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

            if (!self::deleteFolder($dir.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
