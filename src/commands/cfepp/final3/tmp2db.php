<?php
/********************************************************************************
    Loads files data/tmp/cfepp/cfepp-1120-nienhuys.csv and data/tmp/cfepp/cfepp-1120-nienhuys-raw.csv in database.

    NOTE: This code cannot be executed several times (won't update the records if already in database)
        To re-execute it (eg for debug purposes), you must rebuild the databse from scratch (at least A2 and E1)
    
    @pre This command must be executed after tmp2db step of
         - LERRCP A1
         - LERRCP D6
         - Ertel Sport
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-04-22 17:12:58+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\model\wiki\Issue;
use g5\model\wiki\Wikiproject;
use g5\commands\cfepp\final3\Final3;
use g5\commands\cfepp\CFEPP;
use g5\commands\gauq\LERRCP;
use g5\commands\ertel\Ertel;
use g5\commands\cpara\CPara;
use tiglib\patterns\Command;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists the different dates between CFEPP and Gauquelin',
    ];
    
    /**
        @param  $params Array containing one element, the type of report.
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
        
        $cmdSignature = 'cfepp final3 tmp2db';
        $report = "--- $cmdSignature ---\n";
        $dateReport = '';
        $occuReport  = '';
        
        // sources corresponding to this test - insert if does not already exist
        
        // sources 'cfepp' and 'cpara' already exist, created in Ertel Sport tmp2db
        
        $final3Source = Source::createFromSlug(Final3::SOURCE_SLUG); // DB
        if(is_null($final3Source)){
            $final3Source = new Source(Final3::SOURCE_DEFINITION_FILE);
            $final3Source->insert(); // DB
            $report .= "Inserted source " . $final3Source->data['slug'] . "\n";
        }
        
        $cfeppBookletSource = Source::createFromSlug(CFEPP::BOOKLET_SOURCE_SLUG); // DB
        if(is_null($cfeppBookletSource)){
            $cfeppBookletSource = new Source(CFEPP::BOOKLET_SOURCE_DEFINITION_FILE);
            $cfeppBookletSource->insert(); // DB
            $report .= "Inserted source " . $cfeppBookletSource->data['slug'] . "\n";
        }
        
        $nienhuysSource = Source::createFromSlug(CFEPP::NIENHUYS_SOURCE_SLUG); // DB
        if(is_null($nienhuysSource)){
            $nienhuysSource = new Source(CFEPP::NIENHUYS_SOURCE_DEFINITION_FILE);
            $nienhuysSource->insert(); // DB
            $report .= "Inserted source " . $nienhuysSource->data['slug'] . "\n";
        }
        
        $cfeppSource = Source::createFromSlug(CFEPP::SOURCE_SLUG); // DB
        
        $cparaSource = Source::createFromSlug(CPara::SOURCE_SLUG); // DB
        
        // groups
        
        $g1120 = Group::createFromSlug(CFEPP::GROUP_1120_SLUG);
        if(is_null($g1120)){
            $g1120 = CFEPP::getGroup1120();
            $g1120->data['id'] = $g1120->insert(); // DB
            $report .= "Inserted group " . $g1120->data['slug'] . "\n";
        }
        else{
            $g1120->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        $g1066 = Group::createFromSlug(CFEPP::GROUP_1066_SLUG);
        if(is_null($g1066)){
            $g1066 = CFEPP::getGroup1066();
            $g1066->data['id'] = $g1066->insert(); // DB
            $report .= "Inserted group " . $g1066->data['slug'] . "\n";
        }
        else{
            $g1066->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        // Wiki projects associated to the issues raised by this import
        $wp_fix_date = Wikiproject::createFromSlug('fix-date');
        
        $nInsert = 0;
        $nUpdate = 0;
        
        $nDiffDates = 0;
        $nDiffDatesUT = 0;
        
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = Final3::loadTmpFile();
        $linesRaw = Final3::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            $ERID = $line['ERID'];
            $CFID = $line['CFID'];
            $newOccus = [$line['OCCU']];
            $fname = ucwords($line['FNAME']);
            $gname = ucwords($line['GNAME']);
            if($ERID == '' && $CFID != 1119){;
                // Person not already in g5 db
                // (true because commands\cfepp\final3\ids uses Ertel file)
                $p = new Person();
                $new = [];
                $new['trust'] = Final3::TRUST_LEVEL;
                if($line['GNAME'] != ''){
                    $new['name']['family'] = $fname;
                    $new['name']['given'] = $gname;
                }
                else{
                    $new['name']['full'] = $line['FNAME'];
                }
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['date-ut'] = $line['DATE-UT'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                if($line['C3'] != ''){
                    $new['birth']['place']['c3'] = $line['C3'];
                }
                $new['birth']['place']['cy'] = 'FR';
                $new['birth']['place']['lg'] = (float)$line['LG'];
                $new['birth']['place']['lat'] = (float)$line['LAT'];
                //
                $p->addOccus($newOccus); // table person_groop handled by command db/init/occu2 - Group::storePersonInGroup() not called here
                $p->addIdInSource(Final3::SOURCE_SLUG, $CFID);
                $p->addPartialId(CFEPP::SOURCE_SLUG, CFEPP::cfeppId($CFID));
                $p->updateFields($new);
                $p->computeSlug();
                // repeat some fields to include in $history
                $new['ids-in-sources'] = [Final3::SOURCE_SLUG => $CFID];
                $new['occus'] = $newOccus;
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
                // As fields of CFEPP were checked by Nienhuys, considered as more reliable
                // than Gauquelin data coming from cura.free.fr
                // But mark with an issue when a difference is found
                //
                // Note : we do not consider here name restoration (names like "Gauquelin-A1-234"
                // because this is completely handled by import of Ertel's file, which must have been executed before.
                $new = [];
                if($CFID != 1119){
                    $p = Person::createFromPartialId(Ertel::SOURCE_SLUG, $ERID); // DB
                }
                else{
                    // one particular case
                    $p = Person::createFromPartialId(LERRCP::SOURCE_SLUG, 'A1-2089'); // DB
                }
                if(is_null($p)){
                    throw new \Exception("$ERID : try to update an unexisting person");
                }
                // Compare g5 value and CFEPP: both date and date-ut
                // Records from A1 always have date-ut, and sometimes date
                // Records from D6 always have date and sometimes date-ut
                // Records from Ertel only have date, never date-ut ; sometimes only birth day, no time
                $cfeppDate = $line['DATE'];
                $cfeppDateUT = $line['DATE-UT'];
                $g5Date = $p->data['birth']['date'];
                $g5DateUT = $p->data['birth']['date-ut'];
                //
                // test date
                //
                if(strlen($g5Date) > 10){
                    // g5 has birth time
                    // Compare only 16 first chars: YYYY-MM-DD HH:MM
                    // to avoid difference between hours like "07:30" and "07:30:00"
                    $compareG5 = substr($g5Date, 0, 16);
                    $compareCFEPP = substr($cfeppDate, 0, 16);
                }
                else{
                    // g5 has only birth day
                    $compareG5 = $g5Date;
                    $compareCFEPP = substr($cfeppDate, 0, 10);
                }
                if($compareG5 != $compareCFEPP){
                    $nDiffDates++;
                    $msg = "Check birth date because CFEPP and g5 birth dates differ"
                           . "Date"
                           . "<br>$g5Date for g5 $ERID"
                           . "<br>$cfeppDate for CFEPP $CFID";
                    $issue = new Issue($p, Issue::TYPE_DATE, $msg);
                    $test = $issue->insert();
                    if($test != -1){
                        $issue->linkToWikiproject($wp_fix_date);
                    }
                    if($reportType == 'full'){
                        $dateReport .= "\nDATE    g5 $ERID\t $g5Date {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $dateReport .= "DATE CFEPP $CFID\t $cfeppDate $fname - $gname\n";
                    }
                    // As CFEPP dates are considered more reliable, the new value uses CFEPP date
                    // => the person slug changes and must be updated
                    // Just updates the day part of the slug
                    $cfeppDay = substr($cfeppDate, 0, 10);
                    $new['slug'] = substr($p->data['slug'], 0, -10) . $cfeppDay;
                }
                //
                // test date-ut
                //
                if(strlen($g5DateUT) == 0){
                    $compareG5 = $compareCFEPP = true; // don't compare
                }
                else if(strlen($g5DateUT) > 10){
                    $compareG5 = substr($g5DateUT, 0, 16);
                    $compareCFEPP = substr($cfeppDateUT, 0, 16);
                }
                else{
                    $compareG5 = $g5DateUT;
                    $compareCFEPP = substr($cfeppDateUT, 0, 10);
                }
                if($compareG5 != $compareCFEPP){
                    $nDiffDatesUT++;
                    $msg = "Check birth date because CFEPP and g5 birth dates UT differ"
                           . "<br>Date UT"
                           . "<br>$g5DateUT for g5 $ERID"
                           . "<br>$cfeppDateUT for CFEPP $CFID";
                    $issue = new Issue($p, Issue::TYPE_TZO, $msg);
                    $test = $issue->insert();
                    if($test != -1){
                        $issue->linkToWikiproject($wp_fix_date);
                    }
                    if($reportType == 'full'){
                        $dateReport .= "\nDATE UT g5    $ERID\t $g5DateUT {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $dateReport .= "DATE UT CFEPP $CFID\t $cfeppDateUT $fname - $gname\n";
                    }
                }
                // Compute occupations
                // $p->data is passed by reference, so $p->data['occus'] modified inside the function
                self::computeOccu($p->data, $line);
                // update fields that are supposed to be more precise in CFEPP
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['date-ut'] = $line['DATE-UT'];
                $new['birth']['place']['c3'] = $line['C3'];
                // place information, for records in Ertel and not in Gauquelin LERRCP
                if($p->data['birth']['place']['name'] == ''){
                    $new['birth']['place']['name'] = $line['PLACE'];
                    $new['birth']['place']['c2'] = $line['C2'];
                    if($line['C3'] != ''){
                        $new['birth']['place']['c3'] = $line['C3'];
                    }
                    $new['birth']['place']['cy'] = 'FR';
                    $new['birth']['place']['lg'] = (float)$line['LG'];
                    $new['birth']['place']['lat'] = (float)$line['LAT'];
                }
                //
                $p->addOccus($newOccus); // table person_groop handled by command db/init/occu2 - Group::storePersonInGroup() not called here
                $p->addIdInSource(Final3::SOURCE_SLUG, $CFID);
                $p->addPartialId(CFEPP::SOURCE_SLUG, CFEPP::cfeppId($CFID));
                $p->updateFields($new);
                $p->computeSlug(); // recompute in case of date modification
                // repeat fields to include in $history
                $new['ids-in-sources'] = [Final3::SOURCE_SLUG => $CFID];
                $new['occus'] = $newOccus;
                $p->addHistory(
                    command: $cmdSignature,
                    sourceSlug: Final3::SOURCE_SLUG,
                    newdata: $new,
                    rawdata: $lineRaw,
                );
                $nUpdate++;
                $p->update(); // DB
            }
            if($CFID <= 1066){
                $g1066->addMember($p->data['id']);
            }
            $g1120->addMember($p->data['id']);
        }
        $t2 = microtime(true);
        $g1066->insertMembers(); // DB
        $g1120->insertMembers(); // DB
        $dt = round($t2 - $t1, 5);
        $nIssues = $nDiffDates + $nDiffDatesUT;
        if($reportType == 'full'){
            $report .= "=== Different dates ===\n" . $dateReport;
            $report .= "============\n";
            $report .= "$nDiffDates different DATE\n";
            $report .= "$nDiffDatesUT different DATE-UT\n";
        }
        $report .= "$nInsert persons inserted, $nUpdate updated - $nIssues date issues inserted ($dt s)\n";
        return $report;
    }
    
    /**
        Modifies if needed a person's occupation code.
        @param  $data       Date of the person to be modified
        @param  $line       Represents one person in CFEPP file
    **/
    private static function computeOccu(&$data, &$line) {
        // in CFEPP file, only one occupation, so check can be done on $newOccus[0]
        $newOccu = $line['OCCU'];
        if($newOccu == 'athletics-competitor'){
            // CFEPP less precise that Gauquelin and Ertel => do nothing
            return;
        }
        if($line['CFID'] == 156){
            // CFEPP 156 Jean-Luc SALOMON 1944-02-27
            // Looks like both Ertel and CFEPP are right
            $data['occus'] = ['motor-sports-competitor', 'athletics-competitor'];
            return;
        }
        if($line['CFID'] == 417){
            // CFEPP 417 D6-339 Daniel REVENU 1942-12-05
            // Checked on wikipedia, error in Ertel file
            $data['occus'] = ['fencer'];
            return;
        }
        if($line['CFID'] == 513){
            // CFEPP 513 A1-1933 Roland LEFEVRE 1914-10-28
            // Checked on wikipedia, error in Ertel file
            $data['occus'] = ['football-player'];
            return;
        }
        if(in_array($line['CFID'], [
            706, // CFEPP 706  Jacqueline DUBIEF 1930-12-04
            708, // CFEPP 708 A1-2038 Alain GILETTI 1939-09-11
            709, // CFEPP 709 D6-200 Nicole HASSLER 1941-01-06
            710, // CFEPP 710  Philippe PELISSIER 1947-11-30 17:40
            711, // CFEPP 711 D6-321 Patrick PERA 1949-01-17
        ])){
            // Checked on wikipedia, CFEPP more precise than Ertel
            $data['occus'] = ['figure-skater'];
            return;
        }
        if($line['CFID'] == 712){
            // CFEPP 712 A1-2039 Albert HASSLER 1903-11-03
            // Checked on wikipedia, CFEPP more precise than Ertel
            // but CFEPP doesn't mention ice-hockey-player
            $data['occus'] = ['ice-hockey-player', 'speed-skater'];
            return;
        }
        if(in_array($line['CFID'], [
            714, // CFEPP 714  Jean Pierre GIUDICELLI 1943-02-20
            715, // CFEPP 715  Raoul GUEGUEN 1947-06-20
        ])){
            // Checked on wikipedia, CFEPP more precise than Ertel
            $data['occus'] = ['modern-pentathlete'];
            return;
        }
        if($line['CFID'] == 717){
            // CFEPP 717 A1-1766 Roger HEINKELE 1913-01-06
            // Checked on wikipedia, error in Ertel file
            $data['occus'] = ['diver'];
            return;
        }
        if($data['occus'] == ['rugby-player']){
            // Here, CFEPP (rugby-union-player or rugby-league-player) is more precise than Ertel (rugby-player)
            // Trust CFEPP, but didn't check
            $data['occus'] = [$newOccu];
            return;
        }
        if($line['CFID'] == 929){
            // CFEPP 929 A1-1970 Michel POMATHIOS 1924-03-18
            // Adopted a mix of CFEPP (rugby-union-player) and Gauquelin (executive)
            $data['occus'] = ['rugby-union-player', 'executive'];
            return;
        }
        if($line['CFID'] == 1031){
            // CFEPP 1031 A1-2086 Alain GERBAULT 1893-11-17
            $data['occus'] = ['tennis-player', 'sport-sailer', 'writer'];
            return;
        }
        if($line['CFID'] == 1047){
            // CFEPP 1047  Eugene MANCHISKA 1919-09-21
            $data['occus'] = ['table-tennis-player'];
            return;
        }
        if($line['CFID'] == 1057){
            // CFEPP 1057 A1-2079 Pierre FELBACQ 1904-01-23
            // Checked on wikipedia, error in Ertel file
            $data['occus'] = ['archer'];
            return;
        }
        if($line['CFID'] == 1061){
            // CFEPP 1061 A1-1764 Paul DUJARDIN 1894-05-10
            // Checked on wikipedia, error in Ertel file
            $data['occus'] = ['water-polo-player'];
            return;
        }
        // debug trace used to develop
        // became useless after corrections
        if(!in_array($newOccu, $data['occus'])){
            echo "\nDIFFERENT OCCUS ";
            echo "CFEPP {$line['CFID']} {$line['GQID']} {$line['GNAME']} {$line['FNAME']} {$line['DATE']}\n";
            echo "newOccu = $newOccu\n";
            echo "p->data['occus'] = " .implode(' + ', $data['occus']) . "\n";
        }
    }
    
} // end class
