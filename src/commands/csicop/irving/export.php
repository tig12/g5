<?php
/********************************************************************************
    Generates data/output/history/1979-csicop/csicop-irving-408.csv
    By default, the generated file is compressed (using zip).
    
    @license    GPL
    @history    2021-08-06 08:52:37+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Group;
use tiglib\patterns\Command;
use g5\commands\csicop\CSICOP;
use g5\commands\gauq\LERRCP;

class export implements Command {
    
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1979-csicop';
    
    const OUTPUT_FILE = 'csicop-irving-408.csv';
    
    /**  Trick to access to $sourceSlug within $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php csicop irving [nozip]
        If called without parameter, the output is compressed (using zip)
        @param $params array containing 0 or 1 element :
                       - An optional string "nozip"
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 1){
            return "WRONG USAGE : useless parameter : '{$params[1]}'\n";
        }
        if(count($params) == 1 && $params[0] != 'nozip'){
            return "WRONG USAGE : invalid parameter : '{$params[0]}' - possible value : 'nozip'\n";
        }
        $dozip = true;
        if(count($params) == 1){
            $dozip = false;
        }
        
        $report = '';
        
        $g = Group::getBySlug(CSICOP::GROUP_SLUG); // DB
        
        self::$sourceSlug = Irving::LIST_SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE;
        
        $csvFields = [
            'CSID',
            'GQID',
            'BATCH',
            'FNAME',
            'GNAME',
            'DATE',
            'TZO',
            'PLACE',
            'C2',
            'CY',
            'LG',
            'LAT',
            'GEOID',
            'OCCU',
        ];
        
        $map = [
            'ids-in-sources.' . Irving::LIST_SOURCE_SLUG => 'CSID',
// BUG HERE - 
// some records: raw.history.0
// some records: raw.history.1
//            'raw.' . self::$sourceSlug . '.BATCH' => 'BATCH',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.tzo' => 'TZO',
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
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        // sorts by Müller id
        $sort = function($a, $b){
             return $a->data['ids-in-sources'][self::$sourceSlug] <=> $b->data['ids-in-sources'][self::$sourceSlug];
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
