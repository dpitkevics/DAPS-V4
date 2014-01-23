<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;

/**
 * Description of ErrorHandler
 *
 * @author User
 */
class ErrorHandler extends ApplicationComponent {

    public $maxSourceLines = 25;
    public $maxTraceSourceLines = 10;
    public $adminInfo = 'the webmaster';
    public $discardOutput = true;
    public $errorAction;
    private $_error;
    private $_exception;

    public function handle($event) {
        // set event as handled to prevent it from being handled by other event handlers
        $event->handled = true;

        if ($this->discardOutput) {
            $gzHandler = false;
            foreach (ob_list_handlers() as $h) {
                if (strpos($h, 'gzhandler') !== false)
                    $gzHandler = true;
            }
            // the following manual level counting is to deal with zlib.output_compression set to On
            // for an output buffer created by zlib.output_compression set to On ob_end_clean will fail
            for ($level = ob_get_level(); $level > 0; --$level) {
                if (!@ob_end_clean())
                    ob_clean();
            }
            // reset headers in case there was an ob_start("ob_gzhandler") before
            if ($gzHandler && !headers_sent() && ob_list_handlers() === array()) {
                if (function_exists('header_remove')) { // php >= 5.3
                    header_remove('Vary');
                    header_remove('Content-Encoding');
                } else {
                    header('Vary:');
                    header('Content-Encoding:');
                }
            }
        }

        if ($event instanceof ExceptionEvent)
            $this->handleException($event->exception);
        else // CErrorEvent
            $this->handleError($event);
    }

    protected function handleException($exception) {
        if (($trace = $this->getExactTrace($exception)) === null) {
            $fileName = $exception->getFile();
            $errorLine = $exception->getLine();
        } else {
            $fileName = $trace['file'];
            $errorLine = $trace['line'];
        }

        $trace = $exception->getTrace();

        foreach ($trace as $i => $t) {
            if (!isset($t['file']))
                $trace[$i]['file'] = 'unknown';

            if (!isset($t['line']))
                $trace[$i]['line'] = 0;

            if (!isset($t['function']))
                $trace[$i]['function'] = 'unknown';

            unset($trace[$i]['object']);
        }

        $this->_exception = $exception;
        $this->_error = $data = array(
            'code' => ($exception instanceof HttpException) ? $exception->statusCode : 500,
            'type' => get_class($exception),
            'errorCode' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $fileName,
            'line' => $errorLine,
            'trace' => $exception->getTraceAsString(),
            'traces' => $trace,
        );

        if (!headers_sent())
            header("HTTP/1.0 {$data['code']} " . $this->getHttpHeader($data['code'], get_class($exception)));

        $this->renderException();
    }

    protected function handleError($event) {
        $trace = debug_backtrace();
        // skip the first 3 stacks as they do not tell the error position
        if (count($trace) > 3)
            $trace = array_slice($trace, 3);
        $traceString = '';
        foreach ($trace as $i => $t) {
            if (!isset($t['file']))
                $trace[$i]['file'] = 'unknown';

            if (!isset($t['line']))
                $trace[$i]['line'] = 0;

            if (!isset($t['function']))
                $trace[$i]['function'] = 'unknown';

            $traceString.="#$i {$trace[$i]['file']}({$trace[$i]['line']}): ";
            if (isset($t['object']) && is_object($t['object']))
                $traceString.=get_class($t['object']) . '->';
            $traceString.="{$trace[$i]['function']}()\n";

            unset($trace[$i]['object']);
        }

        switch ($event->code) {
            case E_WARNING:
                $type = 'PHP warning';
                break;
            case E_NOTICE:
                $type = 'PHP notice';
                break;
            case E_USER_ERROR:
                $type = 'User error';
                break;
            case E_USER_WARNING:
                $type = 'User warning';
                break;
            case E_USER_NOTICE:
                $type = 'User notice';
                break;
            case E_RECOVERABLE_ERROR:
                $type = 'Recoverable error';
                break;
            default:
                $type = 'PHP error';
        }
        $this->_exception = null;
        $this->_error = array(
            'code' => 500,
            'type' => $type,
            'message' => $event->message,
            'file' => $event->file,
            'line' => $event->line,
            'trace' => $traceString,
            'traces' => $trace,
        );
        if (!headers_sent())
            header("HTTP/1.0 500 Internal Server Error");
        $this->renderError();
    }

    protected function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function getExactTrace($exception) {
        $traces = $exception->getTrace();

        foreach ($traces as $trace) {
            // property access exception
            if (isset($trace['function']) && ($trace['function'] === '__get' || $trace['function'] === '__set'))
                return $trace;
        }
        return null;
    }

    protected function renderError() {
        $data = $this->getError();
        if ($this->isAjaxRequest()) {
            \base\helpers\Debug::dump($data);
        } else if (YII_DEBUG) {
            $this->render('exception', $data);
        } else {
            $this->render('error', $data);
        }
    }

    protected function render($view, $data) {
        $data['version'] = $this->getVersionInfo();
        $data['time'] = time();
        $data['admin'] = $this->adminInfo;
        \base\helpers\Debug::dump($data);
    }

}
