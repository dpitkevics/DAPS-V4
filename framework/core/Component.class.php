<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\core;

use base\libs\Event;
use base\libs\Lister;
use base\libs\SystemException;
use base\libs\Lang;

/**
 * Description of Component
 *
 * @author User
 */
class Component {

    private $_events = array();
    private $_data = array();
    private $_objects;
    protected $_config = array();

    public function __get($name) {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            if (!isset($this->_events[$name])) {
                $this->_events[$name] = new Lister();
            }
            return $this->_events[$name];
        } else if (isset($this->_data[$name])) {
            return $this->_data[$name];
        } else if (is_array($this->_objects)) {
            foreach ($this->_objects as $object) {
                if ($object->isEnabled() && (property_exists($object, $name) || $object->canGetProperty($name))) {
                    return $object->$name;
                }
            }
        }

        throw new SystemException(Lang::tr('system', 'Property "{class}.{property}" is not defined.', array('{class}' => get_class($this), '{property}' => $name)));
    }

    public function __set($name, $value) {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            return $this->$setter();
        } else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            if (!isset($this->_events[$name])) {
                $this->_events[$name] = new Lister();
            }
            return $this->_events[$name]->add($value);
        } else if (is_array($this->_objects)) {
            foreach ($this->_objects as $object) {
                if ($object->isEnabled() && (property_exists($object, $name) || $object->canSetProperty($name))) {
                    return $object->$name = $value;
                }
            }
        } else if (strncasecmp($name, 'conf-', 5) === 0) {
            return $this->_config[substr($name, 5)] = $value;
        } else {
            return $this->_data[$name] = $value;
        }
    }

    public function __call($name, $arguments) {
        if (is_array($this->_objects)) {
            foreach ($this->_objects as $object) {
                if ($object->isEnabled() && method_exists($object, $name)) {
                    return call_user_func_array(array($object, $name), $arguments);
                }
            }
        }

        if (class_exists('Closure', false) && $this->canGetProperty($name) && $this->$name instanceof Closure) {
            return call_user_func_array($this->$name, $parameters);
        }
        
        throw new SystemException(Lang::tr('system', '{class} and its behaviors do not have a method or closure named "{name}".', array('{class}' => get_class($this), '{name}' => $name)));
    }
    
    public function getConfig($key = null) {
        if ($key === null) {
            return $this->_config;
        }

        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }
        throw new SystemException(Lang::tr('system', '"' . $key . '" is not specified in config file.'));
    }
    
    public function hasEvent($name) {
        return !strncasecmp($name, 'on', 2) && method_exists($this, $name);
    }
    
    public function hasEventHandler($name) {
        return isset($this->_events[$name]) && $this->_events[$name]->getCount() > 0;
    }
    
    public function getEventHandlers($name) {
        if ($this->hasEvent($name)) {
            if (!isset($this->_events[$name])) {
                $this->_events[$name] = new Lister();
            }
            return $this->_events[$name];
        } else {
            throw new SystemException(Lang::tr('system', 'Event "{class}.{event}" is not defined.', array('{class}' => get_class($this), '{event}' => $name)));
        }
    }
    
    public function attachEventHandler($name, $handler) {
        $this->getEventHandlers($name)->add($handler);
    }
    
    public function detachEventHandler($name, $handler) {
        if ($this->hasEventHandler($name)) {
            return $this->getEventHandlers($name)->remove($handler) !== false;
        } else {
            return false;
        }
    }
    
    public function raiseEvent($name, $event) {
        if (isset($this->_events[$name])) {
            foreach ($this->_events[$name]->getHandlers() as $handler) {
                if (is_string($handler)) {
                    call_user_func($handler, $event);
                } else if (is_callable($handler, true)) {
                    if (is_array($handler)) {
                        list($object, $method) = $handler;
                        if (is_string($object)) {
                            call_user_func($handler, $event);
                        } else if (method_exists($object, $method)) {
                            $object->$method($event);
                        } else {
                            throw new SystemException(Lang::tr('system', 'Event "{class}.{event}" is attached with an invalid handler "{handler}".', array('{class}' => get_class($this), '{event}' => $name, '{handler}' => $handler[1])));
                        }
                    } else {
                        call_user_func($handler, $event);
                    }
                } else {
                    throw new SystemException(Lang::tr('system', 'Event "{class}.{event}" is attached with an invalid handler "{handler}".', array('{class}' => get_class($this), '{event}' => $name, '{handler}' => gettype($handler))));
                } 
                
                if ($event instanceof Event && $event->handled) {
                    return true;
                }
            }
        } else if (FTK_DEBUG && !$this->hasEvent($name)) {
            throw new SystemException(Lang::tr('system', 'Event "{class}.{event}" is not defined.', array('{class}' => get_class($this), '{event}' => $name)));
        }
    }

}
