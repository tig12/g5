<?php
/********************************************************************************
    Loads files data/tmp/muller/5-medics/muller5-1083-medics.csv and muller5-1083-medics-raw.csv in database.
    Affects records imported in A2 and E1

    NOTE: This code cannot be executed several times (won't update the records if already in database)
        To re-execute it (eg for debug purposes), you must rebuild the databse from scratch (at least A2 and E1)
    
    @license    GPL
    @history    2020-08-20 10:46:02+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller\afd5medics;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\Newalch;
use g5\commands\gauq\LERRCP;
use g5\commands\muller\AFD;

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
        
        $report = "--- AFD5medics tmp2db ---\n";
        
        if($reportType == 'full'){
            $namesReport = '';
            $datesReport = '';
        }
        
        // source corresponding to Müller's Astro-Forschungs-Daten - insert if does not already exist
        $afdSource = Source::getBySlug(AFD::SOURCE_SLUG); // DB
        if(is_null($afdSource)){
            $afdSource = new Source(AFD::SOURCE_DEFINITION_FILE);
            $afdSource->insert(); // DB
            $report .= "Inserted source " . $afdSource->data['slug'] . "\n";
        }
        
        // source corresponding to newalchemypress - insert if does not already exist
        $newalchSource = Source::getBySlug(Newalch::SOURCE_SLUG); // DB
        if(is_null($newalchSource)){
            $newalchSource = new Source(Newalch::SOURCE_DEFINITION_FILE);
            $newalchSource->insert(); // DB
            $report .= "Inserted source " . $newalchSource->data['slug'] . "\n";
        }
        
        // source of Müller's booklet AFD3women - insert if does not already exist
        $bookletSource = Source::getBySlug(AFD5medics::BOOKLET_SOURCE_SLUG); // DB
        if(is_null($bookletSource)){
            $bookletSource = new Source(AFD5medics::BOOKLET_SOURCE_DEFINITION_FILE);
            $bookletSource->insert(); // DB
            $report .= "Inserted source " . $bookletSource->data['slug'] . "\n";
        }
        
        // source of 5a_muller_medics.txt - insert if does not already exist
        $source = Source::getBySlug(AFD5medics::LIST_SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(AFD5medics::LIST_SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::getBySlug(AFD5medics::GROUP_SLUG);
        if(is_null($g)){
            $g = AFD5medics::getGroup();
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
        $lines = AFD5medics::loadTmpFile();
        $linesRaw = AFD5medics::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        $newOccus = ['physician'];
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];                                                                        
            $lineRaw = $linesRaw[$i];
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
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                //
                $p->addOccus($newOccus);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $mullerId = AFD::mullerId($source->data['slug'], $line['NR']);
                $p->addIdInSource(AFD::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [ $source->data['slug'] => $line['NR'] ];
                $new['occus'] = $newOccus;
                $p->addHistory(
                    command: 'newalch muller1083 tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // Storage
            }
            else{
                // Person already in A2 or E1
                $new = [];
                $new['notes'] = [];
                [$curaSourceSlug, $NUM] = AFD5medics::gnr2LERRCPSourceId($line['GNR']);
                $curaFile = strtoupper($curaSourceSlug);
                $gqId = LERRCP::gauquelinId($curaFile, $NUM);
                $p = Person::getBySourceId($curaSourceSlug, $NUM); // DB
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
                    $new['notes'][] = "CHECK birth day : $gqId $curaday / AFD5medics {$line['NR']} $mulday";
                    $new['to-check'] = true;
                    if($reportType == 'full'){
                        $datesReport .= "\nCura $gqId\t $curaday {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $datesReport .= "Müller NR {$line['NR']}\t $mulday {$line['FNAME']} - {$line['GNAME']}\n";
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
                $p->addOccus($newOccus);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $mullerId = AFD::mullerId($source->data['slug'], $line['NR']);
                $p->addIdInSource(AFD::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [ $source->data['slug'] => $line['NR'] ];
                $new['occus'] = $newOccus;
                $p->addHistory(
                    command: 'cura muller1083 tmp2db',
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
        
}// end class    

