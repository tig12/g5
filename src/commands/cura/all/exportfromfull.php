<?php
/********************************************************************************
    Transfers files of 7-full/ to 9-cura/
    
    EXPERIMENTAL - no correct integration yet
    
    @pre        7-full/ must be populated and ready to be transfered.
    
    @license    GPL
    @history    2019-07-05 13:48:39+02:00, Thierry Graff : creation
    @history    2019-12-28, Thierry Graff : port from using 5-tmp to 7-full
********************************************************************************/
namespace g5\commands\cura\all;

use g5\Config;
use g5\model\G5DB;
use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\model\Full;
use g5\model\Group;
use g5\model\Person;

class exportfromfull implements Command{
    
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
        $uid = Cura::UID_PREFIX_GROUP . G5DB::SEP . $datafile;
        $g = Group::new($uid);
        
        $outfile = Config::$data['dirs']['9-cura'] . DS . $datafile . '.csv';
        
        $csvFields = [
            'GID',
            'FNAME',
            'GNAME',
            'OCCU',
            'DATE',
            'DATE-UT',
            'PLACE',
            'C2',
            'C3',
            'CY',
            'LG',
            'LAT',
            'GEOID'
        ];
        
        $map = [
            'ids.cura' => 'GID',
            'fname' => 'FNAME',
            'gname' => 'GNAME',
            //'occus.0' => 'OCCU',
            'birth.date' => 'DATE',
            'birth.date-ut' => 'DATE-UT',
            'birth.place.name' => 'PLACE',
            'birth.place.c2' => 'C2',
            'birth.place.c3' => 'C3',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            'birth.place.geoid' => 'GEOID',
        ];
        
        $fmap = [
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        $g->exportCsv($outfile, $csvFields, $map, $fmap);
        
        return "Exported $uid to $outfile\n";
    }
    
}// end class    

