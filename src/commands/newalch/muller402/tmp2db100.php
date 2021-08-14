<?php
/********************************************************************************
    Loads files data/tmp/newalch/muller-100-it-writers.csv and muller-100-it-writers-raw.csv in database.
    Affects records imported from A6
    
    @license    GPL
    @history    2020-09-21, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\muller\AFD;
use g5\commands\newalch\Newalch;
use g5\commands\gauquelin\LERRCP;

class tmp2db100 implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of perons already in Gauquelin data',
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
        
        $report = "--- Muller402 tmp2db100 ---\n";
        
        // source of muller-afd1-100-writers.txt - insert if does not already exist
        $source = Source::getBySlug(Muller100::LIST_SOURCE_SLUG); // DB
        if(is_null($source)){
            $source = new Source(Muller100::LIST_SOURCE_DEFINITION_FILE);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::getBySlug(Muller100::GROUP_SLUG); // DB
        if(is_null($g)){
            $g = Muller100::getGroup();
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
        $lines = Muller100::loadTmpFile();
        $linesRaw = Muller100::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            $slug = Person::doComputeSlug($line['FNAME'], $line['GNAME'], $line['DATE']);
            $test = Person::getBySlug($slug); // DB
            if(is_null($test)){
                // new person
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
                // OPUS, LEN not part of standard person fields
                // are stored in addHistory()
                self::addOccu($line, $p);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['MUID']);
                $mullerId = AFD::mullerId($source->data['slug'], $line['MUID']);
                $p->addIdInSource(AFD::SOURCE_SLUG, $mullerId);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("newalch muller402 tmp2db100", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $nInsert++;
                $p->data['id'] = $p->insert(); // DB
                $g->addMember($p->data['id']);
            }
            else{
                // person already in Gauquelin data
                $test->addSource($source->data['slug']);
                $test->addIdInSource($source->data['slug'], $line['MUID']);
                $mullerId = AFD::mullerId($source->data['slug'], $line['MUID']);
                $test->addIdInSource(AFD::SOURCE_SLUG, $mullerId);
                self::addOccu($line, $test);
                // TODO see if some fields can be updated (if Müller more precise than Gauquelin)
                $updatedValues = [];
                $test->addHistory("newalch muller402 tmp2db100", $source->data['slug'], $updatedValues);
                $test->addRaw($source->data['slug'], $lineRaw);
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
    
    
    private static function addOccu(&$line, $p) {
        $occu = match($line['OCCU']){
        	'1' => 'comedy-writer',  // 1 commediografo
        	'2',                     // 2 scrittore
        	'3',                     // 3 scrittore combined with other professions
        	'5' => 'writer',         // 5 sonstige, ie something other
        	'4' => 'poet',           // 4 poeta
        };
        $p->addOccus([$occu]);
    }
        
}// end class    

