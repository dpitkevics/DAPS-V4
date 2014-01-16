<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System\Web;

use System\Application as App;

/**
 * Description of WebApplication
 *
 * @author User
 */
class WebApplication extends App {

    public $catchAllRequest;
    public $charset = "utf-8";
    
    public function process() {
        \System\Base::setApp($this);
        
        if (is_array($this->catchAllRequest) && isset($this->catchAllRequest[0])) {
            $route = $this->catchAllRequest[0];
            foreach (array_splice($this->catchAllRequest, 1) as $name => $value)
                $_GET[$name] = $value;
        } else
            $route = $this->getRouter()->parseUrl($this->getHttp());
        //$this->runController($route);
    }
    
    public function getRouter() {
        return $this->getComponent('router');
    }

}
