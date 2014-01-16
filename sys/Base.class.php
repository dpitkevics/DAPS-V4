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
class Base {

    const VERSION = '0.001';

    private static $_app;
    private static $_restrictedPathReplacements = array(
        'System' => 'sys',
        'Web' => 'web',
        'Interfaces' => 'interfaces',
    );

    public static function getVersion() {
        return self::VERSION;
    }

    public static function makeApp($config = null) {
        return new Web\WebApplication($config);
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

    public static function createComponent($config) {
        if (is_string($config)) {
            $type = $config;
            $config = array();
        } elseif (isset($config['class'])) {
            $type = $config['class'];
            unset($config['class']);
        } else
            throw new SystemException(Lang::tr('sys', 'Object configuration must be an array containing a "class" element.'));
        
        if (!class_exists($type, false))
            $type = Base::import($type, true);
        
        if (($n = func_num_args()) > 1) {
            $args = func_get_args();
            if ($n === 2)
                $object = new $type($args[1]);
            elseif ($n === 3)
                $object = new $type($args[1], $args[2]);
            elseif ($n === 4)
                $object = new $type($args[1], $args[2], $args[3]);
            else {
                unset($args[0]);
                $class = new ReflectionClass($type);
                // Note: ReflectionClass::newInstanceArgs() is available for PHP 5.1.3+
                // $object=$class->newInstanceArgs($args);
                $object = call_user_func_array(array($class, 'newInstance'), $args);
            }
        } else {
            $object = new $type;
        }

        foreach ($config as $key => $value)
            $object->$key = $value;

        return $object;
    }
    
    public static function import($name, $include = false) {
        $coreClassNs = self::coreClassNamespaces();
        $name = str_replace(array_keys($coreClassNs), array_values($coreClassNs), $name);
        return self::autoload($name);
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
            $pathName = $path . (strpos($path, 'Interface')!==false?'.php':'.class.php');
            include($pathName);
            return $className;
        } else
            return false;
    }
    
    public static function coreClassNamespaces() {
        return array(
            'Router' => 'System\Web\Router',
            'ErrorEvent' => 'System\ErrorEvent',
            'ErrorHandler' => 'System\ErrorHandler',
            'Http' => 'System\Web\Http',
        );
    }
    
    public static function dump ($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }

}

spl_autoload_register(array('\System\Base', 'autoload'));
