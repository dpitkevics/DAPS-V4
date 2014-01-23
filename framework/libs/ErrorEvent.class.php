<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

/**
 * Description of ErrorEvent
 *
 * @author User
 */
class ErrorEvent extends Event {

    public $code;

    /**
     * @var string error message
     */
    public $message;

    /**
     * @var string error message
     */
    public $file;

    /**
     * @var string error file
     */
    public $line;

    /**
     * Constructor.
     * @param mixed $sender sender of the event
     * @param string $code error code
     * @param string $message error message
     * @param string $file error file
     * @param integer $line error line
     */
    public function __construct($sender, $code, $message, $file, $line) {
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        parent::__construct($sender);
    }

}
