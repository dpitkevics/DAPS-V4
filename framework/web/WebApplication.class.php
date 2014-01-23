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
use base\core\Ftk;
use base\libs\SystemException;
use base\libs\Lang;
/**
 * Description of WebApp
 *
 * @author User
 */
class WebApplication extends Application {
    
    private $_uri = null;
    
    private $_mvc;
    
    public function process() {
        if ($this->hasEventHandler('onProcessStart')) {
            $this->onProcessStart(new Event($this));
        }
        
        $uri = new Uri();
        $this->_uri = $uri->parseUrl(new Request());

        $this->makeMvc();
        
        if ($this->hasEventHandler('onProcessEnd')) {
            $this->onProcessEnd(new Event($this));
        }
    }
    
    public function makeMvc() {
        $uriParts = explode('/', $this->_uri);
        if (count($uriParts) % 2 === 1) { // MODULE IS USED
            if (!in_array($uriParts[0], Ftk::getConfig('modules'))) {
                throw new SystemException(Lang::tr('system', 'Module "'.$uriParts[0].'" not found. Have You enabled it?'));
            }
            $this->_mvc = new Mvc($uriParts[1], $uriParts[2], $uriParts[0]);
        } else { // MODULE IS NOT USED 
            $this->_mvc = new Mvc($uriParts[0], $uriParts[1]);
        }
        \base\helpers\Debug::dump($this->_mvc);
    }
    
}
