<?php

define('APP_INDEX', dirname(__FILE__));

$sysDir = dirname(__FILE__) . '/sys';
require_once $sysDir . '/BaseClass.php';

System\BaseClass::makeApp()->run();