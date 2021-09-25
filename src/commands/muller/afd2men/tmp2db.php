<?php
/********************************************************************************
    Loads files data/tmp/muller/afd2-men/muller-afd2-men.csv in database.
    
    @license    GPL
    @history    2021-09-19 18:05:53+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller\afd2men;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\muller\AFD;
use g5\commands\gauquelin\LERRCP;
use g5\commands\newalch\muller402\Muller402;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of names and dates restoration on Gauquelin files',
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
        
        $report = "--- afd2men tmp2db ---\n";
        
        if($reportType == 'full'){
            $namesReport = '';
            $datesReport = '';
            $timesReport = '';
        }
        
        // source of Müller's booklet AFD2 - insert if does not already exist
        $bookletSource = Source::getBySlug(AFD2::BOOKLET_SOURCE_SLUG); // DB
        if(is_null($bookletSource)){
            $bookletSource = new Source(AFD2::BOOKLET_SOURCE_DEFINITION_FILE);
            $bookletSource->insert(); // DB
            $report .= "Inserted source " . $bookletSource->data['slug'] . "\n";
        }
        
        // source of muller-afd2-men.txt - insert if does not already exist
        $source = Source::getBySlug(AFD2::LIST_SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(AFD2::LIST_SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::getBySlug(AFD2::GROUP_SLUG); // DB
        if(is_null($g)){
            $g = AFD2::getGroup();
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
        $nRestoredTimes = 0;
        
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = AFD2::loadTmpFile();
        $linesRaw = AFD2::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            $muid = (string)$line['MUID'];
            $mullerId = AFD::mullerId($source->data['slug'], $line['MUID']);
            $new = [];
            $new['sex'] = 'M';
            if($muid == '457'){
                // M1-367 - Exceptional case: same person present in 2 different Müller data sets
                // Birth time and birth place differ
                // M1-367: Quasimodo Salvatore M 1901-08-20 18:00 +01:00  Siracusa IT SR 15 37 writer
                // M3-457: Quasimodo, Salvatore 20.08.1901 04.10 -1.00 I Modica 36 N 48 014 E 58 AR 01 56 SA N
                // Update with M3 data (because birth place corresponds to Wikipedia) But should be checked
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['note'] = $line['TIMOD'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $muid);
                $p->updateFields($new);
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [
                    $source->data['slug'] => $muid,
                    AFD::SOURCE_SLUG => $mullerId,
                ];
                if(AFD2::OCCUS[$line['OCCU']] != 'X'){ // X => handled in tweak2db
                    $new['occus'] = [ AFD2::OCCUS[$line['OCCU']] ];
                }
                $p->addHistory(
                    command: 'muller afd2men tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nUpdate++;
                $p->update(); // DB
                continue;
            }
            if(!isset(AFD2::MU_GQ[$muid])){
                // Person not already in db (mainly in Gauquelin data)
                $p = new Person();
                $new['trust'] = AFD::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['name']['nobl'] = $line['NOBL'];
                $new['name']['fame']['full'] = $line['FAME'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['note'] = $line['TIMOD'];
                $new['birth']['place']['name'] = $line['PLACE'];
// TODO handle c1 and c2
//                $new['birth']['place']['c1'] = $line['C1'];
//                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                //
                $p->addOccus([ AFD2::OCCUS[$line['OCCU']] ]);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $muid);
                $p->addIdInSource(AFD::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [
                    $source->data['slug'] => $muid,
                    AFD::SOURCE_SLUG => $mullerId,
                ];
                $new['occus'] = [ AFD2::OCCUS[$line['OCCU']] ];
                $p->addHistory(
                    command: 'muller afd2men tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                try{
                    $p->data['id'] = $p->insert(); // DB
                }
                catch(\Exception $e){
                    throw new \Exception("Unable to insert Müller id $muid\n" . $e->getMessage());
                }
            }
            else{
                // Person already in other Gauquelin data sets
                $gqid = AFD2::MU_GQ[$muid];
                $tmp = explode('-', $gqid);
                $curaSourceSlug = LERRCP::datafile2sourceSlug($tmp[0]);
                $NUM = $tmp[1];
                $p = Person::getBySourceId($curaSourceSlug, $NUM);
                if(is_null($p)){
                    throw new \Exception("$gqid : try to update an unexisting person");
                }
                if($p->data['name']['family'] == "Gauquelin-$gqid"){
                    $new['name']['family'] = $line['FNAME'];
                    $new['name']['given'] = $line['GNAME'];
                    $nRestoredNames++;
                    if($reportType == 'full'){
                        $namesReport .= "Cura\t $gqid\t {$p->data['name']['family']}\n";
                        $namesReport .= "Müller\t {$muid}\t {$line['FNAME']} - {$line['GNAME']}\n\n";
                    }
                }
                // if Cura and Müller have different birth day
                $mulday = substr($line['DATE'], 0, 10);
                // in A6, stored in field 'date-ut' - in D10 and E3 in field 'date'
                $curaday = $p->data['birth']['date-ut'] != ''
                    ? substr($p->data['birth']['date-ut'], 0, 10)
                    : substr($p->data['birth']['date'], 0, 10);
                if($mulday != $curaday){
                    $nDiffDates++;
                    if($reportType == 'full'){
                        $datesReport .= "Cura\t $gqid\t $curaday {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $datesReport .= "Müller\t {$muid}\t $mulday {$line['FNAME']} - {$line['GNAME']}\n\n";
                    }
                }
                else{
                    // in A, stored in field 'date-ut' - in D10 and E3 in field 'date'
                    if($p->data['birth']['date-ut'] != '' && $p->data['birth']['date'] == ''){
                        // A file, restore date
                        $new['birth']['date'] = $line['DATE'];
                        $nRestoredTimes++;
                    }
                    else if($p->data['birth']['date'] != ''){
                        // compare times
// This concerns 9 lines - and none differ !
                        $multime = substr($line['DATE'], 11);
                        $curatime = substr($p->data['birth']['date'], 11);
                        if($multime != $curatime){
                            $timesReport .= "Cura\t $gqid\t $curatime {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                            $timesReport .= "Müller\t {$muid}\t $multime {$line['FNAME']} - {$line['GNAME']}\n\n";
                        }
                    }
                }
                // update fields that are more precise in muller234
                //$new['birth']['date'] = $line['DATE']; // Cura contains only date-ut
                if($p->data['birth']['tzo'] == '' && $line['TZO'] != ''){
                    $new['birth']['tzo'] = $line['TZO'];
                }
                if($p->data['birth']['note'] == '' && $line['TIMOD'] != ''){
                    $new['birth']['note'] = $line['TIMOD'];
                }
                // birth place not handled correctly in Müller
                //$new['birth']['place']['name'] = $line['PLACE'];
                if($p->data['birth']['place']['c1'] == '' && $line['C1'] != ''){
                    $new['place']['c1'] = $line['C1'];
                }
                if($p->data['birth']['place']['c2'] == '' && $line['C2'] != ''){
                    $new['place']['c2'] = $line['C2'];
                }
                $p->addOccus([ AFD2::OCCUS[$line['OCCU']] ]);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $muid);
                $p->addIdInSource(AFD::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [
                    $source->data['slug'] => $muid,
                    AFD::SOURCE_SLUG => $mullerId,
                ];
                $new['occus'] = [ AFD2::OCCUS[$line['OCCU']] ];
                $p->addHistory(
                    command: 'muller afd2men tmp2db',
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
            $report .= "=== Dates fixed ===\n" . $datesReport;
            if($timesReport) $report .= "=== Times fixed ===\n" . $timesReport;
            $report .= "============\n";
        }
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        $report .= "$nDiffDates dates differ from Gauquelin\n";
        $report .= "$nRestoredTimes legal times and $nRestoredNames names restored in A files\n";
        return $report;
    }
        
}// end class    

