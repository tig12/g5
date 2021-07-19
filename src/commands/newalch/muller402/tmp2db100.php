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
use g5\commands\newalch\Newalch;
use g5\commands\cura\Cura;

class tmp2db100 implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of perons already in Gauquelin data',
    ];
    
    // *****************************************
    // Implementation of Command
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
        $source = Source::getBySlug(Muller100::LIST_SOURCE_SLUG);
        if(is_null($source)){
            $source = new Source(Muller100::LIST_SOURCE_DEFINITION_FILE);
            $source->insert();
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::getBySlug(Muller100::GROUP_SLUG);
        if(is_null($g)){
            $g = Muller100::getGroup();
        }
        else{
            $g->deleteMembers(); // only deletes asssociations between group and members
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
            $test = Person::getBySlug($slug);
            if(is_null($test)){
            //if(true){
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
                // OCCU, OPUS, LEN not part of standard person fields
                // are stored in addHistory()
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['MUID']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("newalch muller402 tmp2db100", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $nInsert++;
                $p->data['id'] = $p->insert(); // Storage
                $g->addMember($p->data['id']);
            }
            else{
                // person already in Gauquelin data
                $test->addSource($source->data['slug']);
                $test->addIdInSource($source->data['slug'], $line['MUID']);
                // TODO see if some fields can be updated (if Müller more precise than Gauquelin)
                $updatedValues = [];
                $test->addHistory("newalch muller402 tmp2db100", $source->data['slug'], $updatedValues);
                $test->addRaw($source->data['slug'], $lineRaw);
                if($reportType == 'full'){
                    $gqid = $test->data['ids-in-sources']['cura'];
                    $report .= "Müller {$line['MUID']} = $gqid - $slug\n";
                }
                $nUpdate++;
                $test->update(); // Storage
                $g->addMember($test->data['id']);
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
        $dt = round($t2 - $t1, 5);
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        return $report;
    }
        
}// end class    

