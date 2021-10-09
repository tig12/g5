<?php
/********************************************************************************
    Loads files data/tmp/cura/D10.csv and D10-raw.csv in database.
    
    @license    GPL
    @history    2020-08-19 23:55:18+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\D10;

use tiglib\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\model\Occupation;
use g5\commands\gauq\LERRCP;
use g5\commands\cura\Cura;

class tmp2db implements Command {
                                                                                          
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Echoes the details of duplicate entries',
    ];
    
    /**
        @param  $params Array containing 3 elements :
                        - "D10"
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
        
        $datafile = 'D10';
        
        $report = "--- $datafile tmp2db ---\n";
        
        // source corresponding to LERRCP
        // not inserted because must have been done in A1 import
        $lerrcpSource = new Source(LERRCP::SOURCE_DEFINITION_FILE);
        
        // source corresponding LERRCP booklet of D6 file
        $source = Source::getBySlug(LERRCP::datafile2bookletSourceSlug($datafile)); // DB
        if(is_null($source)){
            $source = LERRCP::getBookletSourceOfDatafile($datafile);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // source corresponding to D6 file
        $source = Source::getBySlug(strtolower($datafile)); // DB
        if(is_null($source)){
            $source = LERRCP::getSourceOfDatafile($datafile);
            $source->insert(); // DB
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::getBySlug(LERRCP::datafile2groupSlug($datafile)); // DB
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
        $lines = Cura::loadTmpFile($datafile);
        $linesRaw = Cura::loadTmpRawFile($datafile);
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
            $p = Person::getBySlug($test->data['slug']); // DB
            if(is_null($p)){
                // insert new person
                $p = new Person();
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NUM']);
                $p->addIdInSource($lerrcpSource->data['slug'], $gqId);
                $new = [];
                $new['trust'] = Cura::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                if($line['C_APP'] != ''){
                    $new['notes'] = [];
                    $new['notes'] = [
                        'Value published in LERRCP corrected in APP',
                    ];
                }
                $p->updateFields($new);
                $p->addOccus($newOccus);
                $p->computeSlug();
                // repeat fields to include in $history
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [
                    $source->data['slug'] => $line['NUM'],
                    $lerrcpSource->data['slug'] => $gqId,
                ];
                $new['occus'] = $newOccus;
                $p->addHistory(
                    command: "cura $datafile tmp2db",
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $p->data['id'] = $p->insert(); // DB
                $nInsert++;
            }
            else{
                // duplicate, person appears in more than one cura file
                $p->addOccus($newOccus);
                $p->addSource($source->data['slug']);
                // does not addIdInSource(lerrcp) to respect the definition of Gauquelin id:
                // lerrcp id takes the value of the first volume where it appears.
                // lerrcp id already affected in a previous file for this record.
                $p->addIdInSource($source->data['slug'], $line['NUM']);
                // repeat fields to include in $history
                $new = [];
                $new['sources'] = $source->data['slug'];
                $new['ids_in_sources'] = [
                    $source->data['slug'] => $line['NUM'],
                ];
                $new['occus'] = $newOccus;
                $p->addHistory(
                    command: "cura $datafile tmp2db",
                    sourceSlug: $source->data['slug'],
                    newdata: $new,
                    rawdata: $lineRaw
                );
                $p->update(); // DB
                if($reportType == 'full'){
                    $report .= "Duplicate "
                    . $test->data['slug'] . " : "
                    . $p->data['ids-in-sources'][Cura::SOURCE_SLUG]
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
        
}// end class    

