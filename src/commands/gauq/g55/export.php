<?php
/********************************************************************************
    Generates data/output/history/1955-gauquelin/*.csv
    By default, the generated file is compressed (using zip).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-02-19 18:35:37+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\g55;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Group;
use g5\commands\gauq\LERRCP;
use g5\commands\db\export\Export as ExportService;
use tiglib\patterns\Command;

class export implements Command {
                                            
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1955-gauquelin';
    
    /**  Trick to access to $sourceSlug inside $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php gauq g55 export <g55 group> [optional parameters]
        If called without optional parameter, the output is compressed (using zip)
        For optional parameters, see comment of class commands/db/export/Export
        The command is redirected from gauq/all/export and handled by GauqRouter, so contains useless parameters.
        @param $params array containing  or 3 or 4 elements :
                       - 'g55' (useless)
                       - 'export' (useless)
                       - key of the group to export
                       - Optional export parameters "zip" or "sep"
        @return Report
    **/
    public static function execute($params=[]): string{
        $msg = "Example of calls:\n"
        . "php run-g5.php gauq g55 export 01-576-physicians\n"
        . "php run-g5.php gauq g55 export 01-576-physicians zip=false\n"
        . "php run-g5.php gauq g55 export 01-576-physicians zip=false,sep=true\n";
        if(count($params) > 4){
            return "WRONG USAGE : useless parameter : '{$params[4]}'\n$msg";
        }
        if(count($params) < 3){
            return "WRONG USAGE : missing parameter(s)'\n$msg";
        }
        
        $groupKey = $params[2];
        if(!in_array($groupKey, G55::getPossibleGroupKeys())){
                return "INVALID GROUP: '$groupKey'\nPossible groups:\n    "
                    . implode("\n    ", G55::getPossibleGroupKeys()) . "\n";
        }
        $groupSlug = G55::groupKey2slug($groupKey);
        $dozip = true;
        $generateSep = false;
        if(count($params) == 4){
            [$dozip, $generateSep] = ExportService::computeOptionalParameters($params[3]);
        }
        
        $report = '';
        
        $g = Group::createFromSlug($groupSlug); // DB
        
        self::$sourceSlug = G55::SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

        $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . $groupSlug . '.csv';
        
        $csvFields = [
            'G55ID',
            'GQID',
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
            'partial-ids.' . G55::SOURCE_SLUG => 'G55ID',
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
            'GQID' => function($p){
                return $p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        // sorts by G55 id
        $sort = function($a, $b){
             return strnatcmp($a->data['ids-in-sources'][G55::SOURCE_SLUG], $b->data['ids-in-sources'][G55::SOURCE_SLUG]);
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
            $report .= self::generateSep($g, $dozip, $outfile);
        }
        
        return $report;
    }
    
    /**
        Generates a second export of the same group, with dates expressed in separate columns
        @param  $g is an object, passed by reference
    **/
    private static function generateSep($g, $dozip, $outfile) {
        $report = '';
        
        $outfile = str_replace('.csv', '-sep.csv', $outfile);
        
        $csvFields = [
            'G55ID',
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
            'partial-ids.' . G55::SOURCE_SLUG => 'G55ID',
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.tzo' => 'TZO',
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
        
        // sorts by G55 id
        $sort = function($a, $b){
             return strnatcmp($a->data['ids-in-sources'][G55::SOURCE_SLUG], $b->data['ids-in-sources'][G55::SOURCE_SLUG]);
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
