<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

/**
 * Description of ItemClass
 *
 * @author Daniels
 */
class Item {

    private $_events;
    private $_parts;
    private $_config = array();

    public function __get($name) {
        $getter = "get" . $name;
        if (method_exists($this, $getter))
            return $this->$getter();
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            $name = strtolower($name);
            if (!isset($this->_events[$name]))
                $this->_events[$name] = new EventList();
            return $this->events[$name];
        } else if (isset($this->_parts[$name]))
            return $this->_parts[$name];
        else if (is_array($this->_parts)) {
            foreach ($this->_parts as $object) {
                if ($object->getEnabled() && (property_exists($object, $name) || $object->canGetProperty($name)))
                    return $object->$name;
            }
        }

        throw new SystemException(Lang::tr('sys', 'Property "{class}.{property}" is not defined.', array('{class}' => get_class($this), '{property}' => $name)));
    }

    public function __set($name, $value) {
        $setter = "set" . $name;
        if (method_exists($this, $setter))
            return $this->$setter();
        else if (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            $name = strtolower($name);
            if (!isset($this->_events[$name]))
                $this->_events[$name] = new EventList();
            return $this->_events[$name]->add($value);
        } else if (is_array($this->_parts)) {
            foreach ($this->_parts as $object) {
                if ($object->getEnabled() && (property_exists($object, $name) || $object->canSetProperty($name)))
                    return $object->$name = $value;
            }
        } else if (strncasecmp($name, 'conf-', 5) === 0)
                return $this->_config[substr ($name, 5)] = $value;
        if (method_exists($this, 'get' . $name))
            throw new SystemException(Lang::tr('sys', 'Property "{class}.{property}" is read only.', array('{class}' => get_class($this), '{property}' => $name)));
        else
            throw new SystemException(Lang::tr('sys', 'Property "{class}.{property}" is not defined.', array('{class}' => get_class($this), '{property}' => $name)));
    }

    public function __isset($name) {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter() !== null;
        elseif (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name)) {
            $name = strtolower($name);
            return isset($this->_events[$name]) && $this->_events[$name]->getCount();
        } elseif (is_array($this->_parts)) {
            if (isset($this->_parts[$name]))
                return true;
            foreach ($this->_parts as $object) {
                if ($object->getEnabled() && (property_exists($object, $name) || $object->canGetProperty($name)))
                    return $object->$name !== null;
            }
        }
        return false;
    }

    public function __unset($name) {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            $this->$setter(null);
        elseif (strncasecmp($name, 'on', 2) === 0 && method_exists($this, $name))
            unset($this->_events[strtolower($name)]);
        elseif (is_array($this->_parts)) {
            if (isset($this->_parts[$name]))
                $this->detachBehavior($name);
            else {
                foreach ($this->_parts as $object) {
                    if ($object->getEnabled()) {
                        if (property_exists($object, $name))
                            return $object->$name = null;
                        elseif ($object->canSetProperty($name))
                            return $object->$setter(null);
                    }
                }
            }
        }
        elseif (method_exists($this, 'get' . $name))
            throw new SystemException(Lang::tr('sys', 'Property "{class}.{property}" is read only.', array('{class}' => get_class($this), '{property}' => $name)));
    }

    public function __call($name, $parameters) {
        if ($this->_parts !== null) {
            foreach ($this->_parts as $object) {
                if ($object->getEnabled() && method_exists($object, $name))
                    return call_user_func_array(array($object, $name), $parameters);
            }
        }
        if (class_exists('Closure', false) && $this->canGetProperty($name) && $this->$name instanceof Closure)
            return call_user_func_array($this->$name, $parameters);
        throw new SystemException(Lang::tr('sys', '{class} and its behaviors do not have a method or closure named "{name}".', array('{class}' => get_class($this), '{name}' => $name)));
    }

    public function returnBehavior($behavior) {
        return (isset($this->_parts[$behavior])) ? $this->_parts[$behavior] : null;
    }

    public function attachBehaviors($behaviors) {
        foreach ($behaviors as $name => $behavior)
            $this->attachBehavior($name, $behavior);
    }

    public function attachBehavior($name, $behavior) {
        if (!($behavior instanceof BehaviorInterface))
            $behavior = Base::createComponent($behavior);
        $behavior->setEnabled(true);
        $behavior->attach($this);
        return $this->_parts[$name] = $behavior;
    }

    public function detachBehaviors() {
        if ($this->_parts !== null) {
            foreach ($this->_parts as $name => $behavior)
                $this->detachBehavior($name);
            $this->_parts = null;
        }
    }

    public function detachBehavior($name) {
        if (isset($this->_parts[$name])) {
            $this->_parts[$name]->detach($this);
            $behavior = $this->_parts[$name];
            unset($this->_parts[$name]);
            return $behavior;
        }
    }

    public function enableBehaviors() {
        if ($this->_parts !== null) {
            foreach ($this->_parts as $behavior)
                $behavior->setEnabled(true);
        }
    }

    public function enableBehavior($name) {
        if (isset($this->_parts[$name]))
            $this->_parts[$name]->setEnabled(true);
    }

    public function disableBehaviors() {
        if ($this->_parts !== null) {
            foreach ($this->_parts as $behavior)
                $behavior->setEnabled(false);
        }
    }

    public function disableBehavior($name) {
        if (isset($this->_parts[$name]))
            $this->_parts[$name]->setEnabled(false);
    }

    public function hasProperty($name) {
        return method_exists($this, 'get' . $name) || method_exists($this, 'set' . $name);
    }

    public function canGetProperty($name) {
        return method_exists($this, 'get' . $name);
    }

    public function canSetProperty($name) {
        return method_exists($this, 'set' . $name);
    }

    public function hasEvent($name) {
        return !strncasecmp($name, 'on', 2) && method_exists($this, $name);
    }

    public function hasEventHandler($name) {
        $name = strtolower($name);
        return isset($this->_events[$name]) && $this->_events[$name]->getCount() > 0;
    }

    public function getEventHandlers($name) {
        if ($this->hasEvent($name)) {
            $name = strtolower($name);
            if (!isset($this->_events[$name]))
                $this->_events[$name] = new EventList();
            return $this->_events[$name];
        } else
            throw new SystemException(Lang::tr('sys', 'Event "{class}.{event}" is not defined.', array('{class}' => get_class($this), '{event}' => $name)));
    }

    public function attachEventHandler($name, $handler) {
        $this->getEventHandlers($name)->add($handler);
    }

    public function detachEventHandler($name, $handler) {
        if ($this->hasEventHandler($name))
            return $this->getEventHandlers($name)->remove($handler) !== false;
        else
            return false;
    }

    public function raiseEvent($name, $event) {
        $name = strtolower($name);
        if (isset($this->_events[$name])) {
            foreach ($this->_events[$name] as $handler) {
                if (is_string($handler))
                    call_user_func($handler, $event);
                elseif (is_callable($handler, true)) {
                    if (is_array($handler)) {
                        // an array: 0 - object, 1 - method name
                        list($object, $method) = $handler;
                        if (is_string($object))        // static method call
                            call_user_func($handler, $event);
                        elseif (method_exists($object, $method))
                            $object->$method($event);
                        else
                            throw new SystemException(Lang::tr('sys', 'Event "{class}.{event}" is attached with an invalid handler "{handler}".', array('{class}' => get_class($this), '{event}' => $name, '{handler}' => $handler[1])));
                    } else // PHP 5.3: anonymous function
                        call_user_func($handler, $event);
                } else
                    throw new SystemException(Lang::tr('sys', 'Event "{class}.{event}" is attached with an invalid handler "{handler}".', array('{class}' => get_class($this), '{event}' => $name, '{handler}' => gettype($handler))));
                // stop further handling if param.handled is set true
                if (($event instanceof EventClass) && $event->handled)
                    return;
            }
        }
        elseif (APP_DEBUG && !$this->hasEvent($name))
            throw new SystemException(Lang::tr('sys', 'Event "{class}.{event}" is not defined.', array('{class}' => get_class($this), '{event}' => $name)));
    }

    public function evaluateExpression($_expression_, $_data_ = array()) {
        if (is_string($_expression_)) {
            extract($_data_);
            return eval('return ' . $_expression_ . ';');
        } else {
            $_data_[] = $this;
            return call_user_func_array($_expression_, $_data_);
        }
    }

}

class Event extends Item {
    
    public $sender;
    
    public $handled = false;
    
    public $params;
    
    public function __construct($sender = null, $params = null) {
        $this->sender = $sender;
        $this->params = $params;
    }
    
}

class Enumerable {}
