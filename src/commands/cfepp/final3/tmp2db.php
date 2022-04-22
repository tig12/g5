<?php
/********************************************************************************
    Loads files data/tmp/cfepp/cfepp-1120-nienhuys.csv and data/tmp/cfepp/cfepp-1120-nienhuys-raw.csv in database.

    NOTE: This code cannot be executed several times (won't update the records if already in database)
        To re-execute it (eg for debug purposes), you must rebuild the databse from scratch (at least A2 and E1)
    
    @pre This command must be executed after tmp2db of LERRCP A1 and Ertel Sport.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-04-22 17:12:58+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\cfepp\final3\Final3;
use g5\commands\gauq\LERRCP;
use g5\commands\cfepp\CFEPP;
use g5\commands\cpara\CPara;

class tmp2db implements Command {
    
    /**
        @param  $params Empty array
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }
        
        $report = "--- cfepp final3 tmp2db ---\n";
        
        // sources corresponding to this test - insert if does not already exist
        
        // sources 'cfepp' and 'cpara' already exist, created in Ertel Sport tmp2db
        
        $final3Source = Source::getBySlug(Final3::SOURCE_SLUG); // DB
        if(is_null($final3Source)){
            $final3Source = new Source(Final3::SOURCE_DEFINITION_FILE);
            $final3Source->insert(); // DB
            $report .= "Inserted source " . $final3Source->data['slug'] . "\n";
        }
        
        $cfeppBookletSource = Source::getBySlug(CFEPP::BOOKLET_SOURCE_SLUG); // DB
        if(is_null($cfeppBookletSource)){
            $cfeppBookletSource = new Source(CFEPP::BOOKLET_SOURCE_DEFINITION_FILE);
            $cfeppBookletSource->insert(); // DB
            $report .= "Inserted source " . $cfeppBookletSource->data['slug'] . "\n";
        }
        
        $nienhuysSource = Source::getBySlug(CFEPP::NIENHUYS_SOURCE_SLUG); // DB
        if(is_null($nienhuysSource)){
            $nienhuysSource = new Source(CFEPP::NIENHUYS_SOURCE_DEFINITION_FILE);
            $nienhuysSource->insert(); // DB
            $report .= "Inserted source " . $nienhuysSource->data['slug'] . "\n";
        }
        
        $cfeppSource = Source::getBySlug(CFEPP::SOURCE_SLUG); // DB
        
        $cparaSource = Source::getBySlug(CPara::SOURCE_SLUG); // DB
        
        // groups
        
        $g1120 = Group::createFromSlug(Final3::GROUP_1120_SLUG);
        if(is_null($g)){
            $g1120 = Final3::getGroup1120();
            $g1120->data['id'] = $g1120->insert(); // DB
            $report .= "Inserted group " . $g1120->data['slug'] . "\n";
        }
        else{
            $g1120->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $g1066 = Group::createFromSlug(Final3::GROUP_1120_SLUG);
        if(is_null($g)){
            $g1066 = Final3::getGroup1120();
            $g1066->data['id'] = $g1066->insert(); // DB
            $report .= "Inserted group " . $g1066->data['slug'] . "\n";
        }
        else{
            $g1066->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $nInsert = 0;
        $nUpdate = 0;
//        $nRestoredNames = 0;
//        $nDiffDates = 0;
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = Final3::loadTmpFile();
        $linesRaw = Final3::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];                                                                        
            $lineRaw = $linesRaw[$i];
            $cfid = CFEPP::cfeppId($line['CFID']);
            $gqid = $line['GQID'];
            $erid = $line['ERID'];
            $cpid = $line['CPID'];
die("\n<br>die here " . __FILE__ . ' - line ' . __LINE__ . "\n");
            if($gqid == ''){
                // Person not in Gauquelin data
                $p = new Person();
                $new = [];
                $new['trust'] = Final3::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['c2'] = $line['C3'];
                $new['birth']['place']['cy'] = 'FR';
                $new['birth']['place']['lg'] = (float)$line['LG'];
                $new['birth']['place']['lat'] = (float)$line['LAT'];
                //
                $p->addOccus([$line['OCCU']]);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->addIdPartial(Muller::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['ids-in-sources'] = [
                    $source->data['slug'] => $line['NR']
                ];
                $new['occus'] = $newOccus;
                $p->addHistory(
                    command: 'muller m5medics tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
            }
            else{
                // Person already in A2 or E1
                $new = [];
                $new['issues'] = [];
                $issue1 = $issue2 = $issue3 = '';
                [$gauqSourceSlug, $NUM] = M5medics::gnr2LERRCPSourceId($line['GNR']);
                $gauqFile = strtoupper($gauqSourceSlug);
                $gauqId = LERRCP::gauquelinId($gauqFile, $NUM);
                $p = Person::sourceId2person($gauqSourceSlug, $NUM); // DB
                if(is_null($p)){
                    throw new \Exception("$gauqId : try to update an unexisting person");
                }
                if($p->data['name']['family'] == "Gauquelin-$gauqId"){
                    $nRestoredNames++;
                    if($reportType == 'full'){
                        $namesReport .= "\nCura NUM $gauqId\t {$p->data['name']['family']}\n";
                        $namesReport .= "Müller NR {$line['NR']}\t {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                // if Cura and Müller have different birth day
                $mulDay = substr($line['DATE'], 0, 10);
                // from A2, stored in field 'date-ut' ; from E1, stored in field 'date'
                if(isset($p->data['ids-in-sources']['a2'])){
                    $gauqDay = substr($p->data['birth']['date-ut'], 0, 10);
                }
                else{ // E1
                    $gauqDay = substr($p->data['birth']['date'], 0, 10);
                }
                if($mulDay != $gauqDay){
                    $nDiffDates++;
                    $issue1 = "Check birth date because Müller and Gauquelin birth days differ\n"
                           . "<br>$gauqDay for Gauquelin $gauqId\n"
                           . "<br>$mulDay for Müller $mullerId\n";
                    $p->addIssue($issue1);
                    if($reportType == 'full'){
                        $datesReport .= "\nCura $gauqId\t $gauqDay {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $datesReport .= "Müller NR {$line['NR']}\t $mulDay {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                // E1 same day as Müller 1083 => check time
                if($mulDay == $gauqDay && isset($p->data['ids-in-sources']['e1'])){
                    $gauqHour = substr($p->data['birth']['date'], 11);
                    $mulHour = substr($line['DATE'], 11);
                    if($gauqHour != $mulHour){
                        $issue2 = "Check birth date because Müller and Gauquelin birth hours differ"
                               . "\n<br>$gauqHour for Gauquelin $gauqId"
                               . "\n<br>$mulHour for Müller $mullerId\n";
                        $p->addIssue($issue2);
                    }
                }
                // update fields that are more precise in muller1083
                $new['birth']['date'] = $line['DATE']; // Cura contains only date-ut
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['name']['nobl'] = $line['NOB'];
                $new['name']['family'] = $line['FNAME'];
                if($p->data['name']['given'] == ''){
                    // happens with names like Gauquelin-A1-258
                    $new['name']['given'] = $line['GNAME'];
                }
                // Müller name considered as = to full name copied from birth certificate
                // (Gauquelin name considered as current name)
                $new['name']['official']['given'] = $line['GNAME'];
                //
                if($line['PLACE'] == 'Paris'){
                    $issue3 = 'Birth date needs to be checked because Arno Müller coulndn\'t verify births in Paris';
                    $p->addIssue($issue3);
                }
                //
                $p->addOccus($newOccus);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->addIdPartial(Muller::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids-in-sources'] = [ $source->data['slug'] => $line['NR'] ];
                $new['occus'] = $newOccus;
                if($issue1 != ''){ $new['issues'][] = $issue1; }
                if($issue2 != ''){ $new['issues'][] = $issue2; }
                if($issue3 != ''){ $new['issues'][] = $issue3; }
                if(count($new['issues']) == 0){ unset($new['issues']); }
                $p->addHistory(
                    command: 'muller m5medics tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nUpdate++;
                $p->update(); // DB
            }
            $g->addMember($p->data['id']);
        }
        $t2 = microtime(true);
        $g->insertMembers(); // DB
        $dt = round($t2 - $t1, 5);
        if($reportType == 'full'){
            $report .= "=== Names fixed ===\n" . $namesReport;
            $report .= "\n=== Dates fixed ===\n" . $datesReport;
            $report .= "============\n";
        }
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        $report .= "$nDiffDates dates differ from A2 and E1";
        $report .= " - $nRestoredNames names restored in A2\n";
        return $report;
    }
        
} // end class    

