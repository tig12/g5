<?php
/********************************************************************************
    Loads files data/tmp/muller/1-writers/muller1-402-writers.csv and muller1-402-writers-raw.csv in database.
    Affects records imported from A6
    
    @license    GPL
    @history    2020-08-25 18:18:41+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller\m1writers;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\Newalch;
use g5\commands\muller\Muller;
use g5\commands\gauq\LERRCP;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of names and dates restoration on A6',
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
            $msg .= "  $k : $v\n";
        }
        if(count($params) != 1){
            return "WRONG USAGE - This command needs a parameter to specify which output it displays. Can be :\n" . $msg;
        }
        $reportType = $params[0];
        if(!in_array($reportType, array_keys(self::REPORT_TYPE))){
            return "INVALID PARAMETER : $reportType - Possible values :\n" . $msg;
        }
        
        $report = "--- muller m1writers tmp2db ---\n";
        
        if($reportType == 'full'){
            $namesReport = '';
            $datesReport = '';
        }
        
        // source of Müller's booklet AFD1 - insert if does not already exist
        $bookletSource = Source::getBySlug(M1writers::BOOKLET_SOURCE_SLUG); // DB
        if(is_null($bookletSource)){
            $bookletSource = new Source(M1writers::BOOKLET_SOURCE_DEFINITION_FILE);
            $bookletSource->insert(); // DB
            $report .= "Inserted source " . $bookletSource->data['slug'] . "\n";
        }
        
        // source of 5muller_writers.csv - insert if does not already exist
        $source = Source::getBySlug(M1writers::LIST_SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(M1writers::LIST_SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::getBySlug(M1writers::GROUP_SLUG); // DB
        if(is_null($g)){
            $g = M1writers::getGroup();
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
        $lines = M1writers::loadTmpFile();
        $linesRaw = M1writers::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            if($line['GQID'] == ''){
                // Person not in Gauquelin data
                $p = new Person();
                $new = [];
                $new['trust'] = Newalch::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['sex'] = $line['SEX'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['note'] = $line['LMT'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                //
                $p->addOccus(['writer']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['MUID']);
                $mullerId = Muller::mullerId($source->data['slug'], $line['MUID']);
                $p->addIdInSource(Muller::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [
                    Muller::SOURCE_SLUG => $mullerId,
                    $source->data['slug'] => $line['MUID'],
                ];
                $new['occus'] = ['writer'];
                $p->addHistory(
                    command: 'muller m1writers tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
            }
            else{
                // Person already in A6
                $new = [];
                $new['notes'] = [];
                [$curaSourceSlug, $NUM] = M1writers::gqid2curaSourceId($line['GQID']);
                $curaFile = strtoupper($curaSourceSlug);
                $gqId = LERRCP::gauquelinId($curaFile, $NUM);
                $p = Person::getBySourceId($curaSourceSlug, $NUM);
                if(is_null($p)){
                    throw new \Exception("$gqId : try to update an unexisting person");
                }
                if($p->data['name']['family'] == "Gauquelin-$gqId"){
                    $nRestoredNames++;
                    if($reportType == 'full'){
                        $namesReport .= "\nCura\t $gqId\t {$p->data['name']['family']}\n";
                        $namesReport .= "Müller\t {$line['MUID']}\t {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                // if Cura and Müller have different birth day
                $mulday = substr($line['DATE'], 0, 10);
                // in E6, stored in field 'date-ut'
                $curaday = substr($p->data['birth']['date-ut'], 0, 10);
                if($mulday != $curaday){
                    $nDiffDates++;
                    $new['to-check'] = true;
                    $new['notes'][] = "CHECK: birth day - $gqId $curaday / M1writers {$line['MUID']} $mulday";
                    if($reportType == 'full'){
                        $datesReport .= "\nCura\t $gqId\t $curaday {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $datesReport .= "Müller\t {$line['MUID']}\t $mulday {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                // update fields that are more precise in muller402
                $new['birth']['date'] = $line['DATE']; // Cura contains only date-ut
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['note'] = $line['LMT'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $p->addOccus(['writer']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['MUID']);
                $mullerId = Muller::mullerId($source->data['slug'], $line['MUID']);
                $p->addIdInSource(Muller::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [
                    Muller::SOURCE_SLUG => $mullerId,
                    $source->data['slug'] => $line['MUID'],
                ];
                $new['occus'] = ['writer'];
                $p->addHistory(
                    command: 'muller m1writers tmp2db',
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
        $report .= "$nDiffDates dates differ from A6";
        $report .= " - $nRestoredNames names restored in A6\n";
        return $report;
    }
        
}// end class    

