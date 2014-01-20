<?php

define('FTK_ROOT', dirname(__FILE__));

$ftkDir = FTK_ROOT . '/framework';

require_once $ftkDir . '/Bootstrap.class.php';

\base\Bootstrap::prepare()->run();