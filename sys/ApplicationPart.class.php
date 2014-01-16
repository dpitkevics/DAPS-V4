<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

use System\Interfaces;
/**
 * Description of ApplicationPart
 *
 * @author User
 */
abstract class ApplicationPart extends Item implements Interfaces\ApplicationComponentInterface
{
        /**
         * @var array the behaviors that should be attached to this component.
         * The behaviors will be attached to the component when {@link init} is called.
         * Please refer to {@link CModel::behaviors} on how to specify the value of this property.
         */
        public $behaviors=array();

        private $_initialized=false;

        /**
         * Initializes the application component.
         * This method is required by {@link IApplicationComponent} and is invoked by application.
         * If you override this method, make sure to call the parent implementation
         * so that the application component can be marked as initialized.
         */
        public function init()
        {
                $this->attachBehaviors($this->behaviors);
                $this->_initialized=true;
        }

        /**
         * Checks if this application component has been initialized.
         * @return boolean whether this application component has been initialized (ie, {@link init()} is invoked).
         */
        public function getIsInitialized()
        {
                return $this->_initialized;
        }
}
