<?php
/********************************************************************************
    Loads files data/tmp/gauq/lerrcp/E1.csv and E1-raw.csv in database.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-08-20 07:47:13+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\gauq\E1_E3;

use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\model\Occupation;
use g5\model\wiki\Issue;
use g5\model\wiki\Wikiproject;
use g5\commands\gauq\LERRCP;
use g5\commands\gauq\Cura5;
use tiglib\patterns\Command;

class tmp2db implements Command {
                                                                                          
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Echoes the details of duplicate entries',
    ];
    
    /**
        @param  $params Array containing 3 elements :
                        - "E1" or "E3"
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
        
        // source corresponding to LERRCP
        // not inserted because must have been done in A1 import
        $lerrcpSource = new Source(LERRCP::SOURCE_DEFINITION_FILE);
        
        // source corresponding LERRCP booklet of D6 file
        $bookletSource = Source::createFromSlug(LERRCP::datafile2bookletSourceSlug($datafile)); // DB
        if(is_null($bookletSource)){
            $bookletSource = LERRCP::getBookletSourceOfDatafile($datafile);
            $bookletSource->insert(); // DB
            $report .= "Inserted source " . $bookletSource->data['slug'] . "\n";
        }
        
        // source corresponding to D6 file
        $source = Source::createFromSlug(LERRCP::datafile2sourceSlug($datafile)); // DB
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
        
        // Wiki project associated to the issues raised by this import
        $wp = Wikiproject::createFromSlug('fix-gauquelin');
        
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
            $test->data['birth']['date'] = $line['DATE'];
            $test->computeSlug();
            $gqId = LERRCP::gauquelinId($datafile, $line['NUM']);
            $newOccus = explode('+', $line['OCCU']);
            $p = Person::createFromSlug($test->data['slug']); // DB
            if(is_null($p)){
                // insert new person
                $p = new Person();
                $p->addIdInSource($source->data['slug'], $line['NUM']);
                $p->addPartialId($lerrcpSource->data['slug'], $gqId);
                $new = [];
                $new['trust'] = Cura5::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                if($line['NOTE']){
                    $new['notes'] = [];
                    $new['notes'][] = self::expandNote($line['NOTE']);
                }
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['date-ut'] = $line['DATE-UT'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['c3'] = $line['C3'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = (float)$line['LG'];
                $new['birth']['place']['lat'] = (float)$line['LAT'];
                $new['birth']['place']['geoid'] = (int)$line['GEOID'];
                $p->updateFields($new);
                $p->addOccus($newOccus);
                $p->computeSlug();
                // repeat fields to include in $history
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
                $p->data['id'] = $p->insert(); // DB
                $nInsert++;
            }
            else{
                // duplicate, person appears in more than one cura file
                $occus = explode('+', $line['OCCU']);
                $p->addOccus($newOccus);
                // does not addPartialId(lerrcp) to respect the definition of Gauquelin id:
                // lerrcp id takes the value of the first volume where it appears.
                // lerrcp id already affected in a previous file for this record.
                $p->addIdInSource($source->data['slug'], $line['NUM']);
                if($line['DATE'] != $p->data['birth']['date']){
                    // Concerns 4 rows in E1 - only hour problems
                    $msg = "Check birth date because $datafile and other Gauquelin file differ\n"
                           . "<br>{$line['DATE']} for Gauquelin $datafile\n"
                           . "<br>{$p->data['birth']['date']} for other Gauquelin file\n";
                    $issue = new Issue( $p, Issue::TYPE_TIME, $msg );
                    $issue->insert();
                    $issue->linkToWikiproject($wp);
                }
                $new = [];
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
                    $report .= 'Duplicate '
                    . $test->data['slug'] . ' : '
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
    
    /** Converts field NOTE to aa array of explicit notes **/
    public static function expandNote($str){
        $res = [];
        if(strpos($str, '+') !== false){
            $res[] = 'Elected member of the French Academy of Medicine or Sciences';
        }
        if(strpos($str, '-') !== false){
            $res[] = 'Apparent lesser stature';
        }
        if(strpos($str, 'L') !== false){
            $res[] = 'Awarded "Compagnon de la libération"';
        }
        if(strpos($str, '*') !== false){
            $res[] = "Not taken from WHO'S WHO by Michel Gauquelin";
        }
    }
    
}// end class    

