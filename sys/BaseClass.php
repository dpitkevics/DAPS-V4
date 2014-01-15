<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

defined('APP_BEGIN_TIME') or define('APP_BEGIN_TIME', microtime(true));
defined('APP_DEBUG') or define('APP_DEBUG', false);
defined('APP_TRACE_LEVEL') or define('APP_TRACE_LEVEL', 0);
defined('APP_ENABLE_EXCEPTION_HANDLER') or define('APP_ENABLE_EXCEPTION_HANDLER', true);
defined('APP_ENABLE_ERROR_HANDLER') or define('APP_ENABLE_ERROR_HANDLER', true);
defined('APP_PATH') or define('APP_PATH', dirname(__FILE__));

/**
 * Description of BaseClass
 *
 * @author Daniels
 */
class BaseClass {

    const VERSION = '0.001';

    private static $_app;
    private static $_restrictedPathReplacements = array(
        'System' => 'sys',
    );

    public static function getVersion() {
        return self::VERSION;
    }

    public static function makeApp($config = null) {
        return new ApplicationClass($config);
    }

    public static function a() {
        return self::$_app;
    }

    public static function setApp($app) {
        if (self::$_app === null || $app === null)
            self::$_app = $app;
        else
            throw new SystemException(Lang::tr('sys', 'Application could be created only once'));
    }

    public static function getPaths($path) {
        $pathParts = explode('.', $path);
        $realPath = '';
        foreach ($pathParts as $pathName) {
            if ($pathName !== end($pathParts))
                $realPath .= ((isset(self::$_restrictedPathReplacements[$pathName])) ? self::$_restrictedPathReplacements[$pathName] : $pathName) . '/';
            else
                $realPath .= $pathName;
        }
        return str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, APP_INDEX . '/' . trim($realPath, '/')));
    }

    public static function autoload($className) {
        $namespace = str_replace('\\', '.', ltrim($className, '\\'));
        if (($path = self::getPaths($namespace)) !== false) {
            include($path . '.php');
        } else
            return false;
    }

}

spl_autoload_register(array('\System\BaseClass', 'autoload'));
