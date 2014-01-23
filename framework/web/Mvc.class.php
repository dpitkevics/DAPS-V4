<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\web;

use base\core\ApplicationComponent;
/**
 * Description of Mvc
 *
 * @author User
 */
class Mvc extends ApplicationComponent {
    
    private $_module = null;
    private $_controller = null;
    private $_action = null;
    
    public function __construct($controllerId, $actionId, $moduleId = null) {
        parent::__construct();
        if ($moduleId !== null) {
            $this->_module = new Module($moduleId);
        }
        $this->_controller = new Controller($controllerId);
        $this->_action = call_user_func(array($this->_controller, $actionId));
    }
    
}
