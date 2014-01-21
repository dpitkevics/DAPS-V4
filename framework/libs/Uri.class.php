<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;
use base\core\Ftk;

/**
 * Description of Uri
 *
 * @author User
 */
class Uri extends ApplicationComponent {

    private $_p = null;
    private $_routes = array();
    private $_routeVar = 'p';
    public $caseSensitive = true;
    public $useStrictParsing = false;
    public $urlSuffix = 'html';
    public $routes = array();

    public function __construct() {
        parent::__construct();
    }

    public function init() {
        parent::init();

        if ($this->_p === null) {
            $this->_p = (Input::exists('get', 'p') ? Input::get('p') : null);
        }
        $this->parseRoutes();
    }

    public function parseRoutes() {
        $this->routes = array_merge($this->routes, Ftk::getConfig('routes'));

        foreach ($this->routes as $routePattern => $route) {
            $this->_routes[] = $this->createRoute($routePattern, $route);
        }
    }

    public function createRoute($routePattern, $route) {
        return new Route($routePattern, $route);
    }

    public function parseUrl(Request $request) {
        $rawPathInfo = $request->getPathInfo();
        $pathInfo = $this->removeUrlSuffix($rawPathInfo, $this->urlSuffix);
        foreach ($this->_routes as $i => $route) {
            if (($r = $route->parseUrl($this, $request, $pathInfo, $rawPathInfo)) !== false) {
                return (Input::exists('get', $this->_routeVar) && strpos(trim($this->_routeVar, '/'), '/')) ? Input::get($this->_routeVar) : $r;
            }
        }
        if (Input::exists('get', $this->_routeVar)) {
            return Input::get($this->_routeVar);
        } else if (Input::exists('post', $this->_routeVar)) {
            return Input::post($this->_routeVar);
        } else {
            return '';
        }
    }

    public function removeUrlSuffix($pathInfo, $urlSuffix) {
        if ($urlSuffix !== '' && substr($pathInfo, -strlen($urlSuffix)) === $urlSuffix)
            return substr($pathInfo, 0, -strlen($urlSuffix));
        else
            return $pathInfo;
    }

}
