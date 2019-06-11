<?php
/********************************************************************************
    Prints records without given name.
    WARNING : this step is not part of the built process.
    It has been used to build raw2csv::$NAMES_CORRECTIONS
    
    @license    GPL
    @history    2019-06-11 17:13:53+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\D6;

use g5\Config;
use g5\patterns\Command;
use tiglib\arrays\csvAssociative;

class checkNames implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura D6 checkNames
        @param $params  array with 2 elements : datafile and command
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{

        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - checkNames doesn't need this parameter\n";
        }
        $csvFile = Config::$data['dirs']['5-cura-csv'] . DS . 'D6.csv';
        if(!is_file($csvFile)){
            return "Missing file $csvFile\n"
                . "You must run first : php run-g5.php cura D6 raw2csv\n";
        }
        $rows = csvAssociative::compute($csvFile);
        foreach($rows as $row){
            if($row['GNAME'] != ''){
                continue;
            }
            echo "        '{$row['NUM']}' => ['{$row['FNAME']}', ''],\n";
        }
        return '';
    }
    
}// end class    

