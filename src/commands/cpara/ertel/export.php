<?php
/********************************************************************************
    Generates data/output/history/1976-cpara/cpara-535-athletes.csv
    By default, the generated file is compressed (using zip).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-02-16 20:22:43+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cpara\ertel;

use g5\G5;
use g5\app\Config;
use g5\model\DB5;
use g5\model\Group;
use g5\commands\cpara\CPara;
use g5\commands\gauq\LERRCP;
use g5\commands\db\export\Export as ExportService;
use tiglib\patterns\Command;

class export implements Command {
                                                                                              
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1976-cpara';
    
    const OUTPUT_FILE = 'cpara-535-athletes.csv';
    
    /**  Trick to access to $sourceSlug inside $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php cfepp final3 export [optional parameters]
        If called without parameter, the output is compressed (using zip)
        For optional parameters, see comment of class commands/db/export/Export
        @param $params array containing 0 or 1 element : or 
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
        
        $g = Group::createFromSlug(CPara::GROUP_SLUG); // DB
        
        self::$sourceSlug = CPara::SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE;
        
        $csvFields = [
            'CPID',
            'GQID',
            'FNAME',
            'GNAME',
            'DATE',
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
            'partial-ids.' . CPara::SOURCE_SLUG => 'CPID',
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
        
        $fmap = [
            'GQID' => function($p){
                return $p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        // sorts by CPara id
        $sort = function($a, $b){
             return $a->data['partial-ids'][self::$sourceSlug] <=> $b->data['partial-ids'][self::$sourceSlug];
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
            'CPID',
            'GQID',
            'FNAME',
            'GNAME',
            'Y',
            'MON',
            'D',
            'H',
            'MIN',
            'Y-UT',
            'MON-UT',
            'D-UT',
            'H-UT',
            'MIN-UT',
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
            'partial-ids.' . CPara::SOURCE_SLUG => 'CPID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.place.name' => 'PLACE',
            'birth.place.c2' => 'C2',
            'birth.place.c3' => 'C3',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            'birth.place.geoid' => 'GEOID',
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
        
        // sorts by CFEPP id
        $sort = function($a, $b){
             return $a->data['partial-ids'][self::$sourceSlug] <=> $b->data['partial-ids'][self::$sourceSlug];
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
            SEP:        ',',
        );
        
        $report .= $exportReport;
        return $report;
    }
    
} // end class
