<?php
/********************************************************************************
    Transfers files in 5-tmp/cura-csv/ to 5-tmp/full
    
    WARNING - code not yet integrated to restoration process.
    
    @pre        5-tmp/cura-csv/ must be populated and ready to be transfered.
    
    @license    GPL
    @history    2019-05-19 18:53:29+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\init\Config;
use g5\patterns\Command;
use g5\model\Full;
//use g5\transform\cura\Cura;

class csv2full implements Command{
    
    const POSSIBLE_PARAM = ['update', 'echo'];
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run.php cura A1 csv2full
        @param $params array
    **/
    public static function execute($params=[]): string{
        
        $report = '';
        
        $curaRows = \lib::csvAssociative(Config::$data['dirs']['5-cura-csv'] . DS . 'A2.csv');
        
        $nTotal = count($curaRows);
        $nMatch = 0;
        foreach($curaRows as $row){
            $full = Full::matchArray($row);
            if(!$full){
//echo $row['NUM'] . ' ' . $row['GNAME'] . ' ' . $row['FNAME'] . ' ' . $row['OCCU'] . "\n";
                // @todo self::handleNotMatched();
                continue;
            }
            $nMatch++;
//if($nMatch == 20) break;
//echo "\n<pre>"; print_r($full); echo "</pre>\n"; exit;
        }

        $report .= "n total = $nTotal\n";
        $report .= "n match = $nMatch\n";
        return $report;
    }
    
}// end class    

