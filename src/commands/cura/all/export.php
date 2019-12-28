<?php
/********************************************************************************
    Transfers files of 5-cura-csv/ to 9-cura/
    
    TEMPORARY CODE - files of 5-cura-csv/ will first be stored in 7-full/
    
    @pre        5-cura-csv/ must be populated and ready to be transfered.
    
    @license    GPL
    @history    2019-07-05 13:48:39+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\all;

use g5\Config;
use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\model\Full;
use g5\model\Group;
use g5\model\Person;

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
        
        $report = '';
        
        $datafile = $params[0];
        $uid = Cura::UID . Full::SEP . $datafile;
        $g = Group::new($uid, true);
        foreach($g->data as $puid){
echo "in export : $puid\n";
            $p = Person::new($puid, true);
echo "\n"; print_r($p); echo "\n";
exit;
        }
        
        
        $outfile = Config::$data['dirs']['9-cura'] . DS . $datafile . '.csv';
        
        $g->export();
        return "Exported $uid to $outfile\n";
    }
    
}// end class    

