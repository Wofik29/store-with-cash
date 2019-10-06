<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Entity.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Store.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Client.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Cash.php';


//var_dump( env('MINUTES_IN_HOUR'));

$store = new \Store\Store(5);
$store->run();