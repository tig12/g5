<?php
/******************************************************************************
    
    Stores all persons with given occupation codes in a csv file
    By default, the generated file is compressed (using zip).
    
    WARNING This command does not fill the field "downloads" of the group
    corresponding to the occupation (because several occus separated by + can be given as parameter,
    so there is no correspondance between occus handled and group stored in database).
    
    @license    GPL
    @history    2020-08-29 19:42:54+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\export;

use g5\patterns\Command;
use g5\Config;
use g5\model\Person;
use g5\model\Group;
use g5\model\Occupation;
use g5\commands\gauquelin\LERRCP;
use g5\commands\muller\AFD;

class occu implements Command {
    
    /** 
        Supplementary parameters that are not handled by CLI interface
    **/
    const PARAMS = [
        // if true, the generated file name will be prefixed by the number of records
        'add_number_in_name' => true,
    ];
    
    /** 
        @param  $params array containing 2 or 3 elements :
                        - a list of occupation slugs separated by +
                        - The path to the output file, relative to directory specified in config.yml by dirs / output
                        - An optional string "nozip"
                        - An optional string "full", indicating that the function must
                          return an associative array with 3 elements :
                            - A report.
                            - The name of the file where the export is stored.
                            - The number of elements in the group
                          If "full" is not present, the function returns a report as usual.
    **/                                                                                          
    public static function execute($params=[]) {
        $msg = "USAGE : php run-g5.php db export occu <profession codes> <path to output file> [nozip] [full]\n"
            . "  To export several professions, separate the codes by \"+\"\n"
            . "  Use 'nozip' to export a csv instead of a zipped csv.\n"
            . "  Use 'full' to indicate that the function must return an array with 3 elements :\n"
            . "    - the report.\n"
            . "    - the file name where the csv was stored (can be a .zip file).\n"
            . "    - the number of elements of the stored group.\n"
            . "Examples :\n"
            . "  php run-g5.php db export skier path/to/output.csv\n"
            . "  php run-g5.php db export skier path/to/output.csv nozip\n"
            . "  php run-g5.php db export skier path/to/output.csv nozip full\n"
            . "  php run-g5.php db export skier+writer path/to/output.csv\n"
            ;
        $possibles = ['nozip', 'full'];
        if(count($params) > 4){
            return "USELESS PARAMETER : '{$params[4]}'\n" . $msg;
        }
        if(count($params) == 4 && !in_array($params[3], $possibles)){
            return "INVALID PARAMETER : '{$params[3]}'\n" . $msg;
        }
        if(count($params) == 3 && !in_array($params[2], $possibles)){
            return "INVALID PARAMETER : '{$params[2]}'\n" . $msg;
        }
        if(count($params) < 2){
            return "MISSING PARAMETER'\n" . $msg;
        }
        $dozip = true;
        $returnType = 'report';
        if(count($params) == 4 && ($params[2] == 'nozip' || $params[3] == 'nozip')){
            $dozip = false;
        }
        if(count($params) == 4 && ($params[2] == 'full' || $params[3] == 'full')){
            $returnType = 'full';
        }
        if(count($params) == 3 && $params[2] == 'nozip'){
            $dozip = false;
        }
        if(count($params) == 3 && $params[2] == 'full'){
            $returnType = 'full';
        }
        
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
        
        $persons = Person::getByOccu($occus); // DB
        if(count($persons) == 0){
            $report .= "GROUP NOT EXPORTED - '{$params[0]}' corresponds to 0 persons\n";
            if($returnType == 'report'){
                return $report;
            }
            return [$report, '', 0];
            
        }
        
        // Can't do Group::getBySlug() because possibly several occus
        $g = new Group();
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
                return ($p->data['ids-in-sources'][LERRCP::SOURCE_SLUG] ?? '');
            },
            'MUID' => function($p){
                return AFD::ids_in_sources2mullerId($p->data['ids-in-sources']);
            },
        ];
        
        // sorts by name
        $sort = function($a, $b){
            $nameA = $a->data['name']['family'] . ' ' . $a->data['name']['given'];
            $nameB = $b->data['name']['family'] . ' ' . $b->data['name']['given'];
            return $nameA <=> $nameB;
        };
        
        $filters = [];
        
        if(self::PARAMS['add_number_in_name']){
            $outfile = self::add_number_in_name($outfile, count($persons));
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
        if($returnType == 'report'){
            return $report;
        }
        return [$report, $exportfile, $N];
    }
    
    // ************************* Auxiliary functions *****************************
    private static function add_number_in_name($file, $N){
        $pathinfo = pathinfo($file);
        return $pathinfo['dirname'] . DS . $pathinfo['filename'] . "-$N." . $pathinfo['extension'];
    }
    
} // end class
