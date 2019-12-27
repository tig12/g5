<?php
/********************************************************************************
    Utilities to route commands in the cura package.
    
    @license    GPL
    @history    2019-06-21 09:36:03+02:00, Thierry Graff : Creation from refactoring
********************************************************************************/
namespace g5\commands\g55;

use g5\patterns\Router;

class G55Router implements Router{
    
    // ******************************************************
    /** 
        Converts the datafile parameter in the user vocabulary to an array of datafiles known by this package.
        Useful for 'all' which means every G55 group.
        Does not perform check on $userParam.
        @param $userParam The data file as expressed by the user.
        @return Array containing datafiles.
    **/
    public static function computeDatafiles($userParam){
        switch($userParam){
        	case 'all' : 
        	    $tmp = G55::DATAFILES_POSSIBLES;
        	    array_shift($tmp); // remove 'all'
        	    return $tmp;
        	break;
            default:
                return [$userParam];
            break;
        }
    }
    
    
    // ******************************************************
    // Implementation of Router
    /**
        Returns an array containing the possible datafiles processed by the dataset.
        @return Array of strings
    **/
    public static function getDatafiles(): array{
        return G55::DATAFILES_POSSIBLES;
    }
    
    
    // ******************************************************
    // Implementation of Router
    /**
        @return A list of possible actions for a given datafile.
    **/
    public static function getCommands($datafile): array{
        // All datafiles share the same possible commands, that are located in 'all' sub-package 
        $tmp = glob(__DIR__ . DS . 'all' . DS . '*.php');
        $res = [];
        foreach($tmp as $file){
            $basename = basename($file, '.php');
            try{
                $class = new \ReflectionClass("g5\\commands\\g55\\all\\$basename");
                if($class->implementsInterface("g5\\patterns\\Command")){
                    $res[] = $basename;
                }
            }
            catch(\Exception $e){
                // silently ignore php files present in the directory, but containing errors
            }
        }
        return $res;
    }
    
    
}// end class
