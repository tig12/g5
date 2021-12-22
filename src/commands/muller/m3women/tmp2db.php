<?php
/********************************************************************************
    Loads files data/tmp/muller/3-women/muller3-234-women.csv in database.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2021-07-31 07:27:19+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller\m3women;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\model\Issue;
use g5\commands\muller\Muller;
use g5\commands\gauq\LERRCP;

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
        
        $report = "--- muller m3women tmp2db ---\n";
        
        if($reportType == 'full'){
            $namesReport = '';
            $datesReport = '';
            $timesReport = '';
        }
        
        // source of Müller's booklet 3 famous women - insert if does not already exist
        $bookletSource = Source::getBySlug(M3women::BOOKLET_SOURCE_SLUG); // DB
        if(is_null($bookletSource)){
            $bookletSource = new Source(M3women::BOOKLET_SOURCE_DEFINITION_FILE);
            $bookletSource->insert(); // DB
            $report .= "Inserted source " . $bookletSource->data['slug'] . "\n";
        }
        
        // source of muller3-234-women.txt - insert if does not already exist
        $source = Source::getBySlug(M3women::LIST_SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(M3women::LIST_SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::getBySlug(M3women::GROUP_SLUG); // DB
        if(is_null($g)){
            $g = M3women::getGroup();
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
        $lines = M3women::loadTmpFile();
        $linesRaw = M3women::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            $muid = (string)$line['MUID'];
            $mullerId = Muller::mullerId($source->data['slug'], $line['MUID']);
            if(!isset(M3women::GQ_MATCH[$muid])){
                // Person not in Gauquelin data
                $p = new Person();
                $new = [];
                $new['sex'] = 'F';
                $new['trust'] = Muller::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                // note: ONAME1, 2, 3 are not used => TODO ?
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                if($line['TIMOD'] == 'LMT'){
                    $new['birth']['lmt'] = true;
                }
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c1'] = $line['C1'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = (float)$line['LG'];
                $new['birth']['place']['lat'] = (float)$line['LAT'];
                //
                if(M3women::OCCUS[$line['OCCU']] != 'X'){ // X => handled in tweak2db
                    $p->addOccus([ M3women::OCCUS[$line['OCCU']] ]);
                }
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $muid);
                $p->addIdPartial(Muller::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids-in-sources'] = [
                    $source->data['slug'] => $muid,
                    Muller::SOURCE_SLUG => $mullerId,
                ];
                if(M3women::OCCUS[$line['OCCU']] != 'X'){ // X => handled in tweak2db
                    $new['occus'] = [ M3women::OCCUS[$line['OCCU']] ];
                }
                $p->addHistory(
                    command: 'muller m3women tmp2db',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
            }
            else{
                // Person already in other Gauquelin data sets
                // common lines come from A1 A2 A4 A5 A6 D10 E3
                $new = [];
                $new['sex'] = 'F';
                $gqid = M3women::GQ_MATCH[$muid];
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
                    // This happens only for 1 person: 177 Rachilde Eymerie (Gauquelin is correct)
                    // Fixed in tweak2db - so only report, don't fix, don't build a TODO
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
                        //
                        // HERE TODO 
                        // compute date for Gauquelin row - compute date-ut for Müller row
                        // Compare result - log in a TODO object if difference
                        // $new['birth']['date'] should be filled only if this computation gives coherent result
                        //
                        $new['birth']['date'] = $line['DATE'];
                        $nRestoredTimes++;
                    }
                    else if($p->data['birth']['date'] != ''){
                        // compare times
                        // This concerns 9 lines - and none differ !
                        $multime = substr($line['DATE'], 11);
                        $curatime = substr($p->data['birth']['date'], 11);
                        $localReport_txt  = str_pad("Gauquelin $gqid", 20) . "$curatime {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $localReport_txt .= str_pad("Müller    $muid", 20) . "$multime {$line['FNAME']} - {$line['GNAME']}\n\n";
                        $localReport_html  = '<br>' . str_pad("Gauquelin $gqid", 20, '&nbsp;') . "$curatime\n";
                        $localReport_html .= '<br>' . str_pad("Müller    $muid", 20, '&nbsp;') . "$multime\n";
/* 
if($multime != $curatime){
    // code never executed as all times are equal
    $localReport_txt = "Gauquelin / Müller times different:\n" . $localReport_txt;
    $localReport_html = "Gauquelin / Müller times different:\n<br>" . $localReport_html;
    $timesReport .= $localReport_txt;
    $todo = [
        'object' => [
          'date' => date('c'),
          'author' => 'php run-g5.php muller m3women tmp2db',
        ],
        'key' => TODO::CHK_TIME,
        'person' => $p->data['slug'],
        'description' => $localReport_html, 
    ];
    $p->addTodo($todo);
}
else{
echo "NOTE: $localReport_html\n";
    $localReport_html = "Gauquelin and Müller have same time:\n" . $localReport_html;
    $p->addNotes([$localReport_html]);
}
*/
                    }
                }
                // update fields that are more precise in muller234
                //$new['birth']['date'] = $line['DATE']; // Cura contains only date-ut
                if($p->data['birth']['tzo'] == '' && $line['TZO'] != ''){
                    $new['birth']['tzo'] = $line['TZO'];
                }
                if($line['TIMOD'] == 'LMT'){
                    $new['birth']['lmt'] = true;
                }
                // birth place not handled correctly in Müller
                //$new['birth']['place']['name'] = $line['PLACE'];
                if($p->data['birth']['place']['c1'] == '' && $line['C1'] != ''){
                    $new['place']['c1'] = $line['C1'];
                }
                if($p->data['birth']['place']['c2'] == '' && $line['C2'] != ''){
                    $new['place']['c2'] = $line['C2'];
                }
                if(M3women::OCCUS[$line['OCCU']] != 'X'){ // X => handled in tweak2db
                    $p->addOccus([ M3women::OCCUS[$line['OCCU']] ]);
                }
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $muid);
                $p->addIdPartial(Muller::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids-in-sources'] = [
                    $source->data['slug'] => $muid,
                    Muller::SOURCE_SLUG => $mullerId,
                ];
                if(M3women::OCCUS[$line['OCCU']] != 'X'){ // X => handled in tweak2db
                    $new['occus'] = [ M3women::OCCUS[$line['OCCU']] ];
                }
                $p->addHistory(
                    command: 'muller m3women tmp2db',
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
            if($timesReport){
                $report .= "=== Times fixed ===\n" . $timesReport;
            }
            $report .= "============\n";
        }
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        $report .= "$nDiffDates dates differ from Gauquelin\n";
        $report .= "$nRestoredTimes legal times and $nRestoredNames names restored in A files\n";
        return $report;
    }
        
} // end class    
