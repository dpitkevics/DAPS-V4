<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;
/**
 * Description of Event
 *
 * @author User
 */
class Event extends ApplicationComponent {
    
    public $sender;
    
    public $handled = false;
    
    public $params;
    
    public function __construct($sender = null, $params = null) {
        $this->sender = $sender;
        $this->params = $params;
    }
    
}
