<?php


namespace Mwakisha\Tarablade\Util;


use Mwakisha\Tarablade\Constants\OperatingSystems;
use Mwakisha\Tarablade\Context\ApplicationContext;


class PathUtils
{

    private $applicationContext;

    private static $instance = null;

    private function __construct(ApplicationContext $context)
    {
        $this->applicationContext = $context;
    }

    public static function getInstance(ApplicationContext $context)
    {
        if (self::$instance == null)
        {
            self::$instance = new self($context);
        }

        return self::$instance;
    }

    public function getAbsolutePath($path)
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

        $absolutePath = implode(DIRECTORY_SEPARATOR, $absolutes);

        switch ($this->applicationContext->hostOs) {
            case(OperatingSystems::WINDOWS):
                return $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$absolutePath;
                break;
            default:
                return $absolutePath;
                break;
        }
    }

    public function getTemplateNamespace($path = '')
    {
        return self::getAbsolutePath(config('tarablade.template_namespace'))
            .($path ? '/'.ltrim($path, "\.\/\\") : $path);
    }

    public function getPublicPath($path = '')
    {
        return self::getAbsolutePath(public_path(config('tarablade.template_namespace')))
            .($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }

    public function getViewsResourcePath($path = '')
    {
        return self::getAbsolutePath(resource_path('views'.DIRECTORY_SEPARATOR.config('tarablade.template_namespace')))
            .($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}