<?php
/********************************************************************************
    Generates files in data/output/history/1996-cfepp
    By default, the generated file is compressed (using zip).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-09-12 17:27:59+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use g5\app\Config;
use g5\model\DB5;
use g5\model\Group;
use g5\model\Names_fr;
use g5\commands\cfepp\CFEPP;
use g5\commands\gauq\LERRCP;
use g5\commands\ertel\Ertel;
use g5\commands\db\export\Export as ExportService;
use tiglib\patterns\Command;

class export implements Command {
                                                                                              
    /**
        Directory where the generated files are stored
        Relative to directory specified in config.yml by dirs / output
    **/
    const OUTPUT_DIR = 'history' . DS . '1996-cfepp';
    
    const OUTPUT_FILE_1120 = 'cfepp-1120-athletes.csv';
    const OUTPUT_FILE_1066 = 'cfepp-1066-athletes.csv';
    
    /**  Trick to access to $sourceSlug inside $sort function **/
    private static $sourceSlug;
    
    /** 
        Called by : php run-g5.php cfepp final3 export [optional parameters]
        If called without parameter, the output is compressed (using zip)
        For optional parameters
            - "zip" and "sep": see comment of class commands/db/export/Export
            - another parameter is possible with this command : "group" ; can be "1120" or "1066" ; dafault "1120"
            Example of optional parameters: "group=1066" ; "zip=false,sep=true,group=1120"
        @param $params array containing 0 or 1 element:
                       - Optional export parameters "zip" or "sep" or "group"
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 1){
            return "WRONG USAGE : useless parameter : '{$params[1]}'\n";
        }
        $dozip = true;
        $generateSep = false;
        $whichGroup='1120';
        if(count($params) == 1){
            [$dozip, $generateSep] = ExportService::computeOptionalParameters($params[0]);
            // handle $whichGroup
            $optional = G5::parseOptionalParameters($params[0]);
            if(isset($optional['group'])){
                if($optional['group'] == '1120' || $optional['group'] == '1066'){
                    $whichGroup = $optional['group'];
                }
                else{
                    return "INVALID OPTIONAL PARAMETER group={$optional['group']} - Can '1120' or '1066'\n";
                }
            }
        }
        
        $report = '';
        
        if($whichGroup == '1120'){
            $g = Group::createFromSlug(CFEPP::GROUP_1120_SLUG); // DB
            $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE_1120;
        }
        else {
            $g = Group::createFromSlug(CFEPP::GROUP_1066_SLUG); // DB
            $outfile = Config::$data['dirs']['output'] . DS . self::OUTPUT_DIR . DS . self::OUTPUT_FILE_1066;
        }
        
        self::$sourceSlug = Final3::SOURCE_SLUG; // Trick to access to $sourceSlug inside $sort function

        
        $csvFields = [
            'CFID',
            'ERID',
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
            // specific to this group, coming from original raw file
            // 'SRC',
            // 'LV',
            // 'TR',
            // 'M12'
        ];
        
        $map = [
            'partial-ids.' . CFEPP::SOURCE_SLUG => 'CFID',
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
            // stuff coming from original raw file
// BUG HERE - 
// some records: raw.history.0
// some records: raw.history.1
/* 
            'raw.history.0.raw.ELECTDAT' => 'ELECTDAT',
            'raw.history.0.raw.ELECTAGE' => 'ELECTAGE',
            'raw.history.0.raw.STBDATUM' => 'STBDATUM',
            'raw.history.0.raw.SONNE' => 'SONNE',
            'raw.history.0.raw.MOND' => 'MOND',
            'raw.history.0.raw.VENUS' => 'VENUS',
            'raw.history.0.raw.MARS' => 'MARS',
            'raw.history.0.raw.JUPITER' => 'JUPITER',
            'raw.history.0.raw.SATURN' => 'SATURN',
            'raw.history.0.raw.SO_' => 'SO_',
            'raw.history.0.raw.MO_' => 'MO_',
            'raw.history.0.raw.VE_' => 'VE_',
            'raw.history.0.raw.MA_' => 'MA_',
            'raw.history.0.raw.JU_' => 'JU_',
            'raw.history.0.raw.SA_' => 'SA_',
            'raw.history.0.raw.PHAS_' => 'PHAS_',
            'raw.history.0.raw.AUFAB' => 'AUFAB',
            'raw.history.0.raw.NIENMO' => 'NIENMO',
            'raw.history.0.raw.NIENVE' => 'NIENVE',
            'raw.history.0.raw.NIENMA' => 'NIENMA',
            'raw.history.0.raw.NIENJU' => 'NIENJU',
            'raw.history.0.raw.NIENSA' => 'NIENSA',
*/
        ];
        
        $fmap = [
            'FNAME' => function($p){
                // ok because all members are french
                return Names_fr::computeFamilyName($p->data['name']['family'], $p->data['name']['nobl']);
            },
            'GQID' => function($p){
                return $p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'ERID' => function($p){
                return $p->data['partial-ids'][Ertel::SOURCE_SLUG] ?? '';
            },
            'OCCU' => function($p){
                return implode('+', $p->data['occus']);
            },
        ];
        
        // sorts by CFEPP id
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
            'CFID',
            'ERID',
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
            'partial-ids.' . CFEPP::SOURCE_SLUG => 'CFID',
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
            'FNAME' => function($p){
                // ok because all members are french
                return Names_fr::computeFamilyName($p->data['name']['family'], $p->data['name']['nobl']);
            },
            'GQID' => function($p){
                return $p->data['partial-ids'][LERRCP::SOURCE_SLUG] ?? '';
            },
            'ERID' => function($p){
                return $p->data['partial-ids'][Ertel::SOURCE_SLUG] ?? '';
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
