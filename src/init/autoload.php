<?php
/** 
    Unique autoload code to include
    Contains PSR-4 autoload for namespace "g5" and autoload for vendor code.
    
    @history    2017-04-27 09:46:32+02:00, Thierry Graff : Creation 
**/

require_once dirname(dirname(__DIR__)) . DS . 'vendor' . DS . 'tiglib' . DS . '1.0' . DS . 'autoload.php';

/** 
    Autoload for g5 namespace
**/
spl_autoload_register(
    function ($full_classname){
        $namespace = 'g5';
        if(strpos($full_classname, $namespace) !== 0){
            return; // not managed by this autoload
        }
        $root_dir = dirname(__DIR__); // root dir for this namespace
        $classname = str_replace($namespace . '\\', '', $full_classname);
        $classname = str_replace('\\', DS, $classname);
        $filename = $root_dir . DS . $classname . '.php';
        $ok = include_once($filename);
        if(!$ok){
            throw new \Exception("AUTOLOAD FAILS for class $full_classname");
        }
    }
);

//////////////////// @todo remove ///////////////////////////
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

