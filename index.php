<?php

define('APP_INDEX', dirname(__FILE__));
define('APP_DIR', dirname(__FILE__) . '/app');

define('APP_DEBUG', true);

$sysDir = dirname(__FILE__) . '/sys';
require_once $sysDir . '/Base.class.php';

System\Base::makeApp()->run();