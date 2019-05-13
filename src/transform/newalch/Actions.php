<?php
/********************************************************************************
    Façade for this transformation.
    
    @license    GPL
    @history    2019-05-10 12:09:27+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch;

use g5\Datasource;

class Actions implements Datasource{
    
    /** 
        Possible values of parameter indicating the subject to process.
    **/
    const SUBJECTS_POSSIBLES = [
        '4391SPO',
    ];
    
    // ******************************************************
    /**
        @return A list of possible actions for this data source.
    **/
    public static function getActions(){
        return [
            'raw2csv',
            'extract',
        ];
    }
    
    // ******************************************************
    /**
        Routes an action to the appropriate code.
        @return report : string describing the result of execution.
    **/
    public static function action($action, $params=[]){
        switch($action){
        	case 'raw2csv' :
        	    return self::raw2csv($params);
            break;
        	case 'extract' :
        	    return ertel4391\extract::action($params);
            break;
        	default:
        	    throw new Exception("Invalid action : $action");
            break;
        }
    }
    
    // ******************************************************
    /**
        Conversion from files of 1-raw/newalchemypress.com to 5-tmp/newalch-csv
        Checks parameters and delegates to the correct class.
        @return report : string describing the result of execution.
    **/
    private static function raw2csv($params){
        $possibles = implode(', ', self::SUBJECTS_POSSIBLES);
        if(count($params) == 0){
            return "raw2csv requires a parameter indicating what you want to process.\n"
                . "Possible values : $possibles\n";
        }
        if(count($params) > 1){
            return "raw2csv requires a unique parameter indicating what you want to process.\n"
                . "Invalid parameters : " . implode(', ', array_slice($params, 1)) . "\n";
        }
        $subject = $params[0];
        if(!in_array($subject, self::SUBJECTS_POSSIBLES)){
            return "Invalid parameter '$subject'\nPossible values : $possibles\n";
        }
        // OK, the subject is valid.
        // Delegate to the class that will do the job
        switch($subject){
            case '4391SPO' : 
                $class = 'g5\transform\newalch\ertel4391\raw2csv';
                return $class::action();
            break;
        }
    }
    
}// end class
