<?php
/********************************************************************************
    Transfers files of 5-newalch-csv/ to 9-newalch/
    
    TEMPORARY CODE - files of 5-newalch-csv/ will first be stored in 7-full/
    
    @pre        5-newalch-csv must be populated and ready to be transfered.
    
    @license    GPL
    @history    2019-10-06 19:02:13+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\newalch\all;

use g5\Config;
use g5\patterns\Command;

class csv2dl implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php newalch all csv2dl
        Copies all files of 5-newalch-csv/ to 9-newalch/
        WARNING : the mechanism differs from cura (cura is more elaborate with CuraCommand and router).
        @param $params array containing two strings :
                       - the datafile to process (like "A1").
                       - The name of this command (useless here)
        @return Report
        @todo Implement a mechanism like cura if needed
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 0){
            return "WRONG USAGE : useless parameter : {$params[0]}\n";
        }
        $report = '';
        foreach(glob(Config::$data['dirs']['5-newalch-csv'] . DS . '*') as $file){
            $infile = Config::$data['dirs']['5-newalch-csv'] . DS . basename($file);
            $outfile = Config::$data['dirs']['9-newalch'] . DS . basename($file);
            copy($infile, $outfile);
            $report .= "Copied $infile to $outfile\n";
        }
        return $report;
    }
    
}// end class    

