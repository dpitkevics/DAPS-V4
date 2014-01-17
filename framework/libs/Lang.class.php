<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;

/**
 * Description of Lang
 *
 * @author User
 */
class Lang extends ApplicationComponent{
    
    public static function tr($key, $text, array $params = array()) {
        return str_replace(array_keys($params), array_values($params), $text);
    }
    
}
