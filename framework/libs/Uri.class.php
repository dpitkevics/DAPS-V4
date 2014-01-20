<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;

/**
 * Description of Uri
 *
 * @author User
 */
class Uri extends ApplicationComponent {
    
    private $_p = null;
    private $_routes = array();

    public $routes = array();
    
    public function __construct() {
        parent::__construct();
        
        if ($this->_p === null) {
            $this->_p = (Input::exists('get', 'p') ? Input::get('p') : null);
        }
    }
    
    public function parseRoutes() {
        $this->routes = array_merge($this->routes, $this->getConfig('routes'));
        \base\helpers\Debug::dump($this->routes);
        foreach ($this->routes as $routePattern => $route) {
            $this->_routes[] = $this->createRoute($routePattern, $route);
        }
    }
    
    public function createRoute ($routePattern, $route) {
        return new Route($routePattern, $route);
    }
    
    public function parseUrl() {
        $urlParts = explode('/', $this->_p);
    }
    
}
