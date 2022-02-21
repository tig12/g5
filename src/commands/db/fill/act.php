<?php
/******************************************************************************
    
    Connects a person to an act
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-02-20 23:52:25+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use tiglib\patterns\Command;
use g5\app\Config;
//use tiglib\filesystem\globRecursive;
use g5\model\Acts;

class act implements Command {
    
    const POSSIBLE_ACTS = ['birth', 'death', 'mariage'];
    const POSSIBLE_ACTIONS = ['insert', 'update'];
    
    /** 
        Inserts or updates an act in database
        @param  $params[0]  Slug of the concerned person - required.
                $params[1]  Type of act ; can be 'birth', 'death' or 'mariage' - optional.
                            Default: 'birth'
                $params[2]  Action ; can be 'insert' or 'update' - optional.
                            Default: 'update'
        @return Empty string, echoes the report on execution for each source processed.
        @throws Exception if unable to insert or update.
    **/
    public static function execute($params=[]): string {
        if(count($params) < 1){
            return "INVALID USAGE - This command needs one parameter :\n"
                . "The slug of the person concerned by the birth certificate\n";
        }
        $slug = $params[0];
        
        $typact = 'birth';
        if(count($params) >= 2){
            $typact = $params[1];
            if(!in_array($typact, self::POSSIBLE_ACTS)){
                return "INVALID PARAMETER '$typact'\n"
                    . "Possible act types: " . implode(', ', self::POSSIBLE_ACTS) . "\n";
            }
        }
        ////////// tmp code //////////
        if($typact != 'birth'){ return "NOT IMPLEMENTED: $typact\n"; }
        ////////// end tmp code //////////
        
        $action = 'update';
        if(count($params) == 3){
            $action = $params[2];
            if(!in_array($action, self::POSSIBLE_ACTIONS)){
                return "INVALID PARAMETER '$action'\n"
                    . "Possible actions: " . implode(', ', self::POSSIBLE_ACTIONS) . "\n";
            }
        }
        
        if(count($params) > 3){
            return "INVALID USAGE - useless parameter '{$params[3]}'\n";
        }
        
        $report = "--- db fill act $typact $action $slug ---\n";
        
        $p = Acts::personAct($slug, $typact);
        // TODO Add entry in person history
        
        switch($action){
        	case 'insert': 
                $p->insert(); // can throw an exception
                $report .= "Inserted $slug\n";
            break;
            case 'update':
                $p->update(); // can throw an exception
                $report .= "Updated $slug\n";
        	break;
        }
        
        return $report;
    }
    
} // end class
