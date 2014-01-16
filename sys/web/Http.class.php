<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System\Web;

/**
 * Description of Http
 *
 * @author User
 */
class Http extends \System\ApplicationPart {

    public $get;
    public $post;
    public $request;
    public $cookie;

    public function init() {
        parent::init();
        $this->normalizeRequest();
    }

    public function normalizeRequest() {
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            if (isset($_GET))
                $this->get = $this->encode($_GET);
            if (isset($_POST))
                $this->post = $this->encode($_POST);
            if (isset($_REQUEST))
                $this->request = $this->encode($_REQUEST);
            if (isset($_COOKIE))
                $this->cookie = $this->encode($_COOKIE);
        } else {
            if (isset($_GET))
                $this->get = $this->encodeSimple($_GET);
            if (isset($_POST))
                $this->post = $this->encodeSimple($_POST);
            if (isset($_REQUEST))
                $this->request = $this->encodeSimple($_REQUEST);
            if (isset($_COOKIE))
                $this->cookie = $this->encodeSimple($_COOKIE);
        }
    }

    public function encode(&$data) {
        if (is_array($data)) {
            if (count($data) == 0)
                return $data;
            $keys = array_map('stripslashes', array_keys($data));
            $data = array_combine($keys, array_values($data));
            return array_map(array($this, 'stripSlashes'), $data);
        } else
            return stripslashes($data);
    }

    public function encodeSingle($text) {
        return htmlspecialchars($text, ENT_QUOTES, \System\Base::a()->charset);
    }
    
    public function encodeSimple(array $array) {
        $resultArray = array();
        foreach ($array as $key => $elem) {
            $resultArray[$this->encodeSingle($key)] = $this->encodeSingle($elem);
        }
        return $resultArray;
    }

}
