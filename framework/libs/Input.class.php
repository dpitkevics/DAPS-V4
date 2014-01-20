<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;

/**
 * Description of Input
 *
 * @author User
 */
class Input extends ApplicationComponent {
    
    private static $_validatedPost = array();
    private static $_validatedGet = array();
    private static $_validatedRequest = array();
    private static $_validatedCookies = array();
    
    private static $_isPostFullyValid = false;
    private static $_isGetFullyValid = false;
    private static $_isRequestFullyValid = false;
    private static $_isCookiesFullyValid = false;
    
    public static function post($key = null) {
        if ($key === null) {
            if (self::$_isPostFullyValid === true) {
                return self::$_validatedPost;
            }
            
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                return self::$_validatedPost = Validator::validateStrip($_POST);
            }
            
            return self::$_validatedPost = Validator::validateArray($_POST);
        }
        
        if (isset(self::$_validatedPost[$key])) {
            return self::$_validatedPost[$key];
        }
        
        if (!isset($_POST[$key])) {
            throw new SystemException(Lang::tr('system', 'Undefined $_POST key "' . $key . '".'));
        }
        
        return self::$_validatedPost[$key] = Validator::validateString($_POST[$key]);
    }
    
    public static function get($key = null) {
        if ($key === null) {
            if (self::$_isGetFullyValid === true) {
                return self::$_validatedGet;
            }
            
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                return self::$_validatedGet = Validator::validateStrip($_GET);
            }
            
            return self::$_validatedGet = Validator::validateArray($_GET);
        }
        
        if (isset(self::$_validatedGet[$key])) {
            return self::$_validatedGet[$key];
        }
        
        if (!isset($_GET[$key])) {
            throw new SystemException(Lang::tr('system', 'Undefined $_GET key "' . $key . '".'));
        }
        
        return self::$_validatedGet[$key] = Validator::validateString($_GET[$key]);
    }
    
    public static function request($key = null) {
        if ($key === null) {
            if (self::$_isRequestFullyValid === true) {
                return self::$_validatedRequest;
            }
            
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                return self::$_validatedRequest = Validator::validateStrip($_REQUEST);
            }
            
            return self::$_validatedRequest = Validator::validateArray($_REQUEST);
        }
        
        if (isset(self::$_validatedRequest[$key])) {
            return self::$_validatedRequest[$key];
        }
        
        if (!isset($_REQUEST[$key])) {
            throw new SystemException(Lang::tr('system', 'Undefined $_REQUEST key "' . $key . '".'));
        }
        
        return self::$_validatedRequest[$key] = Validator::validateString($_REQUEST[$key]);
    }
    
    public static function cookie($key = null) {
        if ($key === null) {
            if (self::$_isCookiesFullyValid === true) {
                return self::$_validatedCookies;
            }
            
            if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
                return self::$_validatedCookies = Validator::validateStrip($_COOKIE);
            }
            
            return self::$_validatedCookies = Validator::validateArray($_COOKIE);
        }
        
        if (isset(self::$_validatedCookies[$key])) {
            return self::$_validatedCookies[$key];
        }
        
        if (!isset($_COOKIE[$key])) {
            throw new SystemException(Lang::tr('system', 'Undefined $_COOKIE key "' . $key . '".'));
        }
        
        return self::$_validatedCookies[$key] = Validator::validateString($_COOKIE[$key]);
    }
    
}
