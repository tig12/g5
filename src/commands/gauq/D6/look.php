<?php
/********************************************************************************
    Prints records without given name.
    WARNING : this step is not part of the generation process.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-06-11 17:13:53+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\D6;

use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;
use g5\commands\gauq\LERRCP;

class look implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php gauq D6 build emptyGiven
    **/
    const POSSIBLE_PARAMS = [
        'emptyGiven',
    ];
    
    /** 
        Called by : php run-g5.php gauq D6 build <action>
        @param $params  array with 3 strings : 
                        - "D6" (useless here)
                        - "build" (useless here)
                        - action, which must be one of POSSIBLE_PARAMS.
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 3){
            return "INVALID PARAMETER : " . $params[3] . " - build doesn't need this parameter\n";
        }
        if(count($params) < 3){
            return "MISSING PARAMETER : - build needs a parameter to specify the action.\n can be :\n"
                 . implode(', ', self::POSSIBLE_PARAMS) . "\n";
        }
        
        $method = 'execute_' . $params[2];
        return self::$method();
    }

    // ******************************************************
    /**
        Lists the rows without given name.
        It has been used to build raw2tmp::$NAMES_CORRECTIONS
    **/
    public static function execute_emptyGiven(): string{
        $csvFile = LERRCP::tmpFilename('D6');
        if(!is_file($csvFile)){
            return "Missing file $csvFile\n"
                . "You must run first : php run-g5.php gauq D6 raw2tmp\n";
        }
        $res = '';
        $rows = csvAssociative::compute($csvFile);
        foreach($rows as $row){
            if($row['GNAME'] != ''){
                continue;
            }
            $res .= "        '{$row['NUM']}' => ['{$row['FNAME']}', ''],\n";
        }
        return $res;
    }
    
}// end class    

