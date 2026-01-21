<?php
/******************************************************************************
    
    Exports all skeptics data (cpara + csicop + cfepp) in a csv file
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2023-02-19 07:29:28+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use tiglib\patterns\Command;
use g5\G5;
use g5\app\Config;
use g5\model\Person;
use g5\model\Group;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;
use g5\commands\ertel\Ertel;
use g5\commands\cpara\CPara;
use g5\commands\csicop\CSICOP;
use g5\commands\cfepp\CFEPP;
use g5\commands\db\export\Export as ExportService;

class skeptics implements Command {
    
    /** 
        Paths of the generated files, relative to Config::$data['dirs']['output']
    **/
    const OUT_FILE = 'history' . DS . 'ogdb-skeptics.csv';
    
    /** 
        Called by : php run-g5.php db export skeptics [optional parameters]
        If called without parameter, the output is compressed (using zip)
        For optional parameters
            - "zip" and "sep": see comment of class commands/db/export/Export
        @param $params array containing 0 or 1 element:
                       - Optional export parameters "zip" or "sep"
        @return Report
    **/
    public static function execute($params=[]) {
        $msg = "USAGE:\n"
             . "php run-g5.php db export skeptics [optional parameters]\n"
             . "Optional parmeters can be 'zip' and 'sep'\n";
        if(count($params) > 1){
            return "USELESS PARAMETER: '{$params[1]}'\n$msg";
        }
        //
        $dozip = true;
        $generateSep = false;
        if(count($params) == 1){
            [$dozip, $generateSep] = ExportService::computeOptionalParameters($params[0]);
        }
        
        $report = '';
        
        $outfile = Config::$data['dirs']['output'] . DS . self::OUT_FILE;
        //$sql = "select * from person where partial_ids::JSONB ? 'cpara' or partial_ids::JSONB ? 'csicop' or partial_ids::JSONB ? 'cfepp' order by slug";
        $sql = "select * from person where
                    (partial_ids::JSONB ? 'cpara' and partial_ids->>'cpara' not like 'CP-*%')
                or  partial_ids::JSONB ? 'csicop'
                or  partial_ids::JSONB ? 'cfepp'
            order by slug";
        
        // Create a group, not stored in db, to use exportCSV
        $g = Group::createFromSQL($sql);                                       
        
        $csvFields = [
            'OGID',
            'GQID',
            'MUID',
            'ERID',
            'CPID',
            'CSID',
            'CFID',
            'EM',
            'OCCU',
            'FNAME',
            'GNAME',
            'DATE',
            'TZO',
            'DATE-UT',
            'PLACE',
            'C1',
            'C2',
            'C3',
            'CY',
            'LG',
            'LAT',
            'GEOID'
        ];
        
        $map = [
            'slug' => 'OGID',
            'birth.date' => 'DATE',
            'birth.date-ut' => 'DATE-UT',
            'birth.tzo' => 'TZO',
            'birth.place.name' => 'PLACE',
            'birth.place.c1' => 'C1',
            'birth.place.c2' => 'C2',
            'birth.place.c3' => 'C3',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            'birth.place.geoid' => 'GEOID',
        ];
        
        $fmap = [
            'FNAME' => function($p){
                return $p->data['name']['family'] ?? $p->data['name']['full'] ?? '';
            },
            'GNAME' => function($p){
                return $p->data['name']['given'] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
            'GQID' => function($p){
                return $p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'MUID' => function($p){
                return $p->data['partial-ids'][Muller::SOURCE_SLUG] ?? '';
            },
            'ERID' => function($p){
                return $p->data['partial-ids'][Ertel::SOURCE_SLUG] ?? '';
            },
            'CPID' => function($p){
                return $p->data['partial-ids'][CPara::SOURCE_SLUG] ?? '';
            },
            'CSID' => function($p){
                return $p->data['partial-ids'][CSICOP::SOURCE_SLUG] ?? '';
            },
            'CFID' => function($p){
                return $p->data['partial-ids'][CFEPP::SOURCE_SLUG] ?? '';
            },
            'EM' => function($p){ // eminence
                foreach($p->data['history'] as $history){
                    if(isset($history['raw']['ZITRANG'])){
                        return $history['raw']['ZITRANG'];
                    }
                }
                return ''; // should not happen
            },
        ];
        
        $filters = [];
        
        [$exportReport, $exportfile, $N] =  $g->exportCsv(
            csvFile:    $outfile,
            csvFields:  $csvFields,
            map:        $map,
            fmap:       $fmap,
            //sort:       $sort,
            filters:    $filters,
            dozip:      $dozip,
        );
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
            'OGID',
            'GQID',
            'MUID',
            'ERID',
            'CPID',
            'CSID',
            'CFID',
            'EM',
            'OCCU',
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
            'C1',
            'C2',
            'C3',
            'CY',
            'LG',
            'LAT',
            'GEOID'
        ];
        
        $map = [
            'slug' => 'OGID',
            'birth.tzo' => 'TZO',
            'birth.place.name' => 'PLACE',
            'birth.place.c1' => 'C1',
            'birth.place.c2' => 'C2',
            'birth.place.c3' => 'C3',
            'birth.place.cy' => 'CY',
            'birth.place.lg' => 'LG',
            'birth.place.lat' => 'LAT',
            'birth.place.geoid' => 'GEOID',
        ];
        
        $fmap = [
            'FNAME' => function($p){
                return $p->data['name']['family'] ?? $p->data['name']['full'] ?? '';
            },
            'GNAME' => function($p){
                return $p->data['name']['given'] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
            'GQID' => function($p){
                return $p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'MUID' => function($p){
                return $p->data['partial-ids'][Muller::SOURCE_SLUG] ?? '';
            },
            'ERID' => function($p){
                return $p->data['partial-ids'][Ertel::SOURCE_SLUG] ?? '';
            },
            'CPID' => function($p){
                return $p->data['partial-ids'][CPara::SOURCE_SLUG] ?? '';
            },
            'CSID' => function($p){
                return $p->data['partial-ids'][CSICOP::SOURCE_SLUG] ?? '';
            },
            'CFID' => function($p){
                return $p->data['partial-ids'][CFEPP::SOURCE_SLUG] ?? '';
            },
            'EM' => function($p){ // eminence
                foreach($p->data['history'] as $history){
                    if(isset($history['raw']['ZITRANG'])){
                        return $history['raw']['ZITRANG'];
                    }
                }
                return ''; // should not happen
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
        
        $filters = [];
        
        [$exportReport, $exportfile, $N] =  $g->exportCsv(
            csvFile:    $outfile,
            csvFields:  $csvFields,
            map:        $map,
            fmap:       $fmap,
            //sort:       $sort,
            filters:    $filters,
            dozip:      $dozip,
            SEP:        ',',
        );
        $report .= $exportReport;
        
        return $report;
    }
    
} // end class
