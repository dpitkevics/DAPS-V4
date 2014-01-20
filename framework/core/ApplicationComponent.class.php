<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\core;

/**
 * Description of ApplicationComponent
 *
 * @author User
 */
class ApplicationComponent extends Component {
    
    private $_cid;
    
    public function __construct() {
        $this->init();
    }
    
    protected function init() {
        $this->_cid = substr(md5(rand(10000000, 99999999) . date('YmdHis')), 20);
    }
}
