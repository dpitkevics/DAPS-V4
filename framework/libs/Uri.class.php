<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;

/**
 * Description of Uri
 *
 * @author User
 */
class Uri extends ApplicationComponent {
    
    public function __construct() {
        parent::__construct();
        
        \base\helpers\Debug::dump(Input::get('p'));
    }
    
}
