<?php
/******************************************************************************
    
    Stores all persons of the database in a csv file
    2 possible calls :
    php run-g5.php db export all        => exports all persons, with and without birth times
    php run-g5.php db export all time   => exports only persons with birth times
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-01-29 08:18:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use tiglib\patterns\Command;
use g5\G5;
use g5\app\Config;
use g5\model\Person;
use g5\model\Group;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;
use g5\commands\db\export\Export as ExportService;

class allpersons implements Command {
    
    /** 
        Paths of the generated files, relative to Config::$data['dirs']['output']
    **/
    const OUT_FILE_ALL = 'ogdb-all.csv';
    const OUT_FILE_TIME = 'ogdb-time.csv';
    const OUT_FILE_NOTIME = 'ogdb-no-time.csv';
    
    /** 
        Supplementary parameters that are not handled by CLI interface
    **/
    const OTHER_PARAMS = [
        // if true, the generated file name will contain the number of records
        'add_number_in_file_name' => false,
    ];
    
    /** 
        Called by : php run-g5.php cfepp final3 export [optional parameters]
        If called without parameter, the output is compressed (using zip)
        For optional parameters
            - "zip" and "sep": see comment of class commands/db/export/Export
            - another parameter is possible with this command : "what" ; indicates what to export
                - what=time : exports only timed data
                - what=notime : exports only untimed data
                - what not specified : exports timed and untimed data
            Example of optional parameters: "group=1066" ; "zip=false,sep=true,what=time"
        @param $params array containing 0 or 1 element:
                       - Optional export parameters "zip" or "sep" or "what"
        @return Report
    **/
    public static function execute($params=[]) {
        $msg = "USAGE:\n"
             . "php run-g5.php db export all [optional parameters]\n"
             . "Optional parmeters can be 'what', 'zip' and 'sep'\n";
        if(count($params) > 1){
            return "USELESS PARAMETER: '{$params[1]}'\n$msg";
        }
        //
        $dozip = true;
        $generateSep = false;
        $what = '';
        if(count($params) == 1){
            [$dozip, $generateSep] = ExportService::computeOptionalParameters($params[0]);
            // handle $whichGroup
            $optional = G5::parseOptionalParameters($params[0]);
            if(isset($optional['what'])){
                if($optional['what'] == 'time' || $optional['what'] == 'notime'){
                    $what = $optional['what'];
                }
                else{
                    return "INVALID OPTIONAL PARAMETER what={$optional['what']} - Can be 'time' or 'notime'\n";
                }
            }
        }
        
        $report = '';
        
        if($what == ''){
            $outfile = Config::$data['dirs']['output'] . DS . self::OUT_FILE_ALL;
            $sql = 'select * from person';
        }
        else if($what == 'time'){
            $outfile = Config::$data['dirs']['output'] . DS . self::OUT_FILE_TIME;
            $sql = "select * from person where length(birth->>'date') > 10 or length(birth->>'date-ut') > 10";
        }
        else{
            $outfile = Config::$data['dirs']['output'] . DS . self::OUT_FILE_NOTIME;
            //$sql = "select * from person where length(birth->>'date') < 11 and length(birth->>'date-ut') < 11";
            $sql = "select * from person where length(birth->>'date') < 11";
        }
        
        // Create a group, not stored in db, to use exportCSV
        $g = Group::createFromSQL($sql);
        
        $csvFields = [
            'OGID',
            'OCCU',
            'GQID',
            'MUID',
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
                return $p->data['name']['family'] ?? $p->data['name']['fame']['family'] ?? $p->data['name']['fame']['full'] ?? '';
            },
            'GNAME' => function($p){
                return $p->data['name']['given'] ?? $p->data['name']['fame']['given'] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
            'GQID' => function($p){
                return ($p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '');
            },
            'MUID' => function($p){
                return ($p->data['partial-ids'][Muller::SOURCE_SLUG] ?? '');
            },
        ];
        
        // sorts persons by name
        $sort = function(Person $a, Person $b){
            return $a->getCommonName() <=> $b->getCommonName();
        };
        
        $filters = [];
        
        if(self::OTHER_PARAMS['add_number_in_file_name']){
            $outfile = Export::add_number_in_file_name($outfile, $g->data['n']);
        }
        
        [$exportReport, $exportfile, $N] =  $g->exportCsv(
            csvFile:    $outfile,
            csvFields:  $csvFields,
            map:        $map,
            fmap:       $fmap,
            sort:       $sort,
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
            'OCCU',
            'GQID',
            'MUID',
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
                return $p->data['name']['family'] ?? $p->data['name']['fame']['family'] ?? $p->data['name']['fame']['full'] ?? '';
            },
            'GNAME' => function($p){
                return $p->data['name']['given'] ?? $p->data['name']['fame']['given'] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
            'GQID' => function($p){
                return ($p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '');
            },
            'MUID' => function($p){
                return ($p->data['partial-ids'][Muller::SOURCE_SLUG] ?? '');
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
        
        // sorts persons by name
        $sort = function(Person $a, Person $b){
            return $a->getCommonName() <=> $b->getCommonName();
        };
        
        $filters = [];
        
        if(self::OTHER_PARAMS['add_number_in_file_name']){
            $outfile = Export::add_number_in_file_name($outfile, $g->data['n']);
        }
        
        [$exportReport, $exportfile, $N] =  $g->exportCsv(
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
