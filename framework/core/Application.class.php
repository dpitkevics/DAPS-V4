<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\core;

use base\libs\Event;

/**
 * Description of Application
 *
 * @author User
 */
abstract class Application extends Component {
    
    private $_ended = false;
    
    abstract function process();
    
    public function __construct() {
        $this->init();
    }
    
    public function init() {
    }
    
    public function run() {
        if ($this->hasEventHandler('onStart')) {
            $this->onStart(new Event($this));
        }
        
        $this->_config = $this->processConfig();
        
        register_shutdown_function(array($this, 'end'));
        
        $this->process();
    }
    
    public function end($status = 0, $exit = true) {
        if ($this->hasEventHandler('onEnd')) {
            $this->onEnd(new Event($this));
        }
        
        if ($exit) {
            exit($status);
        }
    }
    
    public function processConfig () {
        $configDir = FTK_ROOT . DS . 'app' . DS . 'setup' . DS . 'config.php';
        return include_once $configDir;
    }
    
    public function onProcessStart(Event $event) {
        $this->raiseEvent('onProcessStart', $event);
    }
    
    public function onProcessEnd(Event $event) {
        $this->raiseEvent('onProcessEnd', $event);
    }
    
    public function onStart(Event $event) {
        $this->raiseEvent('onStart', $event);
    }
    
    public function onEnd(Event $event) {
        if (!$this->_ended) {
            $this->_ended = true;
            $this->raiseEvent('onEnd', $event);
        }
    }
    
}
