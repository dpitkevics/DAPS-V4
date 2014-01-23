<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

/**
 * Description of HttpException
 *
 * @author User
 */
class HttpException extends SystemException {

    public $statusCode;

    /**
     * Constructor.
     * @param integer $status HTTP status code, such as 404, 500, etc.
     * @param string $message error message
     * @param integer $code error code
     */
    public function __construct($status, $message = null, $code = 0) {
        $this->statusCode = $status;
        parent::__construct($message, $code);
    }

}
