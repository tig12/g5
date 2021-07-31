<?php
/********************************************************************************
    Loads files data/tmp/newalch/4391SPO.csv in database.
    Affects records imported in A1
    
    @license    GPL
    @history    2020-09-25 20:55:38+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\newalch\ertel4391;

use g5\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\newalch\Newalch;
use g5\commands\gauquelin\LERRCP;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of names restoration on A1',
    ];
    
    // *****************************************
    // Implementation of Command
    /**
        @param  $params Array containing 1 element : the type of report ; see REPORT_TYPE
    **/
    public static function execute($params=[]): string {
die("\nNOT IMPLEMENTED\n" . __FILE__ . ' - line ' . __LINE__ . "\n");
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
        
        $report = "--- Ertel4391 tmp2db ---\n";
        
        if($reportType == 'full'){
            $namesReport = '';
            $datesReport = '';
        }
                                             
        // source corresponding to 5a_muller_medics - insert if does not already exist
        $source = Ertel4391::getSource();
        try{
            $source->insert();
        }
        catch(\Exception $e){
            // already inserted, do nothing
        }
        
        // main group
        $g = Group::getBySlug(Ertel4391::GROUP_SLUG);
        if(is_null($g)){
            $g = Ertel4391::getGroup();
        }
        else{
            $g->deleteMembers(); // only deletes asssociations between group and members
        }
        // subgroups
        $subgroups = [];
        foreach(Ertel4391::SUBGROUPS as $slug => $value){
            $subgroups[$slug] = Group::getBySlug($slug);
            if(is_null($subgroups[$slug])){
                $subgroups[$slug] = Ertel4391::getSubgroup($slug);
            }
            else{
                $subgroups[$slug]->deleteMembers(); // only deletes asssociations between group and members
            }
        }
        
        $nInsert = 0;
        $nUpdate = 0;
        $nRestoredNames = 0;
        $nDiffDates = 0;
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = Ertel4391::loadTmpFile();
        $linesRaw = Ertel4391::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
echo "\n<pre>"; print_r($line); echo "</pre>\n"; exit;
            $lineRaw = $linesRaw[$i];
            // All persons already in db are coming from Gauquelin data
            // see docs/newalch-ertel4391.html#ertel-s-subsamples
            if($line['GQID'] == ''){
                // Person not in Gauquelin data
                $p = new Person();
                $new = [];
                $new['trust'] = Newalch::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                //
                $p->addOccu($line['SPORT']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("newalch ertel4391 tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $nInsert++;
                $p->data['id'] = $p->insert(); // Storage
            }
            else{
                // Person already in A1 or D6 or D10
                /* 
                $new = [];
                $new['notes'] = [];
                [$curaFile, $NUM] = Ertel4391::gnr2cura($line['GNR']);
                $gqId = LERRCP::gauquelinId($curaFile, $NUM);
                $p = Person::getBySourceId($curaFile, $NUM);
                if(is_null($p)){
                    throw new \Exception("$gqId : try to update an unexisting person");
                }
                if($p->data['name']['family'] == "Gauquelin-$gqId"){
                    $nRestoredNames++;
                    if($reportType == 'full'){
                        $namesReport .= "\nCura NUM $gqId\t {$p->data['name']['family']}\n";
                        $namesReport .= "Müller NR {$line['NR']}\t {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                // if Cura and Müller have different birth day
                $mulday = substr($line['DATE'], 0, 10);
                // from A2, stored in field 'date-ut' ; from E1, stored in field 'date'
                if(isset($p->data['ids-in-sources']['A2'])){
                    $curaday = substr($p->data['birth']['date-ut'], 0, 10);
                }
                else{
                    $curaday = substr($p->data['birth']['date'], 0, 10);
                }
                if($mulday != $curaday){
                    $nDiffDates++;
                    $new['notes'][] = "CHECK birth day : $gqId $curaday / Ertel4391 {$line['NR']} $mulday";
                    $new['to-check'] = true;
                    if($reportType == 'full'){
                        $datesReport .= "\nCura $gqId\t $curaday {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $datesReport .= "Müller NR {$line['NR']}\t $mulday {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                // update fields that are more precise in muller1083
                $new['birth']['date'] = $line['DATE']; // Cura contains only date-ut
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['name']['nobiliary-particle'] = $line['NOB'];
                $new['name']['family'] = $line['FNAME'];
                if($p->data['name']['given'] == ''){
                    // happens with names like Gauquelin-A1-258
                    $new['name']['given'] = $line['GNAME'];
                }
                // Müller name considered as = to full name copied from birth certificate
                // (Gauquelin name considered as current name)
                $new['name']['official']['given'] = $line['GNAME'];
                //
                $p->addOccu('PH');
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("cura muller1083 tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);                 
                $nUpdate++;
                $p->update(); // Storage
                */
            }
            // main group
            $g->addMember($p->data['id']);
            // subgroups
            $QUEL = $line['QUEL'];
            if(isset($subgroups[$QUEL])){
                $subgroups[$QUEL]->addMember($p->data['id']);
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
        foreach($subgroups as $slug => $subgroup){
            try{
                $subgroup->data['id'] = $subgroup->insert(); // Storage
            }
            catch(\Exception $e){
                // group already exists
                $subgroup->insertMembers();
            }
        }
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
        
}// end class    

