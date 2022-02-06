<?php
/********************************************************************************
    Loads files data/tmp/gauq/lerrcp/A*.csv and A*-raw.csv in database.
    Must be exectued in alphabetical order (first A1, then A2 ... A6)
    to respect the defition of Gauquelin id
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-08-19 05:23:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\A;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\model\Occupation;
use g5\model\Issue;
use g5\commands\gauq\Cura5;
use g5\commands\gauq\LERRCP;
use tiglib\timezone\offset_fr;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Echoes the details of duplicate entries',
    ];
    
    /**
        @param  $params Array containing 3 elements :
                        - a string identifying what is processed (ex : "A1")
                        - "tmp2db" (useless here)
                        - the type of report ; see REPORT_TYPE
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 3){
            return "USELESS PARAMETER : " . $params[3] . "\n";
        }
        $msg = '';
        foreach(self::REPORT_TYPE as $k => $v){
            $msg .= "  $k : $v\n";
        }
        if(count($params) != 3){
            return "WRONG USAGE - This command needs a parameter to specify which output it displays. Can be :\n" . $msg;
        }
        $reportType = $params[2];
        if(!in_array($reportType, array_keys(self::REPORT_TYPE))){
            return "INVALID PARAMETER : $reportType - Possible values :\n" . $msg;
        }
        
        $datafile = $params[0];
        
        $report = "--- gauq $datafile tmp2db ---\n";
        
        // source corresponding to LERRCP - insert if does not already exist
        $lerrcpSource = Source::getBySlug(LERRCP::SOURCE_SLUG); // DB
        if(is_null($lerrcpSource)){
            $lerrcpSource = new Source(LERRCP::SOURCE_DEFINITION_FILE);
            $lerrcpSource->insert(); // DB
            $report .= "Inserted source " . $lerrcpSource->data['slug'] . "\n";
        }
        
        // source corresponding to cura5 - insert if does not already exist
        $curaSource = Source::getBySlug(Cura5::SOURCE_SLUG); // DB
        if(is_null($curaSource)){
            $curaSource = new Source(Cura5::SOURCE_DEFINITION_FILE);
            $curaSource->insert(); // DB
            $report .= "Inserted source " . $curaSource->data['slug'] . "\n";
        }
        
        // source corresponding LERRCP booklet of current A file
        $bookletSource = Source::getBySlug(LERRCP::datafile2bookletSourceSlug($datafile)); // DB
        if(is_null($bookletSource)){
            $bookletSource = LERRCP::getBookletSourceOfDatafile($datafile);
            $bookletSource->insert(); // DB
            $report .= "Inserted source " . $bookletSource->data['slug'] . "\n";
        }
        
        // source corresponding to current A file
        $source = Source::getBySlug(LERRCP::datafile2sourceSlug($datafile)); // DB
        if(is_null($source)){
            $source = LERRCP::getSourceOfDatafile($datafile);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::createFromSlug(LERRCP::datafile2groupSlug($datafile)); // DB
        if(is_null($g)){
            $g = LERRCP::getGroupOfDatafile($datafile);
            $g->data['id'] = $g->insert(); // DB
            $report .= "Inserted group " . $g->data['slug'] . "\n";
        }
        else{
            $g->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = LERRCP::loadTmpFile($datafile);
        $linesRaw = LERRCP::loadTmpRawFile($datafile);
        $nInsert = 0;
        $nDuplicates = 0;
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            // try to get this person from database
            $test = new Person();
            $test->data['name']['family'] = $line['FNAME'];
            $test->data['name']['given'] = $line['GNAME'];
            $test->data['birth']['date-ut'] = $line['DATE-UT'];
            $test->computeSlug();
            $gqId = LERRCP::gauquelinId($datafile, $line['NUM']);
            $newOccus = explode('+', $line['OCCU']);
            $p = Person::getBySlug($test->data['slug']); // DB
            if(is_null($p)){
                // insert new person
                $p = new Person();
                $p->addIdInSource($source->data['slug'], $line['NUM']);
                $p->addIdPartial($lerrcpSource->data['slug'], $gqId);
                $new = [];
                $dateOrTimeIssue = '';
                $new['trust'] = Cura5::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['birth'] = [];
                $new['birth']['date-ut'] = $line['DATE-UT'];
                if($line['DATE-C'] != ''){
                    // date restored by class legalTime
                    $new['birth']['date'] = $line['DATE-C'];
                    $new['birth']['tzo'] = $line['TZO'];
                }
                if($line['NOTES-DATE'] != ''){
                    $dateOrTimeIssue = self::buildTimeRestorationIssue($line['CY'], $line['NOTES-DATE']);
                }
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                if($line['C3']){
                    // in France, useful only for Paris and Lyon arrondissements
                    $new['birth']['place']['c3'] = $line['C3'];
                }
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = (float)$line['LG'];
                $new['birth']['place']['lat'] = (float)$line['LAT'];
                $new['birth']['place']['geoid'] = (int)$line['GEOID'];
                $p->updateFields($new);
                $p->addOccus($newOccus);
                $p->computeSlug();
                if($dateOrTimeIssue != ''){
                    $p->addIssue($dateOrTimeIssue);
                }
                // repeat fields to include in $history
                $new['ids-in-sources'] = [
                    $source->data['slug'] => $line['NUM'],
                ];
                $new['ids_partial'] = [
                    $lerrcpSource->data['slug'] => $gqId,
                ];
                $new['occus'] = $newOccus;
                if($dateOrTimeIssue != ''){
                    $new['issues'] = [$dateOrTimeIssue];
                }
                $p->addHistory(
                    command: "gauq $datafile tmp2db",
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $p->data['id'] = $p->insert(); // DB
                $nInsert++;
            }
            else{
                // duplicate, person appears in more than one lerrcp file
                if(!isset($line['OCCU'])){
                    throw new \Exception("Missing definition for occupation - {$line['NUM']} {$line['FNAME']} {$line['GNAME']} ");
                }
                // check date coherence - useful for persons present in several A files
                // Not used anymore as all errors shown by this check were fixed
                // $testDate = self::checkDate($datafile, $p, $line);
                $p->addOccus($newOccus);
                // does not addIdPartial(lerrcp) to conform with the definition of Gauquelin id:
                // lerrcp id takes the value of the first volume where it appears.
                // lerrcp id already affected in a previous file for this record.
                $p->addIdInSource($source->data['slug'], $line['NUM']);
                // repeat fields to include in $history
                $new = [];
                $new['sources'] = $source->data['slug'];
                $new['ids-in-sources'] = [
                    $source->data['slug'] => $line['NUM'],
                ];
                $new['occus'] = $newOccus;
                $p->addHistory(
                    command: "gauq $datafile tmp2db",
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $p->update(); // DB
                if($reportType == 'full'){
                    $report .= "Duplicate "
                    . $test->data['slug'] . " : "
                    . $p->data['ids-in-sources'][LERRCP::SOURCE_SLUG]
                    . " = $gqId\n";
                }
                $nDuplicates++;
            }
            $g->addMember($p->data['id']);
        }
        $t2 = microtime(true);
        $g->insertMembers(); // DB
        $dt = round($t2 - $t1, 5);
        if($reportType == 'full' && $nDuplicates != 0){
            $report .= "-------\n";
        }
        $report .= "$nInsert persons inserted, $nDuplicates updated ($dt s)\n";
        return $report;
    }
    
    /**
        Computes a message indicating why legal time couldn't be restored.
        @param  $country
        @param  $case       See ofsset_* classes
    **/
    public static function buildTimeRestorationIssue(string $country, int $case): string {
        $issue = '';
        switch($country){
            case 'FR': 
                switch(offset_fr::MESSAGES[$case]){
                	case offset_fr::MSG_1871_1918_LORRAINE: 
                	case offset_fr::MSG_WW2:
                	case offset_fr::MSG_WW2_END:
                        $issue = offset_fr::MESSAGES[$case];
                	break;
                }
            break;
        }
        return $issue;
    }
    
    /**
        Checks if the date stored in database is coherent with date found in current datafile.
        The purpose is to detect incoherences between 2 files of series A.
        
        NOT USED ANYMORE, as all errors shown by this function have been fixed.
        
        @param      $datafile   The datafile currently processed (eg 'A2').
        @param      $p          Person already stored in database, compared with the current line being treated.
        @param      $line       Line of tmp file currently stored in db
        @return     true if the 2 dates are coherent, false otherwise.
    **/
    public static function checkDate(string $datafile, Person $p, array $line): string {
        // comes from previous tmp2db execution
        $d1utc = $p->data['birth']['date-ut'];  // DATE-UT
        $d1    = $p->data['birth']['date'];     // DATE-C
        // comes from line currently imported
        $d2utc = $line['DATE-UT'];
        $d2    = $line['DATE-C'];
        $msg = '';
        if($d1utc != $d2utc || $d1 != $d2){
            $msg = 'Date difference for ' . $p->data['slug'];
            if($d1utc != $d2utc){
                $msg .= "\n<br>Date UTC in " . ' ' . $p->data['ids-partial'][LERRCP::SOURCE_SLUG] . ' = ' . $d1utc;
                $msg .= "\n<br>Date UTC in " . ' ' . LERRCP::gauquelinId($datafile, $line['NUM']) . ' = ' . $d2utc;
            }
            if($d1 != $d2){
                $msg .= "\n<br>Date in " . ' ' . $p->data['ids-partial'][LERRCP::SOURCE_SLUG] . ' = ' . $d1;
                $msg .= "\n<br>Date in " . ' ' . LERRCP::gauquelinId($datafile, $line['NUM']) . ' = ' . $d2;
            }
        }
        return $msg;
    }
    
} // end class    
