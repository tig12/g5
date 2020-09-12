<?php
/********************************************************************************
    Generates data/output/history/1994-muller-medics/muller-1083-medics.csv
    
    @license    GPL
    @history    2020-09-12 17:27:59+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\newalch\muller1083;

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
    const OUTPUT_DIR = 'history' . DS . '1994-muller-medics';
    
    const OUTPUT_FILE = 'muller-1083-medics.csv';
    
    /** 
        Trick to access to $datafile within $fmap and $sort function
    **/
    private static $datafile;
    
    // *****************************************
    // Implementation of Command
    /** 
        @param $params Empty array
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 0){
            return "WRONG USAGE : useless parameter : {$params[0]}\n";
        }
        
        $report = '';
        
        $g = Group::getBySlug($datafile);
        $g-> computeMembers();

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE;
        
        $csvFields = [
            'MUID',
            'GQID',
            'FNAME',
            'GNAME',
            'OCCU',
            'DATE',
            'TZO',
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
            'ids-in-sources.' . $datafile => 'GQID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.tzo' => 'TZO',
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

