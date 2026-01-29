<?php
/******************************************************************************
    
    Stores all persons with given occupation codes in a csv file
    By default, the generated file is compressed (using zip).
    
    WARNING This command does not fill the field "downloads" of the group
    corresponding to the occupation (because several occus separated by + can be given as parameter,
    so there is no correspondance between occus handled and group stored in database).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-08-29 19:42:54+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use tiglib\patterns\Command;
use g5\app\Config;
use g5\model\Person;
use g5\model\Group;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;

class occu implements Command {
    
    /** 
        Supplementary parameters that are not handled by CLI interface
    **/
    const OTHER_PARAMS = [
        // if true, the generated file name will contain the number of records
        'add_number_in_file_name' => true,
    ];
    
    /** 
        @param  $params array containing 2 or 3 elements :
                        - a list of occupation slugs separated by +
                        - The path to the output file.
                          Path relative to directory specified in config.yml by dirs / output (defaults to data/output)
                        - An optional string "nozip"
                        - An optional string "sep", indicating that a file with separated columns for dates must be also generated 
                        - An optional string "full", indicating that the function must
                          return an associative array with 3 elements :
                            - A report.
                            - The name of the file where the export is stored.
                            - The number of elements in the group
                          If "full" is not present, the function returns a report as usual.
    **/                                                                                          
    public static function execute($params=[]) {
        
        $msg = "USAGE : php run-g5.php db export occu <profession codes> <path to output file> [nozip] [full] [sep]\n"
            . "  To export several professions, separate the codes by \"+\"\n"
            . "  Use 'nozip' to export a csv instead of a zipped csv.\n"
            . "  Use 'sep' to also export a csv file where date are expressed in separate comumns (one column for the year, one for the month etc.).\n"
            . "  Use 'full' to indicate that the function must return an array with 3 elements :\n"
            . "    - the report.\n"
            . "    - the file name where the csv was stored (can be a .zip file).\n"
            . "    - the number of elements of the stored group.\n"
            . "Examples :\n"
            . "  php run-g5.php db export occu skier path/to/output.csv\n"
            . "  php run-g5.php db export occu skier path/to/output.csv nozip\n"
            . "  php run-g5.php db export occu skier path/to/output.csv nozip full\n"
            . "  php run-g5.php db export occu skier path/to/output.csv nozip sep\n"
            . "  php run-g5.php db export occu skier path/to/output.csv nozip sep full\n"
            . "  php run-g5.php db export occu skier+writer path/to/output.csv\n"
            ;
        if(count($params) < 2){
            return "MISSING PARAMETER'\n" . $msg;
        }
        // optional parameters
        $possibles = ['nozip', 'full', 'sep'];
        $optionals = array_slice($params, 2);
        $diff = array_diff($optionals, $possibles);
        if(count($diff) != 0){
            return 'INVALID PARAMETER' . (count($diff) > 1 ? 'S: ' : ': ') . implode(', ', $diff) . "\n" . $msg;
        
        }
        $dozip = !in_array('nozip', $optionals);
        $returnType = in_array('full', $optionals) ? 'full' : 'report';
        $generateSep = in_array('sep', $optionals);
        
        $occus1 = explode('+', $params[0]);
        $occus = [];
        foreach($occus1 as $occu){
            $children = Group::getDescendants($occu, includeSeed:true);
            if(!empty($children)){
                $occus = array_merge($occus, $children);
            }
        }
        
        $outfile = Config::$data['dirs']['output'] . DS . $params[1];
        
        $report = '';
        
        $persons = Person::createArrayFromOccus($occus); // DB
        if(count($persons) == 0){
            $report .= "GROUP NOT EXPORTED - '{$params[0]}' corresponds to 0 persons\n";
            if($returnType == 'report'){
                return $report;
            }
            return [$report, '', 0];
            
        }
        
        // Can't do Group::createFromSlug() because possibly several occus
        $g = Group::createEmpty();
        $g->data['person-members'] =& $persons;
        $g->personMembersComputed = true;
        
        $csvFields = [
            'GQID',
            'MUID',
            'FNAME',
            'GNAME',
            'OCCU',
            'DATE',
            'TZO',
            'DATE-UT',
            'PLACE',
            'C2',
            'C3',
            'CY',
            'LG',
            'LAT',
            'GEOID'
        ];
        
        $map = [
            'name.family' => 'FNAME',
            'name.given' => 'GNAME',
            'birth.date' => 'DATE',
            'birth.date-ut' => 'DATE-UT',
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
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
            'GQID' => function($p){
                return ($p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '');
            },
            'MUID' => function($p){
                return Muller::ids_in_sources2mullerId($p->data['ids-in-sources']);
            },
        ];
        
        // sorts by name
        $sort = function($a, $b){
            $nameA = $a->data['name']['family'] . ' ' . $a->data['name']['given'];
            $nameB = $b->data['name']['family'] . ' ' . $b->data['name']['given'];
            return $nameA <=> $nameB;
        };
        
        $filters = [];
        
        if(self::OTHER_PARAMS['add_number_in_file_name']){
            $outfile = Export::add_number_in_file_name($outfile, count($persons));
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
        
        if($returnType == 'report'){
            return $report;
        }
        return [$report, $exportfile, $N];
    }
    
    /**
        Generates a second export of the same group, with dates expressed in separate columns
        @param  $g is an object, passed by reference
    **/
    private static function generateSep($g, $dozip, $outfile) {
        $report = '';
        
        $outfile = str_replace('.csv', '-sep.csv', $outfile);
        
        $csvFields = [
            'GQID',
            'MUID',
            'FNAME',
            'GNAME',
            'OCCU',
            'Y',
            'MON',
            'D',
            'H',
            'MIN',
            'TZO',
            'UT_Y',
            'UT_MON',
            'UT_D',
            'UT_H',
            'UT_MIN',
            'PLACE',
            'C2',
            'C3',
            'CY',
            'LG',
            'LAT',
            'GEOID'
        ];
        
        $map = [
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
            'UT_Y' => function($p){
                return substr($p->data['birth']['date-ut'], 0, 4);
            },
            'UT_MON' => function($p){
                return substr($p->data['birth']['date-ut'], 5, 2);
            },
            'UT_D' => function($p){
                return substr($p->data['birth']['date-ut'], 8, 2);
            },
            'UT_H' => function($p){
                return substr($p->data['birth']['date-ut'], 11, 2);
            },
            'UT_MIN' => function($p){
                return substr($p->data['birth']['date-ut'], 14, 2);
            },
        ];
        
        // sort by name
        $sort = function($a, $b){
             return $a->data['name']['family'] <=> $b->data['name']['family'];
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
