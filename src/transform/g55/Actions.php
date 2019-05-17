<?php
/********************************************************************************
    Router to the different actions of this data source.
    
    @license    GPL
    @history    2019-05-10 10:59:19+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\transform\g55;

use g5\Datasource;

class Actions implements Datasource{
    
    // ******************************************************
    /**
        @return A list of possible actions for this data source.
    **/
    public static function getActions(){
        return [
            'corrected2new',
        ];
    }
    
    // ******************************************************
    /**
        Routes an action to the appropriate code.
        @return report : string describing the result of execution.
    **/
    public static function action($action, $params=[]){
        switch($action){
        	case 'corrected2new' :
        	    return corrected2new::action();
            break;
        	default:
        	    throw new \Exception("Invalid action : $action");
            break;
        }
    }
    
}// end class
