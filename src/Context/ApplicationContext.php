<?php


namespace Mwakisha\Tarablade\Context;


use Mwakisha\Tarablade\Constants\OperatingSystems;

class ApplicationContext
{

    public $hostOs;

    private static $instance = null;

    private function __construct()
    {
        if (DIRECTORY_SEPARATOR == '/') {
            $this->hostOs = OperatingSystems::WINDOWS;
        } else {
            $this->hostOs = OperatingSystems::LINUX;
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }
}