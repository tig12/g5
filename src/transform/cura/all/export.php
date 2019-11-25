<?php
/********************************************************************************
    Transfers files of 5-cura-csv/ to 9-cura/
    
    TEMPORARY CODE - files of 5-cura-csv/ will first be stored in 7-full/
    
    @pre        5-cura-csv/ must be populated and ready to be transfered.
    
    @license    GPL
    @history    2019-07-05 13:48:39+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\all;

use g5\Config;
use g5\patterns\Command;

class export implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura <datafile> export
        @param $params array containing two strings :
                       - the datafile to process (like "A1").
                       - The name of this command (useless here)
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 2){
            return "WRONG USAGE : useless parameter : {$params[2]}\n";
        }
        $datafile = $params[0];
        $infile = Config::$data['dirs']['5-cura-csv'] . DS . $datafile . '.csv';
        $outfile = Config::$data['dirs']['9-cura'] . DS . $datafile . '.csv';
        copy($infile, $outfile);
        return "Copied $infile to $outfile\n";
    }
    
}// end class    

