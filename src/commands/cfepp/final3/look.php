<?php
/********************************************************************************
    Importation of data/raw/cfepp/final3
    to  data/tmp/cfepp/cfepp-1120-nienhuys.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-04-18 11:29:15+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;

class look implements Command {
    
    // *****************************************
    /** 
        @param  $params Empty array
        @return String report                                                                 
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
/* 
        foreach($final3 as $CFID => $line){
echo "\n<pre>"; print_r($line); echo "</pre>\n"; exit;            
        }
        
        $dbpersons = Person::partialId2persons(Ertel::SOURCE_SLUG);
        foreach($dbpersons as $dbperson){
//echo "\n<pre>"; print_r($dbperson); echo "</pre>\n"; exit;
            $p = new Person();
            $p->data = $dbperson;
// echo "\n"; print_r($p->data['ids-in-sources']); echo "\n";
// echo "\n"; print_r($p->data['ids-partial']); echo "\n";
            $hist = $p->historyFromSource(ErtelSport::SOURCE_SLUG);
            $final3[]['ERID'] = $hist['raw']['NR'];
            $final3['GQID'] = ErtelSport::compute_GQID($hist);
            $final3['CFID'] = $hist['raw']['CFEPNR'];
            $final3['CPID'] = $hist['raw']['PARA_NR'];
            
echo "\n<pre>"; print_r($hist); echo "</pre>\n";
exit;
        }
*/        
        return $report;
    }
    
} // end class