<?php
/********************************************************************************
    Generates data/output/history/1991-muller-writers/muller-100-writers.csv
    By default, the generated file is compressed (using zip).
    
    @license    GPL
    @history    2021-08-06 07:39:07+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd1writers;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Group;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\AFD;

class export100 implements Command {
    
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1991-muller-writers';
    
    const OUTPUT_FILE = 'muller-100-writers.csv';
    
    /**  Trick to access to $sourceSlug within $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php newalch muller402 export100 [nozip]
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
        
        $g = Group::getBySlug(Muller100::GROUP_SLUG); // DB
        
        self::$sourceSlug = Muller100::LIST_SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE;
        
        $csvFields = [
            'MUID',
            'GQID',
            'FNAME',
            'GNAME',
            'DATE',
            'TZO',
            'DATE-UT',
            'LMT',
            'PLACE',
            'C2',
            'CY',
            'LG',
            'LAT',
            'GEOID',
            'OCCU',
            'OPUS',
            'LEN',
        ];
        
        $map = [
            'ids-in-sources.' . AFD::SOURCE_SLUG => 'MUID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.tzo' => 'TZO',
            'birth.date-ut' => 'DATE-UT',
            'birth.note' => 'LMT',
            'birth.place.name' => 'PLACE',
            'birth.place.c2' => 'C2',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            'birth.place.geoid' => 'GEOID',
// BUG HERE - 
// some records: raw.history.0
// some records: raw.history.1
/* 
            'raw.' . self::$sourceSlug . '.OPUS' => 'OPUS',
            'raw.' . self::$sourceSlug . '.LEN' => 'LEN',
*/
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
