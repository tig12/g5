<?php
/******************************************************************************
    Autoload function for namespace \soniakeys

    @license    GPL
    @history    2019-07-31 02:10:43+02:00, Thierry Graff : Creation
********************************************************************************/


spl_autoload_register(
    function ($full_classname){
        $namespace = 'soniakeys';
        if(strpos($full_classname, $namespace) !== 0){
            return; // not managed by this autoload
        }
        $root_dir = __DIR__; // root dir for this namespace
        $classname = str_replace($namespace . '\\', '', $full_classname);
        $classname = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
        $filename = $root_dir . DIRECTORY_SEPARATOR . $classname . '.php';
        $ok = include_once($filename);
        if(!$ok){
            throw new \Exception("AUTOLOAD FAILS for class $full_classname");
        }
    }
);
