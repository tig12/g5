<?php
/********************************************************************************
    Generates csv files in data/output/1970-1984-gauquelin
    By default, the generated files are compressed (using zip).
    
    @license    GPL
    @history    2019-07-05 13:48:39+02:00, Thierry Graff : creation
    @history    2019-12-28,                Thierry Graff : export using 7-full instead of 5-tmp
    @history    2020-08-12 08:58:19+02:00, Thierry Graff : export using g5 db instead of 7-full
********************************************************************************/
namespace g5\commands\cura\all;

use g5\app\Config;
use g5\model\DB5;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
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
        Called by : php run-g5.php cura <datafile> export [nozip]
        If called without third parameter, the output is compressed (using zip)
        @param $params array containing 2 or 3 strings :
                       - the datafile to process (like "A1").
                       - The name of this command (useless here).
                       - An optional string "nozip"
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 3){
            return "WRONG USAGE : useless parameter : '{$params[3]}'\n";
        }
        if(count($params) == 3 && $params[2] != 'nozip'){
            return "WRONG USAGE : invalid parameter : '{$params[2]}' - possible value : 'nozip'\n";
        }
        $dozip = true;
        if(count($params) == 3){
            $dozip = false;
        }
        
        $report = '';
        
        $datafile = $params[0];
        
        $groupSlug = LERRCP::datafile2groupSlug($datafile);
        $g = Group::getBySlug($groupSlug); // DB

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
        
        $filters = [];
        
        [$exportReport, $exportFile, $N] = 
        $g->exportCsv(
            csvFile:    $outfile,
            csvFields:  $csvFields,
            map:        $map,
            fmap:       $fmap,
            sort:       $sort,
            filters:    $filters,
            dozip:      $dozip,
        );
        $g->data['download'] = str_replace(Config::$data['dirs']['output'] . DS, '', $exportFile);
        $g->update(updateMembers:false);
        $report .= $exportReport;
        return $report;
    }
    
} // end class    
