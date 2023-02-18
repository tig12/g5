<?php
/********************************************************************************
    Generates data/output/history/1993-muller2-men/muller-612-men.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-01-02 16:17:28+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\m2men;

use g5\app\Config;
use tiglib\patterns\Command;
use g5\model\Group;
use g5\commands\muller\Muller;
use g5\commands\gauq\LERRCP;
use g5\commands\db\export\Export as ExportService;

class export implements Command {
    
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1992-muller2-men';
    
    const OUTPUT_FILE = 'muller-612-men.csv';
    
    /**  Trick to access to $sourceSlug within $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php muller m2men export [optional parameters]
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
        
        $g = Group::createFromSlug(M2men::GROUP_SLUG); // DB
        
        self::$sourceSlug = M2men::LIST_SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE;
        
        $csvFields = [
            'MUID',
            'GQID',
            'FNAME',
            'GNAME',
            'DATE',
            'DATE-UT',
            'TZO',
            'TIMOD',
            'PLACE',
// TODO handle correctly C1 and C2 in tmp2db
//            'C1',
//            'C2',
            'CY',
            'LG',
            'LAT',
            'GEOID',
            'OCCU',
        ];
        
        $map = [
            'partial-ids.' . Muller::SOURCE_SLUG => 'MUID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.date-ut' => 'DATE-UT',
            'birth.tzo' => 'TZO',
            'birth.note' => 'TIMOD',
            'birth.place.name' => 'PLACE',
//            'birth.place.c1' => 'C1',
//            'birth.place.c2' => 'C2',
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
            'Y-UT',
            'MON-UT',
            'D-UT',
            'H-UT',
            'MIN-UT',
            'TZO',
            'TIMOD',
            'PLACE',
// TODO handle correctly C1 and C2 in tmp2db
//            'C1',
//            'C2',
            'CY',
            'LG',
            'LAT',
            'GEOID',
            'OCCU',
        ];
        
        $map = [
            'partial-ids.' . Muller::SOURCE_SLUG => 'MUID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.tzo' => 'TZO',
            'birth.note' => 'TIMOD',
            'birth.place.name' => 'PLACE',
//            'birth.place.c1' => 'C1',
//            'birth.place.c2' => 'C2',
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
            SEP:        ',',
        );
        $report .= $exportReport;
        return $report;
    }
    
} // end class    
