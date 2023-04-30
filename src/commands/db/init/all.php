<?php
/******************************************************************************
    
    Fills database from scratch with historical data.
    WARNING : all existing tables are dropped and recreated.
    Precise order of the executed steps must be respcted to obtain a coherent result.
    
    Order of execution may not seem logical (ex: Müller1 should be done before Müller5),
    but it corresponds to the order of integration in g5, as things progressed.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-08-17 20:18:47+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\init;

use tiglib\patterns\Command;
use g5\commands\gauq\GauqRouter;

// for information sources
use g5\G5;
use g5\commands\gauq\Gauquelin;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;
use g5\commands\muller\AFD;
use g5\commands\ertel\Ertel;
use g5\commands\gauq\Cura5;
use g5\commands\Newalch;
use g5\commands\wd\Wikidata;

use g5\commands\db\init\dbcreate                as dbInitDBCreate;
use g5\commands\db\init\occus1                  as dbInitOccu1;
use g5\commands\db\init\occus2                  as dbInitOccu2;
use g5\commands\db\fill\source                  as dbFillSource;
use g5\commands\db\init\tweaks                  as dbInitTweaks;
use g5\commands\db\init\stats                   as DBInitStats;
use g5\commands\db\init\wiki                    as dbInitWiki;

// order of imports corresponds to order of execution

// raw2tmp
use g5\commands\gauq\A\raw2tmp                  as Araw2tmp;
use g5\commands\gauq\A\addGeo                   as AaddGeo;
use g5\commands\gauq\A\legalTime                as AlegalTime;
use g5\commands\gauq\D6\raw2tmp                 as D6raw2tmp;
use g5\commands\gauq\D6\addGeo                  as D6addGeo;
use g5\commands\gauq\D10\raw2tmp                as D10raw2tmp;
use g5\commands\gauq\E1_E3\raw2tmp              as E1E3raw2tmp;
use g5\commands\gauq\all\tweak2tmp              as Gauqtweak2tmp;

use g5\commands\ertel\sport\raw2tmp             as ErtelSportRaw2tmp;
use g5\commands\ertel\sport\tweak2tmp           as ErtelSporttweak2tmp;
use g5\commands\ertel\sport\fixA1               as ErtelSportFixA1;

use g5\commands\muller\m5medics\raw2tmp         as M5MedicsRaw2tmp;
use g5\commands\muller\m5medics\tweak2tmp       as M5MedicsTweak2tmp;
use g5\commands\muller\m5medics\fixGnr          as M5MedicsFixGnr;

use g5\commands\muller\m1writers\raw2tmp        as M1WritersRaw2tmp;
use g5\commands\muller\m1writers\tweak2tmp      as M1WritersTweak2tmp;
use g5\commands\muller\m1writers\gauq           as M1WritersGauq;
use g5\commands\muller\m1writers\raw2tmp100     as M1Writers100raw2tmp;

use g5\commands\csicop\si42\raw2tmp             as si42raw2tmp;
use g5\commands\csicop\si42\addCanvas1          as si42addCanvas1;
use g5\commands\csicop\irving\raw2tmp           as csiIrvingRaw2tmp;
use g5\commands\csicop\irving\addD10            as csiIrvingAddD10;

use g5\commands\muller\m3women\raw2tmp          as M3WomenRaw2tmp;

use g5\commands\muller\m2men\raw2tmp            as M2MenRaw2tmp;

use g5\commands\cfepp\final3\raw2tmp            as CFEPPRaw2tmp;
use g5\commands\cfepp\final3\ids                as CFEPPIds;

use g5\commands\gauq\g55\raw2tmp                as g55Raw2tmp;
use g5\commands\gauq\g55\gqid                   as g55Gqid;
use g5\commands\gauq\g55\special                as g55Special;

// tmp2db
use g5\commands\gauq\A\tmp2db                   as Atmp2db;
use g5\commands\gauq\A\A6occu                   as A6occu;
use g5\commands\gauq\D6\tmp2db                  as D6tmp2db;
use g5\commands\gauq\D10\tmp2db                 as D10tmp2db;
use g5\commands\gauq\E1_E3\tmp2db               as E1E3tmp2db;
use g5\commands\muller\m1writers\tmp2db         as M1writersTmp2db;
use g5\commands\muller\m1writers\tmp2db100      as M1writers100tmp2db;
use g5\commands\muller\m2men\tmp2db             as M2menTmp2db;
use g5\commands\muller\m3women\tmp2db           as M3womenTmp2db;
use g5\commands\muller\m5medics\tmp2db          as M5medicsTmp2db;
use g5\commands\csicop\irving\tmp2db            as csiIrvingTmp2db;
use g5\commands\ertel\sport\tmp2db              as ErteSportTmp2db;
use g5\commands\cfepp\final3\tmp2db             as CFEPPTmp2db;
use g5\commands\cpara\ertel\group               as CParaGroup;
use g5\commands\gauq\g55\tmp2db                 as g55Tmp2db;

// wiki
use g5\commands\wiki\project\addall             as wikiAddAllProjects;
use g5\commands\wiki\bc\addall                  as wikiAddAllBCs;

// finalize
use g5\commands\db\init\stats;
use g5\commands\db\init\search;

// export
use g5\commands\gauq\all\export                 as curaExport;
use g5\commands\muller\m1writers\export         as M1WritersExport;
use g5\commands\muller\m1writers\export100      as M1Writers100export;
use g5\commands\muller\m2men\export             as M2MenExport;
use g5\commands\muller\m3women\export           as M3WomenExport;
use g5\commands\cfepp\final3\export             as CFEPPExport;
use g5\commands\muller\m5medics\export          as M5MedicsExport;
use g5\commands\csicop\irving\export            as csiIrvingExport;
use g5\commands\db\export\skeptics              as skepticsExport;
use g5\commands\ertel\sport\export              as ertelExport;
use g5\commands\db\export\alloccus              as allOccusExport;
use g5\commands\db\export\allpersons            as allPersonsExport;
use g5\commands\db\export\pgdump                as pgdumpExport;

class all implements Command {
    
    /** 
        Possible values of the command
    **/
    const POSSIBLE_PARAMS = [
        'tmp'       => 'Build tmp files in data/tmp',
        'db'        => 'Fill database with tmp files',
        'wiki'      => 'Adds with wiki data to the database',
        'finalize'  => 'Finalize DB (stats, groups, search)',
        'export'    => 'Exports the groups in zipped csv files',
        'all'       => 'Executes all steps',
    ];
    
    /** 
        @param  $params empty array
        @return Empty string, echoes the reports of individual commands progressively. 
    **/                                                                  
    public static function execute($params=[]): string {
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "    $k : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[1]} - this command takes only one parameter :\n$possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER: $param\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $filesGauqA = GauqRouter::computeDatafiles('A');
        
        $t1 = microtime(true);
        
        $g55Groups = [
            '01-576-physicians',
            '02-508-physicians',
            '03-570-sportsmen',
            '04-676-military',
            '05-906-painters',
            '06-361-minor-painters',
            '07-500-actors',
            '08-494-deputies',
            '09-349-scientists',
            '10-884-priests',
        ];
        
        //
        //  1 - Create tmp files from raw data
        //
        if($param == 'tmp' || $param == 'all'){
            echo "***********************\n";
            echo "*** Build tmp files ***\n";
            echo "***********************\n";
            
            foreach($filesGauqA as $datafile){
                echo Araw2tmp::execute([$datafile, 'raw2tmp', 'small']);
                echo Gauqtweak2tmp::execute([$datafile, 'tweak2tmp']);
                echo AaddGeo::execute([$datafile, 'addGeo', 'small']);
                echo AlegalTime::execute([$datafile, 'legalTime']);
            }
            
            echo D6raw2tmp::execute(['D6', 'raw2tmp']);
            echo D6addGeo::execute(['D6', 'addGeo']); // tmp code - addGeo needs to be fixed
            
            echo D10raw2tmp::execute(['D10', 'raw2tmp']);
            
            echo E1E3raw2tmp::execute(['E1', 'raw2tmp', 'small']);
            echo Gauqtweak2tmp::execute(['E1', 'tweak2tmp']);
            
            echo E1E3raw2tmp::execute(['E3', 'raw2tmp', 'small']);
            
            echo ErtelSportRaw2tmp::execute([]);
            echo ErtelSporttweak2tmp::execute([]);
            echo ErtelSportFixA1::execute(['update']);
            
            echo M5MedicsRaw2tmp::execute([]);
            echo M5MedicsTweak2tmp::execute([]);
            echo M5MedicsFixGnr::execute(['update']);
            
            echo si42raw2tmp::execute([]);                                   
            echo si42addCanvas1::execute([]);
            echo csiIrvingRaw2tmp::execute([]);
            echo csiIrvingAddD10::execute([]);
            
            echo M1WritersRaw2tmp::execute([]);
            echo M1WritersTweak2tmp::execute([]);
            echo M1WritersGauq::execute(['update']);
            echo M1Writers100raw2tmp::execute([]);
            
            echo M3WomenRaw2tmp::execute([]);
            
            echo M2MenRaw2tmp::execute([]);
            
            echo CFEPPRaw2tmp::execute([]);
            echo CFEPPIds::execute([]);
            
            // note g55 raw2tmp is not done here because the cache needs the database
            // = done just before g55 tmp2db
        }
        
        //
        //  2 - Import tmp files to db
        //
        if($param == 'db' || $param == 'all'){
            
            echo "***********************\n";
            echo "***  Fill database  ***\n";
            echo "***********************\n";
            echo dbInitDBCreate::execute([]);
            // Main sources are inserted here because they are used in various places
            // Sources related to specific groups are inserted in the code of related tmp2db
            echo dbFillSource::execute([Gauquelin::SOURCE_DEFINITION_FILE]);
            echo dbFillSource::execute([LERRCP::SOURCE_DEFINITION_FILE]);
            echo dbFillSource::execute([Muller::SOURCE_DEFINITION_FILE]);
            echo dbFillSource::execute([AFD::SOURCE_DEFINITION_FILE]);
            echo dbFillSource::execute([Ertel::SOURCE_DEFINITION_FILE]);
            echo dbFillSource::execute([Cura5::SOURCE_DEFINITION_FILE]);
            echo dbFillSource::execute([Newalch::SOURCE_DEFINITION_FILE]);
            echo dbFillSource::execute([Wikidata::SOURCE_DEFINITION_FILE]);
            echo dbFillSource::execute([G5::SOURCE_DEFINITION_FILE]);
            echo dbInitOccu1::execute();
            
            // Done here to build associations between issues and wiki projects.
            echo wikiAddAllProjects::execute(['small']);
            
            foreach($filesGauqA as $datafile){
                echo Atmp2db::execute([$datafile, 'tmp2db', 'small']);
//break;
            }
//exit;
            echo dbInitTweaks::execute(['A1.yml']);
            echo A6occu::execute(['A6','A6occu']);
            
            echo D6tmp2db::execute(['D6', 'tmp2db', 'small']);
            echo dbInitTweaks::execute(['D6.yml']);
            
            echo D10tmp2db::execute(['D10', 'tmp2db', 'small']);
            
            echo E1E3tmp2db::execute(['E1', 'tmp2db', 'small']);
            
            echo E1E3tmp2db::execute(['E3', 'tmp2db', 'small']);
            
            echo M5medicsTmp2db::execute(['small']);
            
            echo M1writersTmp2db::execute(['small']);
            
            echo M1writers100tmp2db::execute(['small']);
            
            echo csiIrvingTmp2db::execute(['small']);
            
            echo M3womenTmp2db::execute(['small']);
            echo dbInitTweaks::execute(['muller-234-women.yml']);
            
            echo M2menTmp2db::execute(['small']);
            echo dbInitTweaks::execute(['muller-612-men.yml']);
            
            echo ErteSportTmp2db::execute(['small']);
            echo dbInitTweaks::execute(['ertel-sport.yml']);
            
            echo CFEPPTmp2db::execute(['small']);
            
            echo CParaGroup::execute([]);
            
            // g55 raw2tmp done here because it needs db to compute cache
            echo g55Gqid::execute(['g55', 'gqid', 'cache']);
            foreach($g55Groups as $groupKey){
                echo g55Raw2tmp::execute(['g55', 'raw2tmp', $groupKey]);
                echo g55Gqid::execute(['g55', 'gqid', 'update', $groupKey]);
                if($groupKey == '09-349-scientists'){
                    echo g55Special::execute(['g55', 'special', 'complete09']);
                }
            }
            //
            foreach($g55Groups as $groupKey){
                echo g55Tmp2db::execute(['g55', 'tmp2db', $groupKey]);
            }
            
            echo dbInitOccu2::execute();
        }
        
        if($param == 'wiki' || $param == 'all'){
            echo "***************************\n";
            echo "***    Add wiki data    ***\n";
            echo "***************************\n";
            echo wikiAddAllBCs::execute(['small']);
        }
        
        if($param == 'finalize' || $param == 'all'){
            echo "***************************\n";
            echo "***  Finalize database  ***\n";
            echo "***************************\n";
            echo DBInitStats::execute(['small']);
//            echo search::execute();
        }
        
        if($param == 'export' || $param == 'all'){
            echo "***************************\n";
            echo "***    Export groups    ***\n";
            echo "***************************\n";
            foreach($filesGauqA as $datafile){
                echo curaExport::execute([$datafile, 'export', 'sep=true']);
            }
            echo curaExport::execute(['D6', 'export', 'sep=true']);
            echo curaExport::execute(['D10', 'export', 'sep=true']);
            echo curaExport::execute(['E1', 'export', 'sep=true']);
            echo curaExport::execute(['E3', 'export', 'sep=true']);
            echo M1WritersExport::execute(['sep=true']);
            echo M1Writers100export::execute(['sep=true']);
            echo M2MenExport::execute(['sep=true']);
            echo M3WomenExport::execute(['sep=true']);
            echo M5MedicsExport::execute(['sep=true']);
            echo csiIrvingExport::execute(['sep=true']);
            echo CFEPPExport::execute(['sep=true,group=1120']);
            echo CFEPPExport::execute(['sep=true,group=1066']);
            echo ErtelExport::execute(['sep=true']);
            echo skepticsExport::execute(['sep=true']);
            echo allOccusExport::execute([]);
            echo allPersonsExport::execute(['sep=true']);
            echo allPersonsExport::execute(['sep=true,what=time']);
            //echo allPersonsExport::execute(['sep=true;what=notime']);
            echo pgdumpExport::execute([]);
        }
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 2);
        $dt_min = round($dt / 60, 2);
        
        echo "====== " . date('c') . " - Execution of all commands in $dt s ($dt_min min) ======\n";
        return '';
    }
    
} // end class
