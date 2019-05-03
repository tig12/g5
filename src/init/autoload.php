<?php

/** 
    PSR-4 autoload for namespace gauquelin5
    
    @history    2017-04-27 09:46:32+02:00, Thierry Graff : Creation 
**/
spl_autoload_register(
    function ($full_classname){
        $namespace = 'gauquelin5';
        if(strpos($full_classname, $namespace) !== 0){
            return; // not managed by this autoload
        }
        $root_dir = dirname(__DIR__); // root dir for this namespace
        $classname = str_replace($namespace . '\\', '', $full_classname);
        $classname = str_replace('\\', DS, $classname);
        $filename = $root_dir . DS . $classname . '.php';
        require_once $filename;
    }
);

/** 
    Autoload for classes without namespace, located in lib/ and subdirectories
    
    @history    2017-05-04 10:04:59+02:00, Thierry Graff : Creation 
**/
spl_autoload_register(
    function ($classname){
        $root_dir = dirname(__DIR__);
        $filename = $root_dir . DS . 'lib' . DS . $classname . '.php';
        if(is_file($filename)){
            require_once $filename;
            return;
        }
        $filename = $root_dir . DS . 'lib' . DS . 'timezone' . DS . $classname . '.php';
        if(is_file($filename)){
            require_once $filename;
            return;
        }
    }
);

