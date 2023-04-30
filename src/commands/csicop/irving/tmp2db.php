<?php
/********************************************************************************
    Loads files data/tmp/csicop/irving/408-csicop-irving.csv and 408-csicop-irving-raw.csv in database.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-09-02 00:36:52+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\Newalch;
use g5\commands\gauq\LERRCP;
use g5\commands\csicop\CSICOP;
use g5\commands\csicop\si42\SI42;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of date differences from D10',
    ];
    
    /**
        @param  $params Empty array
    **/
    public static function execute($params=[]): string {
        if(count($params) > 1){
            return "USELESS PARAMETER : " . $params[1] . "\n";
        }
        $msg = '';
        foreach(self::REPORT_TYPE as $k => $v){
            $msg .= "  $k : $v\n";
        }
        if(count($params) != 1){
            return "WRONG USAGE - This command needs a parameter to specify which output it displays. Can be :\n" . $msg;
        }
        $reportType = $params[0];
        if(!in_array($reportType, array_keys(self::REPORT_TYPE))){
            return "INVALID PARAMETER : $reportType - Possible values :\n" . $msg;
        }

        $report = "--- csicop irving tmp2db ---\n";     
        
        if($reportType == 'full'){
            $datesReport = '';
        }
        
        // source related to CSICOP - insert if does not already exist
        $csicopSource = Source::createFromSlug(CSICOP::SOURCE_SLUG); // DB
        if(is_null($csicopSource)){
            $csicopSource = new Source(CSICOP::SOURCE_DEFINITION_FILE);
            $csicopSource->insert(); // DB
            $report .= "Inserted source " . $csicopSource->data['slug'] . "\n";
        }
        
        // source related to si42 - insert if does not already exist
        $si42Source = Source::createFromSlug(SI42::SOURCE_SLUG); // DB
        if(is_null($si42Source)){
            $si42Source = new Source(SI42::SOURCE_DEFINITION_FILE);
            $si42Source->insert(); // DB
            $report .= "Inserted source " . $si42Source->data['slug'] . "\n";
        }
        
        // source of rawlins-ertel-irving.csv - insert if does not already exist
        $source = Source::createFromSlug(Irving::LIST_SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(Irving::LIST_SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // groups
        $g = Group::createFromSlug(CSICOP::GROUP_SLUG); // DB
        if(is_null($g)){
            $g = CSICOP::getGroup();
            $g->data['id'] = $g->insert(); // DB
            $report .= "Inserted group " . $g->data['slug'] . "\n";
        }
        else{
            $g->deleteMembers(); // only deletes asssociations between group and members
        }
        
        $g1 = Group::createFromSlug(CSICOP::GROUP1_SLUG); // DB
        if(is_null($g1)){
            $g1 = CSICOP::getGroup_batch1();
            $g1->data['id'] = $g1->insert(); // DB
            $report .= "Inserted group " . $g1->data['slug'] . "\n";
        }
        else{
            $g1->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $g2 = Group::createFromSlug(CSICOP::GROUP2_SLUG); // DB
        if(is_null($g2)){
            $g2 = CSICOP::getGroup_batch2();
            $g2->data['id'] = $g2->insert();
            $report .= "Inserted group " . $g2->data['slug'] . "\n";
        }
        else{
            $g2->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $g3 = Group::createFromSlug(CSICOP::GROUP3_SLUG); // DB
        if(is_null($g3)){
            $g3 = CSICOP::getGroup_batch3();
            $g3->data['id'] = $g3->insert(); // DB
            $report .= "Inserted group " . $g3->data['slug'] . "\n";
        }
        else{
            $g3->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $nInsert = 0;
        $nUpdate = 0;
        $nDiffDates = 0;
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = Irving::loadTmpFile();
        $linesRaw = Irving::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            if($line['GQID'] == ''){
                // Person not in Gauquelin data
                $p = new Person();
                $new = [];
                $new['trust'] = Irving::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['date-ut'] = $line['DATE-UT'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = (float)$line['LG'];
                $new['birth']['place']['lat'] = (float)$line['LAT'];
                //
                $p->addOccus([$line['SPORT']]); // table person_groop handled by command db/init/occu2 - Group::storePersonInGroup() not called here
                $p->addIdInSource($source->data['slug'], $line['CSID']);
                $p->addPartialId($csicopSource->data['slug'], CSICOP::csicopId($line['CSID']));
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['ids-in-sources'] = [ $source->data['slug'] => $line['CSID'] ];
                $new['occus'] = [[$line['SPORT']]];
                $p->addHistory(
                    command: 'csicop irving tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
            }
            else{
                // Person already in D10
                $new = [];
                $new['notes'] = [];
                [$curaSourceSlug, $NUM] = Irving::gqid2curaSourceId($line['GQID']);
                $curaFile = strtoupper($curaSourceSlug);
                $gqId = LERRCP::gauquelinId($curaFile, $NUM);
                $p = Person::createFromSourceId($curaSourceSlug, $NUM); // DB
                if(is_null($p)){
                    throw new \Exception("$gqId : try to update an unexisting person");
                }
                // if Cura and csicop have different birth day
                $csiday = substr($line['DATE'], 0, 10);
                $curaday = substr($p->data['birth']['date'], 0, 10);
                if($csiday != $curaday){
                    $nDiffDates++;
                    $new['to-check'] = true;
                    $new['notes'][] = "CHECK birth day : $gqId $curaday / CSID {$line['CSID']} $csiday";
                    if($reportType == 'full'){
                        $datesReport .= "\nCura $gqId\t $curaday {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $datesReport .= "Irving CSID {$line['CSID']}\t $csiday {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                $p->addOccus([$line['SPORT']]); // table person_groop handled by command db/init/occu2 - Group::storePersonInGroup() not called here
                $p->addIdInSource($source->data['slug'], $line['CSID']);
                $p->addPartialId($csicopSource->data['slug'], CSICOP::csicopId($line['CSID']));
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['ids-in-sources'] = [ $source->data['slug'] => $line['CSID'] ];
                $new['occus'] = [[$line['SPORT']]];
                $p->addHistory(
                    command: 'csicop irving tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nUpdate++;
                $p->update(); // DB
            }
            $g->addMember($p->data['id']);
            if($line['CANVAS'] == 1){
                $g1->addMember($p->data['id']);
            }
            else if($line['CANVAS'] == 2){
                $g2->addMember($p->data['id']);
            }
            else if($line['CANVAS'] == 3){
                $g3->addMember($p->data['id']);
            }
        }
        $t2 = microtime(true);
        $g->insertMembers(); // DB
        $g1->insertMembers(); // DB
        $g2->insertMembers(); // DB
        $g3->insertMembers(); // DB
        $dt = round($t2 - $t1, 5);
        if($reportType == 'full'){
            $report .= "\n=== Dates different from D10 ===\n" . $datesReport;
            $report .= "============\n";
        }
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        $report .= "$nDiffDates dates differ from D10\n";
        return $report;
    }
        
} // end class    
