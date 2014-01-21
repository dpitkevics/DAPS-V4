<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;

/**
 * Description of Request
 *
 * @author User
 */
class Request extends ApplicationComponent {

    private $_pathInfo = null;
    private $_requestUri = null;
    private $_scriptUrl = null;
    private $_baseUrl = null;

    public function getBaseUrl($absolute = false) {
        if ($this->_baseUrl === null)
            $this->_baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/');
        return $absolute ? $this->getHostInfo() . $this->_baseUrl : $this->_baseUrl;
    }

    public function getScriptUrl() {
        if ($this->_scriptUrl === null) {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if (basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
                $this->_scriptUrl = $_SERVER['SCRIPT_NAME'];
            } elseif (basename($_SERVER['PHP_SELF']) === $scriptName) {
                $this->_scriptUrl = $_SERVER['PHP_SELF'];
            } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
                $this->_scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            } elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
                $this->_scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            } elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
                $this->_scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            } else {
                throw new SystemException(Lang::tr('system', 'Request is unable to determine script URL'));
            }
        }
        return $this->_scriptUrl;
    }

    public function getPathInfo() {

        if ($this->_pathInfo === null) {
            $pathInfo = $this->getRequestUri();

            if (($pos = strpos($pathInfo, '?')) !== false) {
                $pathInfo = substr($pathInfo, 0, $pos);
            }

            $pathInfo = $this->decodePathInfo($pathInfo);
            $scriptUrl = $this->getScriptUrl();
            $baseUrl = $this->getBaseUrl();
            if (strpos($pathInfo, $scriptUrl) === 0) {
                $pathInfo = substr($pathInfo, strlen($scriptUrl));
            } else if ($baseUrl === '' || strpos($pathInfo, $baseUrl) === 0) {
                $pathInfo = substr($pathInfo, strlen($baseUrl));
            } else if (strpos($_SERVER['PHP_SELF'], $scriptUrl) === 0) {
                $pathInfo = substr($_SERVER['PHP_SELF'], strlen($scriptUrl));
            } else {
                throw new SystemException(Lang::tr('system', 'Request is unable to determine path info'));
            }

            if ($pathInfo === '/') {
                $pathInfo = '';
            } else if ($pathInfo[0] === '/') {
                $pathInfo = substr($pathInfo, 1);
            }

            if (($posEnd = strlen($pathInfo) - 1) > 0 && $pathInfo[$posEnd] === '/') {
                $pathInfo = substr($pathInfo, 0, $posEnd);
            }

            $this->_pathInfo = $pathInfo;
        }

        return $this->_pathInfo;
    }

    protected function decodePathInfo($pathInfo) {
        $pathInfo = urldecode($pathInfo);

        if (preg_match('%^(?:
                   [\x09\x0A\x0D\x20-\x7E]            # ASCII
                 | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                 | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
                 | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                 | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
                 | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
                 | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                 | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
                )*$%xs', $pathInfo)) {
            return $pathInfo;
        } else {
            return utf8_encode($pathInfo);
        }
    }

    public function getRequestUri() {
        if ($this->_requestUri === null) {
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
                $this->_requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
            } else if (isset($_SERVER['REQUEST_URI'])) {
                $this->_requestUri = $_SERVER['REQUEST_URI'];
                if (!empty($_SERVER['HTTP_HOST'])) {
                    if (strpos($this->_requestUri, $_SERVER['HTTP_HOST']) !== false) {
                        $this->_requestUri = preg_replace('/^\w+:\/\/[^\/]+/', '', $this->_requestUri);
                    }
                } else {
                    $this->_requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $this->_requestUri);
                }
            } else if (isset($_SERVER['ORIG_PATH_INFO'])) {
                $this->_requestUri = $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $this->_requestUri .= '?' . $_SERVER['QUERY_STRING'];
                }
            } else {
                throw new SystemException(Lang::tr('system', 'Request is unable to determine requested URI'));
            }
        }

        return $this->_requestUri;
    }

    public function getRequestType() {
        if (isset($_POST['_method']))
            return strtoupper($_POST['_method']);

        return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }

}
