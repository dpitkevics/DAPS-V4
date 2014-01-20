<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;

/**
 * Description of Validator
 *
 * @author User
 */
class Validator extends ApplicationComponent {

    public static function validateStrip(&$data) {
        if (is_array($data)) {
            if (count($data) == 0)
                return $data;
            $keys = array_map('stripslashes', array_keys($data));
            $data = array_combine($keys, array_values($data));
            return array_map(array($this, 'stripSlashes'), $data);
        } else
            return stripslashes($data);
    }

    public static function validateString($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
    }

    public static function validateArray(array $array) {
        $validated = array();
        foreach ($array as $key => $value) {
            $validated[self::validateString($key)] = self::validateString($value);
        }
        return $validated;
    }

}
