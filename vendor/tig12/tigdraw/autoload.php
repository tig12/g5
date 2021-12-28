<?php
/** 
    Unique autoload code to include.
    Contains PSR-4 autoload for namespace "tigdraw"
    
    @history    2021-05-02 19:39:21+01:00, Thierry Graff : Creation 
**/

/** 
    Autoload for tigdraw namespace
**/
spl_autoload_register(
    function ($full_classname){
        $namespace = 'tigdraw';
        if(strpos($full_classname, $namespace) !== 0){
            return; // not managed by this autoload
        }
        $root_dir = __DIR__ . DIRECTORY_SEPARATOR; // root dir for this namespace
        $classname = str_replace($namespace . '\\', '', $full_classname);
        $classname = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
        $filename = $root_dir . DIRECTORY_SEPARATOR . $classname . '.php';
        $ok = include_once($filename);
        if(!$ok){
            throw new \Exception("AUTOLOAD FAILS for class $full_classname");
        }
    }
);
