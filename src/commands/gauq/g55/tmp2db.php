<?php
/********************************************************************************
    Loads files from data/tmp/gauq/g55 in database.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-06-03 23:52:31+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\g55;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\gauq\Gauquelin;
use g5\commands\gauq\LERRCP;

class tmp2db implements Command {
    
    /**
        @param  $params Array containing 3 elements :
                        - the string "g55" (useless here, used by GauqCommand).
                        - the string "raw2tmp" (useless here, used by GauqCommand).
                        - a string identifying what is processed (ex : '570SPO').
                          Corresponds to a key of G55::GROUPS array
    **/
    public static function execute($params=[]): string {
        
        $cmdSignature = 'gauq g55 tmp2db';
        $report = "--- $cmdSignature ---\n";
        
        $possibleParams = G55::getPossibleGroupKeys();
        $msg = "Usage : php run-g5.php $cmdSignature <group>\nPossible values for <group>: \n  - " . implode("\n  - ", $possibleParams) . "\n";
        
        if(count($params) != 3){
            return "INVALID CALL: - this command needs exactly one parameter.\n$msg";
        }
        $groupKey = $params[2];
        if(!in_array($groupKey, $possibleParams)){
            return "INVALID PARAMETER: $groupKey\n$msg";
        }
        
        $tmpfile = G55::tmpFilename($groupKey);
        if(!is_file($tmpfile)){
            return "UNABLE TO PROCESS GROUP: missing temporary file $tmpfile\n";
        }
        
        // particular case
        $isPriests = ($groupKey == '513PRE' || $groupKey == '369PRE');
        
        // Sources related to this group - insert if does not already exist
        $g55Source = Source::createFromSlug(G55::SOURCE_SLUG); // DB
        if(is_null($g55Source)){
            $g55Source = new Source(G55::SOURCE_DEFINITION_FILE);
            $g55Source->insert(); // DB
            $report .= "Inserted source " . $g55Source->data['slug'] . "\n";
        }
        // Precise source of the group
        // Convention: source slug = group slug
        $source = Source::createFromSlug(G55::GROUPS[$groupKey]['slug']); // DB
        if(is_null($source)){
            // source definition file name is built from its slug
            $defFile = 'gauq' . DS . 'g55' . DS . G55::GROUPS[$groupKey]['slug'] . '.yml';
            $source = new Source($defFile);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $groupSlug = G55::GROUPS[$groupKey]['slug'];
        $g = Group::createFromSlug($groupSlug);
        if(is_null($g)){
            // group definition file name is built from its slug
            $defFile = 'gauq' . DS . 'g55' . DS . $groupSlug . '.yml';
            $g = Group::createFromDefinitionFile($defFile);
            $g->data['id'] = $g->insert(); // DB
            $report .= "Inserted group " . $g->data['slug'] . "\n";
        }
        else{
            $g->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        // particular case for priests
        // can be executed once for 369PRE and once for 513PRE
        // several executions will give incoherent results (deleteMembers() not done)
        if($isPriests){
            $groupSlug2 = 'g55-882-priests';
            $g2 = Group::createFromSlug($groupSlug2);
            if(is_null($g2)){
                // group definition file name is built from its slug
                $defFile = 'gauq' . DS . 'g55' . DS . $groupSlug2 . '.yml';
                $g2 = Group::createFromDefinitionFile($defFile);
                $g2->data['id'] = $g2->insert(); // DB
                $report .= "Inserted group " . $g2->data['slug'] . "\n";
            }
        }
        
        $nInsert = 0;
        $nUpdate = 0;
        
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = G55::loadTmpFile($groupKey);
        $linesRaw = G55::loadTmpRawFile($groupKey);
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $NUM = $i + 1;
            $lineRaw = $linesRaw[$i];
            $G55ID = G55::g55Id($groupKey, $i);
            $GQID = $line['GQID']; // eventually computed by command gqid
            if($GQID == ''){;
                // Person not already in g5 db
                $p = new Person();
                $new = [];
                $new['trust'] = Gauquelin::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['name']['nobility'] = $line['NOB'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = 'FR';
                $new['occus'] = [ $line['OCCU'] ];
                //
                $p->addIdInSource($source->data['slug'], (string)$NUM);
                $p->addPartialId(G55::SOURCE_SLUG, $G55ID);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat some fields to include in $history
                $new['ids-in-sources'] = [$source->data['slug'] => $NUM];
                $p->addHistory(
                    command: $cmdSignature . ' ' . $groupKey,
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
            }
            else{
                // Person already in Gauquelin
                $p = Person::createFromPartialId(LERRCP::SOURCE_SLUG, $GQID); // DB
                $p->addOccus([ $line['OCCU'] ]);
                $p->addIdInSource($source->data['slug'], (string)$NUM);
                $p->addPartialId(G55::SOURCE_SLUG, $G55ID);
                // add an issue if G55 and LERRCP dates differ
                // note: as matching is done by date (see cmomand gqid check),
                // current check concerns birth hours
                if($p->data['birth']['date'] != ''){
                    if($line['DATE'] != substr($p->data['birth']['date'], 0, 16)){
                        $issue = "Check birth date because CFEPP and g55 birth dates differ\n"
                               . "<br>G55: {$line['DATE']}\n"
                               . "<br>CFEPP: {$p->data['birth']['date']}\n";
//echo "$issue\n";
                        $p->addIssue($issue);
                    }
                }
                $p->addHistory(
                    command: $cmdSignature,
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw,
                );
                $nUpdate++;
                $p->update(); // DB
            }
            $g->addMember($p->data['id']);
            if($isPriests){
                $g2->addMember($p->data['id']);
            }
        }
        $g->insertMembers(); // DB
        if($isPriests){
            $g2->insertMembers(); // DB
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 5);
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        return $report;
    }
    
} // end class
