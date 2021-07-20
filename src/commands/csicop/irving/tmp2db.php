<?php
/********************************************************************************
    Loads files data/tmp/newalch/muller-402-it-writers.csv and muller-402-it-writers-raw.csv in database.
    Affects records imported from A1
    
    @license    GPL
    @history    2020-09-02 00:36:52+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\newalch\Newalch;
use g5\commands\cura\Cura;
use g5\commands\csicop\CSICOP;
use g5\commands\csicop\si42\SI42;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of date differences from D10',
    ];
    
    // *****************************************
    // Implementation of Command
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
        $csicopSource = Source::getBySlug(CSICOP::SOURCE_SLUG);
        if(is_null($csicopSource)){
            $csicopSource = new Source(CSICOP::SOURCE_DEFINITION_FILE);
            $csicopSource->insert();
            $report .= "Inserted source " . $csicopSource->data['slug'] . "\n";
        }
        
        // source related to si42 - insert if does not already exist
        $si42Source = Source::getBySlug(SI42::SOURCE_SLUG);
        if(is_null($si42Source)){
            $si42Source = new Source(SI42::SOURCE_DEFINITION_FILE);
            $si42Source->insert();
            $report .= "Inserted source " . $si42Source->data['slug'] . "\n";
        }
        
        // source of rawlins-ertel-irving.csv - insert if does not already exist
        $source = Source::getBySlug(Irving::LIST_SOURCE_SLUG);
        if(is_null($source)){
            $source = new Source(Irving::LIST_SOURCE_DEFINITION_FILE);
            $source->insert();
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // groups
        $g = Group::getBySlug(CSICOP::GROUP_SLUG);
        if(is_null($g)){
            $g = new Group();
            $g->data['slug'] = CSICOP::GROUP_SLUG;
            $g->data['sources'][] = $source->data['slug'];
            $g->data['name'] = "CSICOP";
            $g->data['description'] = "CSICOP";
            $g->data['id'] = $g->insert();
        }
        else{
            $g->deleteMembers(); // only deletes asssociations between group and members
        }
        $g1 = Group::getBySlug(CSICOP::GROUP1_SLUG);
        if(is_null($g1)){
            $g1 = new Group();
            $g1->data['slug'] = CSICOP::GROUP1_SLUG;
            $g1->data['sources'][] = $source->data['slug'];
            $g1->data['name'] = "CSICOP Canvas 1";
            $g1->data['description'] = "";
            $g1->data['id'] = $g1->insert();
        }
        else{
            $g1->deleteMembers(); // only deletes asssociations between group and members
        }
        $g2 = Group::getBySlug(CSICOP::GROUP2_SLUG);
        if(is_null($g2)){
            $g2 = new Group();
            $g2->data['slug'] = CSICOP::GROUP2_SLUG;
            $g2->data['sources'][] = $source->data['slug'];
            $g2->data['name'] = "CSICOP Canvas 2";
            $g2->data['description'] = "";
            $g2->data['id'] = $g2->insert();
        }
        else{
            $g2->deleteMembers(); // only deletes asssociations between group and members
        }
        $g3 = Group::getBySlug(CSICOP::GROUP3_SLUG);
        if(is_null($g3)){
            $g3 = new Group();
            $g3->data['slug'] = CSICOP::GROUP3_SLUG;
            $g3->data['sources'][] = $source->data['slug'];
            $g3->data['name'] = "CSICOP Canvas 3";
            $g3->data['description'] = "";
            $g3->data['id'] = $g3->insert();
        }
        else{
            $g3->deleteMembers(); // only deletes asssociations between group and members
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
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                //
                $p->addOccu($line['SPORT']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['CSID']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("newalch irving tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $nInsert++;
                $p->data['id'] = $p->insert(); // Storage
            }
            else{
                // Person already in D10
                $new = [];
                $new['notes'] = [];
                [$curaSourceSlug, $NUM] = Irving::gqid2curaSourceId($line['GQID']);
                $curaFile = strtoupper($curaSourceSlug);
                $curaId = Cura::gqid($curaFile, $NUM);
                $p = Person::getBySourceId($curaSourceSlug, $NUM);
                if(is_null($p)){
                    throw new \Exception("$curaId : try to update an unexisting person");
                }
                // if Cura and csicop have different birth day
                $csiday = substr($line['DATE'], 0, 10);
                $curaday = substr($p->data['birth']['date'], 0, 10);
                if($csiday != $curaday){
                    $nDiffDates++;
                    $new['to-check'] = true;
                    $new['notes'][] = "CHECK birth day : $curaId $curaday / CSID {$line['CSID']} $csiday";
                    if($reportType == 'full'){
                        $datesReport .= "\nCura $curaId\t $curaday {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $datesReport .= "Irving CSID {$line['CSID']}\t $csiday {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                $p->addOccu($line['SPORT']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['CSID']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("cura irving tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);                 
                $nUpdate++;
                $p->update(); // Storage
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
        try{
            $g->data['id'] = $g->insert(); // Storage
        }
        catch(\Exception $e){
            // group already exists
            $g->insertMembers();
        }
        $dt = round($t2 - $t1, 5);
        if($reportType == 'full'){
            $report .= "\n=== Dates different from D10 ===\n" . $datesReport;
            $report .= "============\n";
        }
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        $report .= "$nDiffDates dates differ from D10\n";
        return $report;
    }
        
}// end class    

