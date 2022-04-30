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
use g5\app\Config;
use g5\model\Person;
use g5\model\Group;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;

class allpersons implements Command {
    
    /** 
        Paths of the generated files, relative to Config::$data['dirs']['output']
    **/
    const OUT_FILE_ALL = 'ogdb-all.csv';
    const OUT_FILE_TIME = 'ogdb-time.csv';
    
    /** 
        Possible params handled by CLI
    **/
    const POSSIBLE_PARAMS = [
        'time' => 'Exports only persons with birth time',
    ];
    
    /** 
        Supplementary parameters that are not handled by CLI interface
    **/
    const OTHER_PARAMS = [
        // if true, the generated file name will contain the number of records
        'add_number_in_file_name' => false,
    ];
    
    /** 
        @param  $params empty array
                        or array containing one element: 'time'
    **/                                                                                          
    public static function execute($params=[]) {
        $report = '';
        $msg = "USAGE:\n"
             . "php run-g5.php db export all        => exports all persons, with and without birth times\n"
             . "php run-g5.php db export all time   => exports only persons with birth times\n";
        if(count($params) > 1){
            return "USELESS PARAMETER: '{$params[1]}'\n$msg";
        }
        if(count($params) == 1 && !in_array($params[0], array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER: '{$params[0]}'\n$msg";
        }
        
        $timeonly = count($params) == 1;
        if($timeonly){
            $outfile = Config::$data['dirs']['output'] . DS . self::OUT_FILE_TIME;
            $sql = "select * from person where length(birth->>'date') > 10 or length(birth->>'date-ut') > 10";
        }
        else{
            $outfile = Config::$data['dirs']['output'] . DS . self::OUT_FILE_ALL;
            $sql = 'select * from person';
        }
        
        $dozip = true;

        // Create a group, not stored in db, to use exportCSV
        $g = Group::createFromSQL($sql);
        
        $csvFields = [
            'OGDBID',
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
            'slug' => 'OGDBID',
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
            $nameA = trim($a->familyName() . ' ' . $a->givenName());
            $nameB = trim($b->familyName() . ' ' . $b->givenName());
            return $nameA <=> $nameB;
        };
        
        $filters = [];
        
        if(self::OTHER_PARAMS['add_number_in_file_name']){
            $outfile = Export::add_number_in_file_name($outfile, $g->data['n']);
        }
        
        [$report, $exportfile, $N] =  $g->exportCsv(
            csvFile:    $outfile,
            csvFields:  $csvFields,
            map:        $map,
            fmap:       $fmap,
            sort:       $sort,
            filters:    $filters,
            dozip:      $dozip,
        );
        return $report;
    }
    
} // end class
