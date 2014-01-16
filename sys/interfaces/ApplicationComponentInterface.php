<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System\Interfaces;

/**
 *
 * @author User
 */
interface ApplicationComponentInterface {

    public function init();

    public function getIsInitialized();
}
