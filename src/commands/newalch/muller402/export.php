<?php
/********************************************************************************
    Extract from data/1-raw/newalch/5muller_writers/5muller_writers.csv
    Builds a csv file of 436 writers from data/7-full/
    
    
    @license    GPL
    @history    2020-07-15 00:47:14+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\Config;
use g5\model\DB5;
use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\model\Full;
use g5\model\Group;
use g5\model\Person;

class export implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php newalch muller402 export
        @param $params empty array 
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 0){
            return "WRONG USAGE : useless parameter : {$params[0]}\n";
        }
        
        $report = '';
        
        $uid = Muller402::UID_GROUP_PREFIX;
        $g = Group::new($uid);
        
        $filename = '402-it-writers.csv';
        
        $outfile = Config::$data['dirs']['9-newalch'] . DS . $filename;
        
        $csvFields = [
            'FNAME',
            'GNAME',
            'OCCU',
            'DATE',
            'TZ',
            'PLACE',
            'C2',
            'CY',
            'LG',
            'LAT',
        ];
        
        $map = [
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.tz' => 'TZ',
            'birth.place.name' => 'PLACE',
            'birth.place.c2' => 'C2',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            //'birth.place.geoid' => 'GEOID',
        ];
        
        $fmap = [
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        $filters = [
            function($p){
                // keep only records with complete birth time (at least YYYY-MM-DD HH:MM)
                return strlen($p->data['birth']['date']) >= 16;
            },
        ];
        
        $g->exportCsv($outfile, $csvFields, $map, $fmap, $filters);
        
        return "Exported $uid to $outfile\n";
    }
    
}// end class    

