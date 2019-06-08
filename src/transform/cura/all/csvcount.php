<?php
/********************************************************************************
    Look at presence of different fields in csv files of data/5-tmp.
    
    @license    GPL
    @history    2019-06-07 22:29:12+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\all;

use g5\Config;
use g5\patterns\Command;
use \lib;
use g5\transform\cura\CuraRouter;

class csvcount implements Command{
    
    
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura D6 all
        @param $params  array with 2 elements : datafile and command
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - csvcount doesn't need this parameter\n";
        }
        
        $datafiles = array_merge(
            CuraRouter::computeDatafiles('A'),
            CuraRouter::computeDatafiles('D6'),
            CuraRouter::computeDatafiles('D10'),
            CuraRouter::computeDatafiles('E1'),
            CuraRouter::computeDatafiles('E3')
        );
        
        $dir = Config::$data['dirs']['5-cura-csv'];
        
        // associative arrays holding the counts
        // keys = datafiles ; values = counts
        $N = $nGNAME
           = $nGEOID
           = $nPLACE
           = $nDTU
           = $nC2
           = array_fill_keys($datafiles, 0);
           
        foreach($datafiles as $datafile){
            $file = $dir . DS . $datafile . '.csv';
            $rows = \lib::csvAssociative($file);
            foreach($rows as $row){
echo "\n<pre>"; print_r($row); echo "</pre>\n"; exit;
                $N[$datafile]++;
                if($row['GNAME']) $nGNAME[$datafile]++;
                if($row['GEOID']) $nGEOID[$datafile]++;
                if($row['PLACE']) $nPLACE[$datafile]++;
                if($row['DTU']) $nDTU[$datafile]++;
                if($row['C2']) $nC2[$datafile]++;
            }
            
echo "\n<pre>"; print_r($N); echo "</pre>\n"; exit;
        }
        
        
        return '';
    }
    
}// end class    

