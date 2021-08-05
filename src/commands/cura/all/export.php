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
use g5\commands\gauquelin\LERRCP;
use g5\commands\muller\AFD;
use g5\model\Full;
use g5\model\Group;
use g5\model\Person;

class export implements Command {
    
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1970-1984-gauquelin';
    
    /**  Trick to access to $sourceSlug from $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php cura <datafile> export               
        @param $params array containing two strings :
                       - the datafile to process (like "A1").
                       - The name of this command (useless here).
        @return Report
    **/
    public static function execute($params=[]): string{
echo "\n<pre>"; print_r($params); echo "</pre>\n"; exit;
        if(count($params) > 2){
            return "WRONG USAGE : useless parameter : {$params[2]}\n";
        }
        
        $report = '';
        
        $datafile = $params[0];
        
        $groupSlug = LERRCP::datafile2groupSlug($datafile);
        $g = Group::getBySlug($groupSlug);

        self::$sourceSlug = LERRCP::datafile2sourceSlug($datafile); // Trick to access to $sourceSlug inside $sort function
        
        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . $datafile . '.csv';
        
        $csvFields = [
            'GQID',
            'MUID',
            'NUM',
            'FNAME',
            'GNAME',
            'DATE',
            'TZO',
            'DATE-UT',
            'PLACE',
            'C2',
            'C3',
            'CY',
            'LG',
            'LAT',
            'GEOID',
            'OCCU',
        ];
        
        $map = [
            'ids-in-sources.' . self::$sourceSlug => 'NUM',
            'ids-in-sources.' . LERRCP::SOURCE_SLUG => 'GQID',
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
                
        $fmap = [
            'MUID' => function($p){
                return AFD::ids_in_sources2mullerId($p->data['ids-in-sources']);
            },          
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        $sort = function($a, $b){
            return (int)$a->data['ids-in-sources'][self::$sourceSlug] <=> (int)$b->data['ids-in-sources'][self::$sourceSlug];
        };
        
        return $g->exportCsv($outfile, $csvFields, $map, $fmap, $sort);
    }
    
}// end class    

