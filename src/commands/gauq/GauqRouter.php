<?php
/********************************************************************************
    Utilities to route commands in the cura package.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-05-09 01:34:14+02:00, Thierry Graff : Creation from refactoring
********************************************************************************/
namespace g5\commands\gauq;

use g5\app\Router;

class GauqRouter implements Router {
    
    /** 
        Associations between datafile in the user's vocabulary and the sub-namespace that handles it.
        (sub-namespace of g5\commands\gauq).
    **/
    const DATAFILES_SUBNAMESPACE = [
        'all' => 'all',
        'look' => 'look',
        'A' => 'A',
        'A1' => 'A',
        'A2' => 'A',
        'A3' => 'A',
        'A4' => 'A',                                                                             
        'A5' => 'A',
        'A6' => 'A',
        'D6' => 'D6',
        'D10' => 'D10',
        'E1' => 'E1_E3',
        'E3' => 'E1_E3',
    ];
    
    // ******************************************************
    /** 
        Converts the datafile parameter in the user vocabulary to an array of datafiles known by this package.
        Useful for parameters like 'A' which means everything from A1 to A6.
        Does not perform check on $userParam.
        @param $userParam The data file as expressed by the user.
        @return Array containing datafiles.
    **/
    public static function computeDatafiles($userParam){
        switch($userParam){
        	case 'all' : 
        	    return ['A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'D6', 'D10', 'E1', 'E3'];
        	break;
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
        (= possible values of parameter indicating the subject to process)
        @return Array of strings
    **/
    public static function getArgs2(): array{
        return [
            'all',
            'look',
            'A', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6',
            // 'B', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6',
            'D6', 'D10',
            'E1', 'E3',
        ];
    }
    
    
    // ******************************************************
    // Implementation of Router
    /**
        @return A list of possible actions for a given datafile.
    **/
    public static function getArgs3($datafile): array{
        $subnamespace = self::DATAFILES_SUBNAMESPACE[$datafile];
        $tmp = glob(__DIR__ . DS . $subnamespace . DS . '*.php');
        $res = [];
        foreach($tmp as $file){
            $basename = basename($file, '.php');
            try{
                $class = new \ReflectionClass("g5\\commands\\gauq\\$subnamespace\\$basename");
                if($class->implementsInterface("tiglib\\patterns\\Command")){
                    $res[] = $basename;
                }
            }
            catch(\Exception $e){
                // silently ignore php files present in the directory,
                // not implementing Command, or containing errors.
            }
        }
        
        // commands available for all datafiles, and implemented in subpackage all.
        if($datafile != 'all' && $datafile != 'look'){
            $res[] = 'export';
            $res[] = 'tweak2tmp';
            sort($res);
        }
        return $res;
    }
    
    
}// end class
