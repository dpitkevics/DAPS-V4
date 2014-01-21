<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base;

/**
 * Description of bootstrap
 *
 * @author User
 */
class Bootstrap {

    public static function prepare() {
        self::initializeApp();
        core\Ftk::$_app = new web\WebApplication();
        return core\Ftk::$_app;
    }

    public static function initializeApp() {
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'definitions' . DIRECTORY_SEPARATOR . 'main.php';
        spl_autoload_register(__NAMESPACE__ . '\Bootstrap::autoloader');
    }

    public static function autoloader($classname) {
        if (strpos($classname, 'interface') !== false) {
            $interface = true;
        } else {
            $interface = false;
        }
        if ($interface) {
            $dir = FTK_DIR . '/interfaces';
        } else {
            $dir = FTK_DIR;
        }
        $dir = realpath($dir . DS . str_replace('base\\', '', $classname) . '.' . ($interface ? 'interface' : 'class') . '.php');
        if ($dir === false) {
            echo '<pre>Could not find file ' . print_r($dir, 1) . '</pre>';
        } else {
            include_once $dir;
        }
    }

}
