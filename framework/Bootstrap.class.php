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
        return new web\WebApplication();
    }
    
    public static function initializeApp() {
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'definitions' . DIRECTORY_SEPARATOR . 'main.php';
        spl_autoload_register(__NAMESPACE__ . '\Bootstrap::autoloader');
    }
    
    public static function autoloader($classname) {
        if (strpos($classname, 'interface')!==false) {
            $interface = true;
        } else {
            $interface = false;
        }
        if ($interface) {
            $dir = FTK_DIR . '/interfaces';
        } else {
            $dir = FTK_DIR;
        }
        
        include_once $dir . DS . str_replace('base\\', '', $classname) . '.' . ($interface ? 'interface' : 'class') . '.php';
    }
    
}