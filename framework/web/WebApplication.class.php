<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\web;

use base\core\Application;
use base\libs\Uri;
use base\libs\Request;
/**
 * Description of WebApp
 *
 * @author User
 */
class WebApplication extends Application {
    
    private $_uri = null;
    
    public function process() {
        if ($this->hasEventHandler('onProcessStart')) {
            $this->onProcessStart(new Event($this));
        }
        
        $uri = new Uri();
        $this->_uri = $uri->parseUrl(new Request());
        \base\helpers\Debug::dump($this);
        
        if ($this->hasEventHandler('onProcessEnd')) {
            $this->onProcessEnd(new Event($this));
        }
    }
    
}
