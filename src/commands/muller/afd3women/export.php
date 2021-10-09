<?php
/********************************************************************************
    Generates data/output/history/1993-muller-women/muller-234-women.csv
    
    @license    GPL
    @history    2021-08-14 20:01:49+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd3women;

use g5\app\Config;
use g5\model\Group;
use tiglib\patterns\Command;
use g5\commands\db\export\Export as ExportUtils;
use g5\commands\muller\AFD;
use g5\commands\gauq\LERRCP;

class export implements Command {
    
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1993-muller-women';
    
    const OUTPUT_FILE = 'muller-234-women.csv';
    
    /**  Trick to access to $sourceSlug within $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php muller afd3women export
        @param $params empty array 
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
        
        $g = Group::getBySlug(AFD3::GROUP_SLUG); // DB
        
        self::$sourceSlug = AFD3::LIST_SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE;
        
        $csvFields = [
            'MUID',
            'GQID',
            'FNAME',
            'GNAME',
            'DATE',
            'TZO',
            'TIMOD',
            'PLACE',
            'C1',
            'C2',
            'CY',
            'LG',
            'LAT',
            'GEOID',
            'OCCU',
        ];
        
        $map = [
            'ids-in-sources.' . AFD::SOURCE_SLUG => 'MUID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
//            'birth.date' => 'DATE',
            'birth.tzo' => 'TZO',
            'birth.note' => 'TIMOD',
            'birth.place.name' => 'PLACE',
            'birth.place.c1' => 'C1',
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
            'DATE' => function($p){
                return ExportUtils::exportDate(
                    date: $p->data['birth']['date'],
                    dateUT: $p->data['birth']['date-ut'],
                    tzo: $p->data['birth']['tzo'],
                );
            }
        ];
        
        // sort by Müller id
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
