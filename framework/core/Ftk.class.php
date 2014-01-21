<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\core;

use base\libs\SystemException;
use base\libs\Lang;
/**
 * Description of Ftk
 *
 * @author User
 */
class Ftk {
    
    public static $_app;
    public static $_config;
    
    public static function app() {
        return self::$_app;
    }
    
    public static function setConfig() {
        $configDir = FTK_ROOT . DS . 'app' . DS . 'setup' . DS . 'config.php';
        self::$_config = include_once $configDir;
    }
    
    public static function getConfig($key = null) {
        if ($key === null) {
            return self::$_config;
        }

        if (isset(self::$_config[$key])) {
            return self::$_config[$key];
        }
        throw new SystemException(Lang::tr('system', '"' . $key . '" is not specified in config file.'));
    }
    
}
