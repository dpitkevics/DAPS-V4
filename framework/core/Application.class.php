<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\core;

/**
 * Description of Application
 *
 * @author User
 */
abstract class Application extends Component {
    
    abstract function process();
    
    public function __construct() {
        $this->init();
    }
    
    public function init() {
    }
    
}
