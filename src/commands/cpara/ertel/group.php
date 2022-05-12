<?php
/********************************************************************************
    Creates a group containing the 535 athletes used in the Comité Para test.
    
    Comité Para data are included in g5 using Ertel file.
    This test has no raw2tmp or tmp2db step.
    The only specific thing is the creation of the group
    
    @pre This command must be executed after tmp2db step of Ertel Sport
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-05-12 00:21:26+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cpara\ertel;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Group as ModelGroup;
use g5\model\Person;
use g5\commands\cpara\CPara;
use g5\commands\ertel\Ertel;
use g5\commands\ertel\sport\ErtelSport;

class group implements Command {
    
    /**
        @param  $params Empty array
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $cmdSignature = 'cpara ertel group';
        $report = "--- $cmdSignature ---\n";
        
        // source'cpara' already exist, created in Ertel Sport tmp2db
        
        // groups
        $g = ModelGroup::createFromSlug(CPara::GROUP_SLUG);
        if(is_null($g)){
            $g = CPara::getGroup();
            $g->data['id'] = $g->insert(); // DB
            $report .= "Inserted group " . $g->data['slug'] . "\n";
        }
        else{
            $g->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $t1 = microtime(true);
        
        // Select in db persons containing an Ertel id
        $persons = Person::createArrayFromPartialId(Ertel::SOURCE_SLUG);
        $N = 0;
        foreach($persons as $p){
            $his = $p->historyFromSource(ErtelSport::SOURCE_SLUG);
            if($his['raw']['PARA_NR'] == ''){
                continue;
            }
            $N++;
            $CPID = CPara::cparaId($his['raw']['PARA_NR']);
            $p->addPartialId(CPara::SOURCE_SLUG, $CPID);
            $p-> update();
//echo $his['raw']['PARA_NR'] . "\n";
//echo "\n<pre>"; print_r($his); echo "</pre>\n"; exit;
//echo "\n<pre>"; print_r($p); echo "</pre>\n"; exit;
        }
//exit;
//        $g->insertMembers(); // DB
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 5);
        $report .= "Added $N person to group " . CPara::GROUP_SLUG . " ($dt s)\n";
        
        return $report;
    }
    
} // end class
