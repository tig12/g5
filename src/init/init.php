<?php
/** 
    @history    2017-04-27 09:36:43+02:00, Thierry Graff : Creation
**/

require_once __DIR__ . DS . 'autoload.php';

// require code out of gauquelin5 namespace
$ROOT_DIR = dirname(dirname(__DIR__)); // directory containing config.yml
require_once $ROOT_DIR . '/src/lib/yaml/YAML.php';
require_once $ROOT_DIR . '/src/lib/lib.php';

// conf
use gauquelin5\init\Config;

Config::init();

