<?php
/******************************************************************************
    Like sleep() but parameter is a nb of seconds, and it prints a message.
    Adaptation of  https://stackoverflow.com/questions/12109042/php-get-file-listing-including-sub-directories
    
    Needs the definition of constant DS = DIRECTORY_SEPARATOR
    
    @license    GPL
    @history    2020-05-18 10:38:54+02:00, Thierry Graff : Creation
********************************************************************************/

namespace tiglib\filesystem;

class globRecursive{
    
    /** 
        Like glob() but also scans subdirectories
        Does not support flag GLOB_BRACE
        ex: globRecursive::execute(mydir . '*' . DS . '*.yml');
    **/
   public static function execute(string $pattern, int $flags = 0){
     $files = glob($pattern, $flags);
     foreach (glob(dirname($pattern).DS.'*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){
        $files = array_merge($files, self::execute($dir . DS . basename($pattern), $flags));
     }
     return $files;
   }    

}// end class
