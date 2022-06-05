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
        
        
        // sources corresponding to this group - insert if does not already exist
        
        // TODO
        // Normally, one source definition file should exist in data/db/source/gauq/g55 for each raw file.
        // temporary : groups are related to g55 instead of being related to a precise source.
        
        $g55Source = Source::createFromSlug(G55::SOURCE_SLUG); // DB
        if(is_null($g55Source)){
            $g55Source = new Source(G55::SOURCE_DEFINITION_FILE);
            $g55Source->insert(); // DB
            $report .= "Inserted source " . $g55Source->data['slug'] . "\n";
        }
        
        // groups
        $groupSlug = G55::GROUPS[$groupKey]['slug'];
        $g = Group::createFromSlug($slug);
        if(is_null($g)){
            // group definition file name is built from its slug
            $defFile = 'gauq' . DS . 'g55' . G55::GROUPS[$groupKey]['slug'] . '.yml';
            if(!is_file($defFile)){
                throw new \Exception("Unable to read group definition file: $defFile");
            }
            $g = Group::createFromDefinitionFile($defFile);
            $g->data['id'] = $g->insert(); // DB
            $report .= "Inserted group " . $g->data['slug'] . "\n";
        }
        else{
            $g->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $nInsert = 0;
        $nUpdate = 0;
exit;
        
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = G55::loadTmpFile($groupKey);
        $linesRaw = G55::loadTmpRawFile($groupKey);
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            $G55ID = G55::g55Id($groupKey, $i);
            $GQID = $line['GQID']; // eventually computed by command addGqid
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
//                $p->addIdInSource(TODO group source slug, $NUM);
                $p->addPartialId(G55::SOURCE_SLUG, $G55ID);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat some fields to include in $history
                $new['ids-in-sources'] = [Final3::SOURCE_SLUG => $CFID];
                $p->addHistory(
                    command: $cmdSignature,
                    sourceSlug: Final3::SOURCE_SLUG,
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
            }
            else{
                // Person already in Gauquelin
            }
        }
        $t2 = microtime(true);
        $g->insertMembers(); // DB
        $dt = round($t2 - $t1, 5);
        $report .= "$nInsert persons inserted, $nUpdate updated - $nDiffDates different dates ($dt s)\n";
        return $report;
    }
    
} // end class
