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
                        - a string identifying what is processed (ex : '01-576-physicians').
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
        
        // Source related to this group - insert if does not already exist
        $g55Source = Source::createFromSlug(G55::SOURCE_SLUG); // DB
        if(is_null($g55Source)){
            $g55Source = new Source(G55::SOURCE_DEFINITION_FILE);
            $g55Source->insert(); // DB
            $report .= "Inserted source " . $g55Source->data['slug'] . "\n";
        }
        
        // group
        $groupSlug = G55::groupKey2slug($groupKey);
        $g = Group::createFromSlug($groupSlug);
        if(is_null($g)){
            $defFile = 'gauq' . DS . 'g55' . DS . $groupKey . '.yml';
            $g = Group::createFromDefinitionFile($defFile);
            $g->data['id'] = $g->insert(); // DB
            $report .= "Inserted group " . $g->data['slug'] . "\n";
        }
        else{
            $g->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $nInsert = 0;
        $nUpdate = 0;
        
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = G55::loadTmpFile($groupKey);
        $linesRaw = G55::loadTmpRawFile($groupKey);
        $N = count($lines);
        $NFixedNames = 0; // number of names like 'Gauquelin-A2-217' replaced by real name
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $NUM = $i + 1;
            $lineRaw = $linesRaw[$i];
            $G55ID = G55::g55Id($groupKey, $i+1);
            $GQID = $line['GQID']; // eventually computed by command gqid
            if($GQID == ''){;
                // Person not already in g5 db - insert
                $p = new Person();
                $new = [];
                $new['trust'] = Gauquelin::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['name']['nobility'] = $line['NOB'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c1'] = $line['C1'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = 'FR';
                $new['occus'] = [ $line['OCCU'] ];
                //
                $p->addIdInSource($g55Source->data['slug'], (string)$NUM);
                $p->addPartialId(G55::SOURCE_SLUG, $G55ID);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat some fields to include in $history
                $new['ids-in-sources'] = [G55::SOURCE_SLUG => $NUM];
                $p->addHistory(
                    command:    $cmdSignature . ' ' . $groupKey,
                    sourceSlug: $g55Source->data['slug'],
                    newdata:    $new,
                    rawdata:    $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
            }
            else{
                // Person already in Gauquelin - update
                $p = Person::createFromPartialId(LERRCP::SOURCE_SLUG, $GQID); // DB
                $p->addOccus([ $line['OCCU'] ]);
                $p->addIdInSource(G55::SOURCE_SLUG, (string)$NUM);
                $p->addPartialId(G55::SOURCE_SLUG, $G55ID);
                // add an issue if G55 and LERRCP dates differ
                // note: as matching is done by date (see command gqid check),
                // current check concerns birth hours
                if($p->data['birth']['date'] != ''){
                    if($line['DATE'] != substr($p->data['birth']['date'], 0, 16)){
                        $issue = "Check birth date because CFEPP and g55 birth dates differ\n"
                               . "<br>G55: {$line['DATE']}\n"
                               . "<br>CFEPP: {$p->data['birth']['date']}\n";
                        $p->addIssue($issue);
                    }
                }
                $new = [
                    'ids-in-source' => [G55::SOURCE_SLUG => (string)$NUM],
                    'partial-ids' => [G55::SOURCE_SLUG => $G55ID],
                ];
                if(!$p->data['birth']['place']['geoid']){
                    // g55 place names are generally better than cura
                    $new['birth']['place']['name'] = $line['PLACE'];
                }
                if(strpos($p->data['slug'], 'gauquelin-') === 0){
                    $new['name']['family'] = $line['FNAME'];
                    $new['name']['given'] = $line['GNAME'];
                    $new['name']['nobility'] = $line['NOB'];
                    $NFixedNames++;
                }
                $p->addHistory(
                    command:    $cmdSignature . ' ' . $groupKey,
                    sourceSlug: $g55Source->data['slug'],
                    newdata:    $new,
                    rawdata:    $lineRaw,
                );
                $nUpdate++;
                $p->updateFields($new);
                $p->update(); // DB
            }
            $g->addMember($p->data['id']);
        }
        $g->insertMembers(); // DB
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 5);
        $strFixed = '';
        if($NFixedNames != 0){
            $strFixed = ", fixed $NFixedNames names";
        }
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        return $report;
    }
    
} // end class
