<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

/**
 * Description of ExceptionEvent
 *
 * @author User
 */
class ExceptionEvent extends Event
{
        /**
         * @var CException the exception that this event is about.
         */
        public $exception;

        /**
         * Constructor.
         * @param mixed $sender sender of the event
         * @param CException $exception the exception
         */
        public function __construct($sender,$exception)
        {
                $this->exception=$exception;
                parent::__construct($sender);
        }
}
