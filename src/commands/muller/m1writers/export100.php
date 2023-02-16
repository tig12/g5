<?php
/********************************************************************************
    Generates data/output/history/1991-muller1-writers/muller-100-writers.csv
    By default, the generated file is compressed (using zip).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-08-06 07:39:07+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m1writers;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Group;
use g5\commands\db\export\Export as ExportService;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;
use tiglib\patterns\Command;

class export100 implements Command {
    
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1991-muller1-writers';
    
    const OUTPUT_FILE = 'muller-100-writers.csv';
    
    /**  Trick to access to $sourceSlug within $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php muller m1writers export100 [optional parameters]
        If called without parameter, the output is compressed (using zip)
        For optional parameters, see comment of class commands/db/export/Export
        @param $params array containing 0 or 1 element :
                       - Optional export parameters "zip" or "sep"
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 1){
            return "WRONG USAGE : useless parameter : '{$params[1]}'\n";
        }
        $dozip = true;
        $generateSep = false;
        if(count($params) == 1){
            [$dozip, $generateSep] = ExportService::computeOptionalParameters($params[0]);
        }
        
        $report = '';
        
        $g = Group::createFromSlug(M1writers100::GROUP_SLUG); // DB
        
        self::$sourceSlug = M1writers100::LIST_SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

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
            'partial-ids.' . Muller::SOURCE_SLUG => 'MUID',
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
                return $p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '';
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
        if($generateSep){
            $report .= self::generateSep($g, $dozip);
        }
        return $report;
    }

    /**
        Generates a second export of the same group, with dates expressed in separate columns
        @param  $g is an object, passed by reference
    **/
    private static function generateSep($g, $dozip) {
        $report = '';
        
        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . str_replace('.csv', '-sep.csv', self::OUTPUT_FILE);
        
        $csvFields = [
            'MUID',
            'GQID',
            'FNAME',
            'GNAME',
            'Y',
            'MON',
            'D',
            'H',
            'MIN',
            'TZO',
            'Y-UT',
            'MON-UT',
            'D-UT',
            'H-UT',
            'MIN-UT',
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
            'partial-ids.' . Muller::SOURCE_SLUG => 'MUID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.tzo' => 'TZO',
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
                return $p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
            'Y' => function($p){
                return substr($p->data['birth']['date'], 0, 4);
            },
            'MON' => function($p){
                return substr($p->data['birth']['date'], 5, 2);
            },
            'D' => function($p){
                return substr($p->data['birth']['date'], 8, 2);
            },
            'H' => function($p){
                return substr($p->data['birth']['date'], 11, 2);
            },
            'MIN' => function($p){
                return substr($p->data['birth']['date'], 14, 2);
            },
            'Y-UT' => function($p){
                return substr($p->data['birth']['date-ut'], 0, 4);
            },
            'MON-UT' => function($p){
                return substr($p->data['birth']['date-ut'], 5, 2);
            },
            'D-UT' => function($p){
                return substr($p->data['birth']['date-ut'], 8, 2);
            },
            'H-UT' => function($p){
                return substr($p->data['birth']['date-ut'], 11, 2);
            },
            'MIN-UT' => function($p){
                return substr($p->data['birth']['date-ut'], 14, 2);
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
        $report .= $exportReport;
        return $report;
    }

} // end class    
