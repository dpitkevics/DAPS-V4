<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace System;

/**
 * Description of Module
 *
 * @author User
 */
class Module extends Item {

    public $preload = array();
    public $behaiors = array();
    private $_id;
    private $_parentModule;
    private $_basePath;
    private $_modulePath;
    private $_params;
    private $_modules = array();
    private $_moduleConfig = array();
    private $_components = array();
    private $_componentConfig = array();

    public function __construct($id, $parent, $config = null) {
        $this->_id = $id;
        $this->_parentModule = $parent;

        if ($config === null || !is_array($config)) {
            $config = include APP_DIR . '/setup/config.php';
        }

        $this->configure($config);
        $this->attachBehaviors($this->behaiors);

        $this->preloadComponents();
    }

    public function __get($name) {
        if ($this->hasComponent($name))
            return $this->getComponent($name);
        else
            return parent::__get($name);
    }

    public function __isset($name) {
        if ($this->hasComponent($name))
            return $this->getComponent($name) !== null;
        else
            return parent::__isset($name);
    }

    public function getId() {
        return $this->_id;
    }

    public function setId($id) {
        $this->_id = $id;
    }

    public function getBasePath() {
        if ($this->_basePath === null) {
            $class = new ReflectionClass(get_class($this));
            $this->_basePath = dirname($class->getFileName());
        }
        return $this->_basePath;
    }

    public function setBasePath($path) {
        if (($this->_basePath = realpath($path)) === false || !is_dir($this->_basePath))
            throw new CException(Yii::t('yii', 'Base path "{path}" is not a valid directory.', array('{path}' => $path)));
    }

    public function getParams() {
        if ($this->_params !== null)
            return $this->_params;
        else {
            $this->_params = new CAttributeCollection;
            $this->_params->caseSensitive = true;
            return $this->_params;
        }
    }

    public function setParams($value) {
        $params = $this->getParams();
        foreach ($value as $k => $v)
            $params->add($k, $v);
    }

    public function getModulePath() {
        if ($this->_modulePath !== null)
            return $this->_modulePath;
        else
            return $this->_modulePath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'modules';
    }

    public function setModulePath($value) {
        if (($this->_modulePath = realpath($value)) === false || !is_dir($this->_modulePath))
            throw new CException(Yii::t('yii', 'The module path "{path}" is not a valid directory.', array('{path}' => $value)));
    }

    public function getParentModule() {
        return $this->_parentModule;
    }

    public function getModule($id) {
        if (isset($this->_modules[$id]) || array_key_exists($id, $this->_modules))
            return $this->_modules[$id];
        elseif (isset($this->_moduleConfig[$id])) {
            $config = $this->_moduleConfig[$id];
            if (!isset($config['enabled']) || $config['enabled']) {
                $class = $config['class'];
                unset($config['class'], $config['enabled']);
                if ($this === Base::a())
                    $module = Base::createComponent($class, $id, null, $config);
                else
                    $module = Base::createComponent($class, $this->getId() . '/' . $id, $this, $config);
                return $this->_modules[$id] = $module;
            }
        }
    }

    public function hasModule($id) {
        return isset($this->_moduleConfig[$id]) || isset($this->_modules[$id]);
    }

    public function getModules() {
        return $this->_moduleConfig;
    }

    public function setModules($modules) {
        foreach ($modules as $id => $module) {
            if (is_int($id)) {
                $id = $module;
                $module = array();
            }
            if (!isset($module['class'])) {
                $module['class'] = $id . '.' . ucfirst($id) . 'Module';
            }

            if (isset($this->_moduleConfig[$id]))
                $this->_moduleConfig[$id] = CMap::mergeArray($this->_moduleConfig[$id], $module);
            else
                $this->_moduleConfig[$id] = $module;
        }
    }

    public function hasComponent($name) {
        return isset($this->_components[$name]) || isset($this->_componentsConfig[$name]);
    }

    public function getComponent($id, $createIfNull = true) {
        if (isset($this->_components[$id]))
            return $this->_components[$id];
        elseif (isset($this->_componentConfig[$id]) && $createIfNull) {
            $config = $this->_componentConfig[$id];
            if (!isset($config['enabled']) || $config['enabled']) {
                unset($config['enabled']);
                $component = Base::createComponent($config);
                $component->init();
                return $this->_components[$id] = $component;
            }
        }
    }

    public function setComponent($id, $component, $merge = true) {
        if ($component === null) {
            unset($this->_components[$id]);
            return;
        } elseif ($component instanceof IApplicationComponent) {
            $this->_components[$id] = $component;

            if (!$component->getIsInitialized())
                $component->init();

            return;
        }
        elseif (isset($this->_components[$id])) {
            if (isset($component['class']) && get_class($this->_components[$id]) !== $component['class']) {
                unset($this->_components[$id]);
                $this->_componentConfig[$id] = $component; //we should ignore merge here
                return;
            }

            foreach ($component as $key => $value) {
                if ($key !== 'class')
                    $this->_components[$id]->$key = $value;
            }
        }
        elseif (isset($this->_componentConfig[$id]['class'], $component['class']) && $this->_componentConfig[$id]['class'] !== $component['class']) {
            $this->_componentConfig[$id] = $component; //we should ignore merge here
            return;
        }

        if (isset($this->_componentConfig[$id]) && $merge)
            $this->_componentConfig[$id] = CMap::mergeArray($this->_componentConfig[$id], $component);
        else
            $this->_componentConfig[$id] = $component;
    }

    public function getComponents($loadedOnly = true) {
        if ($loadedOnly)
            return $this->_components;
        else
            return array_merge($this->_componentConfig, $this->_components);
    }

    public function setComponents($components, $merge = true) {
        foreach ($components as $id => $component)
            $this->setComponent($id, $component, $merge);
    }

    protected function preloadComponents() {
        foreach ($this->preload as $id)
            $this->getComponent($id);
    }

    public function configure($config) {
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                $this->{"conf-" . $key} = $value;
            }
        }
    }

}
