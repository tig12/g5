<?php
/********************************************************************************
    Utilities to route commands in the cura package.
    
    @license    GPL
    @history    2019-05-09 01:34:14+02:00, Thierry Graff : Creation from refactoring
********************************************************************************/
namespace g5\transform\cura;

use g5\patterns\Router;

class CuraRouter implements Router{
    
    // ******************************************************
    /** 
        Converts the datafile parameter in the user vocabulary to an array of datafiles known by this package.
        Useful for parameters like 'A' which means everything from A1 to A6.
        Does not perform check on $userParam.
        @param $userParam The data file as expressed by the user.
        @return Array containing subjects.
    **/
    public static function computeDatafiles($userParam){
        switch($userParam){
        	case 'A' : 
        	    return ['A1', 'A2', 'A3', 'A4', 'A5', 'A6'];
        	break;
        	/* 
        	case 'B' : 
        	    return ['B1', 'B2', 'B3', 'B4', 'B5', 'B6'];
        	break;
        	case 'E2' : 
        	    return ['E2a', 'E2b', 'E2c', 'E2d', 'E2e', 'E2f', 'E2g'];
        	break;
        	*/
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
        return Cura::DATAFILES_POSSIBLES;
    }
    
    
    // ******************************************************
    // Implementation of Router
    /**
        @return A list of possible actions for this data source.
    **/
    public static function getCommands($datafile): array{
        $subnamespace = Cura::DATAFILES_SUBNAMESPACE[$datafile];
        $tmp = glob(__DIR__ . DS . $subnamespace . DS . '*.php');
        $res = [];
        foreach($tmp as $file){
            $basename = basename($file, '.php');
            $class = new \ReflectionClass("g5\\transform\\cura\\$subnamespace\\$basename");
            if($class->implementsInterface("g5\\patterns\\Command")){
                $res[] = $basename;
            }
        }
        return $res;
    }
    
    
}// end class
