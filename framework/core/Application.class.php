<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\core;

use base\libs\Event;
use base\libs\HttpException;
use base\libs\ExceptionEvent;
use base\libs\ErrorEvent;

/**
 * Description of Application
 *
 * @author User
 */
abstract class Application extends Component {

    private $_ended = false;

    abstract function process();

    public function __construct() {
        $this->init();
    }

    public function init() {
        
    }

    public function run() {
        if ($this->hasEventHandler('onStart')) {
            $this->onStart(new Event($this));
        }

        Ftk::setConfig();
        register_shutdown_function(array($this, 'end'));
        $this->initSystemHandlers();

        $this->process();
    }

    protected function initSystemHandlers() {
        if (FTK_ENABLE_EXCEPTION_HANDLER) {
            set_exception_handler(array($this, 'handleException'));
        } else if (FTK_ENABLE_ERROR_HANDLER) {
            set_error_handler(array($this, 'handleError'), error_reporting());
        }
    }

    public function handleException($exception) {
        // disable error capturing to avoid recursive errors
        restore_error_handler();
        restore_exception_handler();

        $category = 'exception.' . get_class($exception);
        if ($exception instanceof HttpException)
            $category.='.' . $exception->statusCode;
        // php <5.2 doesn't support string conversion auto-magically
        $message = $exception->__toString();
        if (isset($_SERVER['REQUEST_URI']))
            $message.="\nREQUEST_URI=" . $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTP_REFERER']))
            $message.="\nHTTP_REFERER=" . $_SERVER['HTTP_REFERER'];
        $message.="\n---";

        try {
            $event = new ExceptionEvent($this, $exception);
            $this->onException($event);
            if (!$event->handled) {
                // try an error handler
                if (($handler = $this->getErrorHandler()) !== null)
                    $handler->handle($event);
                else
                    $this->displayException($exception);
            }
        } catch (Exception $e) {
            $this->displayException($e);
        }

        try {
            $this->end(1);
        } catch (Exception $e) {
            // use the most primitive way to log error
            $msg = get_class($e) . ': ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
            $msg .= $e->getTraceAsString() . "\n";
            $msg .= "Previous exception:\n";
            $msg .= get_class($exception) . ': ' . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ")\n";
            $msg .= $exception->getTraceAsString() . "\n";
            $msg .= '$_SERVER=' . var_export($_SERVER, true);
            error_log($msg);
            exit(1);
        }
    }

    public function handleError($code, $message, $file, $line) {
        if ($code & error_reporting()) {
            // disable error capturing to avoid recursive errors
            restore_error_handler();
            restore_exception_handler();

            $log = "$message ($file:$line)\nStack trace:\n";
            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if (count($trace) > 3)
                $trace = array_slice($trace, 3);
            foreach ($trace as $i => $t) {
                if (!isset($t['file']))
                    $t['file'] = 'unknown';
                if (!isset($t['line']))
                    $t['line'] = 0;
                if (!isset($t['function']))
                    $t['function'] = 'unknown';
                $log.="#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) && is_object($t['object']))
                    $log.=get_class($t['object']) . '->';
                $log.="{$t['function']}()\n";
            }
            if (isset($_SERVER['REQUEST_URI']))
                $log.='REQUEST_URI=' . $_SERVER['REQUEST_URI'];

            try {
                $event = new ErrorEvent($this, $code, $message, $file, $line);
                $this->onError($event);
                if (!$event->handled) {
                    // try an error handler
                    if (($handler = $this->getErrorHandler()) !== null)
                        $handler->handle($event);
                    else
                        $this->displayError($code, $message, $file, $line);
                }
            } catch (Exception $e) {
                $this->displayException($e);
            }

            try {
                $this->end(1);
            } catch (Exception $e) {
                // use the most primitive way to log error
                $msg = get_class($e) . ': ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
                $msg .= $e->getTraceAsString() . "\n";
                $msg .= "Previous error:\n";
                $msg .= $log . "\n";
                $msg .= '$_SERVER=' . var_export($_SERVER, true);
                error_log($msg);
                exit(1);
            }
        }
    }

    public function onException($event) {
        $this->raiseEvent('onException', $event);
    }

    public function onError($event) {
        $this->raiseEvent('onError', $event);
    }

    public function displayError($code, $message, $file, $line) {
        if (FTK_DEBUG) {
            echo "<h1>PHP Error [$code]</h1>\n";
            echo "<p>$message ($file:$line)</p>\n";
            echo '<pre>';

            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if (count($trace) > 3)
                $trace = array_slice($trace, 3);
            foreach ($trace as $i => $t) {
                if (!isset($t['file']))
                    $t['file'] = 'unknown';
                if (!isset($t['line']))
                    $t['line'] = 0;
                if (!isset($t['function']))
                    $t['function'] = 'unknown';
                echo "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) && is_object($t['object']))
                    echo get_class($t['object']) . '->';
                echo "{$t['function']}()\n";
            }

            echo '</pre>';
        }
        else {
            echo "<h1>PHP Error [$code]</h1>\n";
            echo "<p>$message</p>\n";
        }
    }

    public function displayException($exception) {
        if (FTK_DEBUG) {
            echo '<h1>' . get_class($exception) . "</h1>\n";
            echo '<p>' . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')</p>';
            echo '<pre>' . $exception->getTraceAsString() . '</pre>';
        } else {
            echo '<h1>' . get_class($exception) . "</h1>\n";
            echo '<p>' . $exception->getMessage() . '</p>';
        }
    }

    public function end($status = 0, $exit = true) {
        if ($this->hasEventHandler('onEnd')) {
            $this->onEnd(new Event($this));
        }

        if ($exit) {
            exit($status);
        }
    }

    public function onProcessStart(Event $event) {
        $this->raiseEvent('onProcessStart', $event);
    }

    public function onProcessEnd(Event $event) {
        $this->raiseEvent('onProcessEnd', $event);
    }

    public function onStart(Event $event) {
        $this->raiseEvent('onStart', $event);
    }

    public function onEnd(Event $event) {
        if (!$this->_ended) {
            $this->_ended = true;
            $this->raiseEvent('onEnd', $event);
        }
    }

}
