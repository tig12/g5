<?php
/********************************************************************************
    Loads files data/tmp/muller/1-writers/muller1-100-writers.csv and muller1-100-writers-raw.csv in database.
    Affects records imported from A6
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2020-09-21, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\muller\m1writers;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\model\wiki\Issue;
use g5\model\wiki\Wikiproject;
use g5\commands\muller\Muller;
use g5\commands\Newalch;
use g5\commands\gauq\LERRCP;

class tmp2db100 implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of persons already in Gauquelin data',
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
        
        $report = "--- muller m1writers tmp2db100 ---\n";
        
        // source of muller1-100-writers.txt - insert if does not already exist
        $source = Source::createFromSlug(M1writers100::LIST_SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(M1writers100::LIST_SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::createFromSlug(M1writers100::GROUP_SLUG); // DB
        if(is_null($g)){
            $g = M1writers100::getGroup();
            $g->data['id'] = $g->insert(); // DB
            $report .= "Inserted group " . $g->data['slug'] . "\n";
        }
        else{
            $g->deleteMembers(); // DB - only deletes asssociations between group and members
        }
        
        // Wiki projects associated to the issues raised by this import
        $wp = Wikiproject::createFromSlug('italian-writers');
        $issue_msg = "Arno Müller couldn't obtain birth time. Check if birth certificate with birth time can be found.";
        
        $nInsert = 0;
        $nUpdate = 0;
        $nRestoredNames = 0;
        $nDiffDates = 0;
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = M1writers100::loadTmpFile();
        $linesRaw = M1writers100::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            $slug = Person::doComputeSlug($line['FNAME'], $line['GNAME'], $line['DATE']);
            $test = Person::createFromSlug($slug); // DB
            if(is_null($test)){
                // new person
                $p = new Person();
                $new = [];
                $new['trust'] = Newalch::TRUST_LEVEL;
                if($line['GNAME'] != ''){
                    $new['name']['family'] = $line['FNAME'];
                    $new['name']['given'] = $line['GNAME'];
                }
                else{
                    $new['name']['full'] = $line['FNAME'];
                }
                $new['sex'] = $line['SEX'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['note'] = $line['LMT'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = (float)$line['LG'];
                $new['birth']['place']['lat'] = (float)$line['LAT'];
                // OPUS, LEN not part of standard person fields
                // are stored in addHistory()
                $occu = self::computeOccu($line);
                $p->addOccus([$occu]); // table person_groop handled by command db/init/occu2 - Group::storePersonInGroup() not called here
                $p->addIdInSource($source->data['slug'], $line['MUID']);
                $mullerId = Muller::mullerId($source->data['slug'], $line['MUID']);
                $p->addPartialId(Muller::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['ids-in-sources'] = [
                    $source->data['slug'] => $line['MUID'],
                ];
                $new['occus'] = [$occu];
                //
                $p->addHistory(
                    command: 'muller m1writers tmp2db100',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
                $g->addMember($p->data['id']);
                // Issue
                $issue = new Issue($p, Issue::TYPE_DATE, $issue_msg);
                $test = $issue->insert(); // DB
                if($test != -1){
                    $issue->linkToWikiproject($wp);
                }
            }
            else{
                // person already in Gauquelin data
                $test->addIdInSource($source->data['slug'], $line['MUID']);
                $mullerId = Muller::mullerId($source->data['slug'], $line['MUID']);
                $test->addPartialId(Muller::SOURCE_SLUG, $mullerId);
                $occu = self::computeOccu($line);
                $p->addOccus([$occu]); // table person_groop handled by command db/init/occu2 - Group::storePersonInGroup() not called here
                // TODO see if some fields can be updated (if Müller more precise than Gauquelin)
                $new = [];
                // repeat fields to include in $history
                $new['ids-in-sources'] = [
                    $source->data['slug'] => $line['MUID'],
                ];
                $new['occus'] = [$occu];
                $p->addHistory(
                    command: 'muller m1writers tmp2db100',
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                if($reportType == 'full'){
                    $gqid = $test->data['ids-in-sources'][LERRCP::SOURCE_SLUG];
                    $report .= "Müller {$line['MUID']} = $gqid - $slug\n";
                }
                $nUpdate++;
                $test->update(); // DB
                $g->addMember($test->data['id']);
            }
        }
        $t2 = microtime(true);
        $g->insertMembers(); // DB
        $dt = round($t2 - $t1, 5);
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        return $report;
    }
    
    
    private static function computeOccu(&$line) {
        $occu = match($line['OCCU']){
        	'1' => 'comedy-writer',  // 1 commediografo
        	'2',                     // 2 scrittore
        	'3',                     // 3 scrittore combined with other professions
        	'5' => 'writer',         // 5 sonstige, ie something other
        	'4' => 'poet',           // 4 poeta
        };
        return $occu;
    }
        
}// end class    

