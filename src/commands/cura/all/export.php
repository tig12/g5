<?php
/********************************************************************************
    Generates csv files in data/output
    
    @license    GPL
    @history    2019-07-05 13:48:39+02:00, Thierry Graff : creation
    @history    2019-12-28,                Thierry Graff : export using 7-full instead of 5-tmp
    @history    2020-08-12 08:58:19+02:00, Thierry Graff : export using g5 db instead of 7-full
********************************************************************************/
namespace g5\commands\cura\all;

use g5\Config;
use g5\model\DB5;
use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\model\Full;
use g5\model\Group;
use g5\model\Person;

class export implements Command {
    
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'datasets' . DS . 'cura';
    
    /** 
        Trick to access to $datafile within $fmap and $sort function
    **/
    private static $datafile;
    
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
        
        $g = Group::getBySlug($datafile);
        $g-> computeMembers();

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . $datafile . '.csv';
        
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
            'ids-in-sources.' . $datafile => 'GID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
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
        
        self::$datafile = $datafile; // trick for $fmap and $sort
        
        $fmap = [
            'GID' => function($p){
                return Cura::gqid(self::$datafile, $p->data['ids-in-sources'][self::$datafile]);
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        // sorts by id within the $datafile
        $sort = function($a, $b){
             return $a->data['ids-in-sources'][self::$datafile] <=> $b->data['ids-in-sources'][self::$datafile];
        };
        
        return $g->exportCsv($outfile, $csvFields, $map, $fmap, $sort);
    }
    
}// end class    

