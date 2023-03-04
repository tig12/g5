<?php
/********************************************************************************
    Loads files data/tmp/muller/5-medics/muller5-1083-medics.csv and muller5-1083-medics-raw.csv in database.
    Affects records imported in A2 and E1

    NOTE: This code cannot be executed several times (won't update the records if already in database)
        To re-execute it (eg for debug purposes), you must rebuild the database from scratch (at least A2 and E1)
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-08-20 10:46:02+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller\m5medics;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\Newalch;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\Muller;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of names and dates restoration on A2 or E1',
    ];
    
    /**
        @param  $params Array containing 1 element : the type of report ; see REPORT_TYPE
    **/
    public static function execute($params=[]): string {
        if(count($params) > 1){
            return "USELESS PARAMETER : " . $params[1] . "\n";
        }
        $msg = '';
        foreach(self::REPORT_TYPE as $k => $v){
            $msg .= "  '$k' : $v\n";
        }
        if(count($params) != 1){
            return "WRONG USAGE - This command needs a parameter to specify which output it displays. Can be :\n" . $msg;
        }
        $reportType = $params[0];
        if(!in_array($reportType, array_keys(self::REPORT_TYPE))){
            return "INVALID PARAMETER : $reportType - Possible values :\n" . $msg;
        }
        
        $report = "--- muller m5medics tmp2db ---\n";
        
        if($reportType == 'full'){
            $namesReport = '';
            $datesReport = '';
        }
        
        // source corresponding to Müller's Astro-Forschungs-Daten - insert if does not already exist
        $afdSource = Source::createFromSlug(Muller::SOURCE_SLUG); // DB
        if(is_null($afdSource)){
            $afdSource = new Source(Muller::SOURCE_DEFINITION_FILE);
            $afdSource->insert(); // DB
            $report .= "Inserted source " . $afdSource->data['slug'] . "\n";
        }
        
        // source corresponding to newalchemypress - insert if does not already exist
        $newalchSource = Source::createFromSlug(Newalch::SOURCE_SLUG); // DB
        if(is_null($newalchSource)){
            $newalchSource = new Source(Newalch::SOURCE_DEFINITION_FILE);
            $newalchSource->insert(); // DB
            $report .= "Inserted source " . $newalchSource->data['slug'] . "\n";
        }
        
        // source of Müller's booklet 5 physicians - insert if does not already exist
        $bookletSource = Source::createFromSlug(M5medics::BOOKLET_SOURCE_SLUG); // DB
        if(is_null($bookletSource)){
            $bookletSource = new Source(M5medics::BOOKLET_SOURCE_DEFINITION_FILE);
            $bookletSource->insert(); // DB
            $report .= "Inserted source " . $bookletSource->data['slug'] . "\n";
        }
        
        // source of 5a_muller_medics.txt - insert if does not already exist
        $source = Source::createFromSlug(M5medics::LIST_SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(M5medics::LIST_SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::createFromSlug(M5medics::GROUP_SLUG);
        if(is_null($g)){
            $g = M5medics::getGroup();
            $g->data['id'] = $g->insert(); // DB
            $report .= "Inserted group " . $g->data['slug'] . "\n";
        }
        else{
            $g->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $nInsert = 0;
        $nUpdate = 0;
        $nRestoredNames = 0;
        $nDiffDates = 0;
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = M5medics::loadTmpFile();
        $linesRaw = M5medics::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        $newOccus = ['physician'];
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            $mullerId = Muller::mullerId($source->data['slug'], $line['NR']);
            if($line['GNR'] == ''){
                // Person not in Gauquelin data
                $p = new Person();
                $new = [];
                $new['trust'] = Newalch::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['name']['nobl'] = $line['NOB'];
                // Müller name considered as = to full name copied from birth certificate
                $new['name']['official']['given'] = $line['GNAME'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = 'FR';
                $new['birth']['place']['lg'] = (float)$line['LG'];
                $new['birth']['place']['lat'] = (float)$line['LAT'];
                //
                $p->addOccus($newOccus);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->addPartialId(Muller::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['ids-in-sources'] = [ $source->data['slug'] => $line['NR'] ];
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
                $p = Person::createFromSourceId($gauqSourceSlug, $NUM); // DB
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
                    $p->addIssue_old($issue1);
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
                        $p->addIssue_old($issue2);
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
                    $p->addIssue_old($issue3);
                }
                //
                $p->addOccus($newOccus);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->addPartialId(Muller::SOURCE_SLUG, $mullerId);
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

