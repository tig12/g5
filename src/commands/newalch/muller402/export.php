<?php
/********************************************************************************
    Generates data/output/history/1991-muller-writers/muller-402-writers.csv
    
    @license    GPL
    @history    2020-09-18 16:41:27+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\Config;
use g5\model\DB5;
use g5\model\Group;
use g5\patterns\Command;
use g5\commands\muller\AFD;
use g5\commands\cura\Cura;

class export implements Command {
    
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1991-muller-writers';
    
    const OUTPUT_FILE = 'muller-402-writers.csv';
    
    /**  Trick to access to $sourceSlug within $sort function **/
    private static $sourceSlug;
    
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
        
        $g = Group::getBySlug(Muller402::GROUP_SLUG);
        $g-> computeMembers();
        
        $source = Muller402::getSource();
        self::$sourceSlug = $source->data['slug'];

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
            'CY',
            'LG',
            'LAT',
            'GEOID',
        ];
        
        $map = [
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.tzo' => 'TZO',
            'birth.date-ut' => 'DATE-UT',
            'birth.place.name' => 'PLACE',
            'birth.place.c2' => 'C2',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            'birth.place.geoid' => 'GEOID',
        ];
        
        $fmap = [
            'GQID' => function($p){
                return $p->data['ids-in-sources'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'MUID' => function($p){
                return AFD::ids_in_sources2mullerId($p->data['ids-in-sources']);
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        // sorts by Müller id
        $sort = function($a, $b){
             return $a->data['ids-in-sources'][self::$sourceSlug] <=> $b->data['ids-in-sources'][self::$sourceSlug];
        };
        
        $filters = [];
        
        return $g->exportCsv($outfile, $csvFields, $map, $fmap, $sort, $filters);
    }
    
}// end class    

