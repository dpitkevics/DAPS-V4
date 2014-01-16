<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System\Web;

/**
 * Description of Router
 *
 * @author User
 */
class Router extends \System\ApplicationPart {
    
    public function __construct() {
        return true;
    }
    
    public function parseUrl(Http $http) {
        \System\Base::dump($http);
    }
    
}
