<?php

if (!function_exists('def')) {

    function def($key, $value) {
        if (!defined($key)) {
            define($key, $value);
        }
    }

}
def('DS', DIRECTORY_SEPARATOR);
/**
 * FToolKit base dir
 */
def('FTK_DIR', dirname(__FILE__) . '/..');
def('FTK_DEBUG', true);
