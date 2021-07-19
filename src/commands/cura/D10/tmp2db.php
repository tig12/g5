<?php
/********************************************************************************
    Loads files data/tmp/cura/D10.csv and D10-raw.csv in database.
    
    @license    GPL
    @history    2020-08-19 23:55:18+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\D10;

use g5\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\cura\Cura;

class tmp2db implements Command {
                                                                                          
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Echoes the details of duplicate entries',
    ];
    
    // *****************************************
    // Implementation of Command
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
        
        // source corresponding to CURA
        // not inserted because must have been done in A1 import
        $curaSource = new Source(Cura::SOURCE_DEFINITION_FILE);
        
        // source corresponding LERRCP booklet of D6 file
        $source = Source::getBySlug(Cura::datafile2bookletSourceSlug($datafile));
        if(is_null($source)){
            $source = Cura::getBookletSourceOfFile($datafile);
            $source->insert();
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // source corresponding to D6 file
        $source = Source::getBySlug(strtolower($datafile));
        if(is_null($source)){
            $source = Cura::getSourceOfFile($datafile);
            $source->insert();
            $report .= "Inserted source " . $source->data['slug'] . "\n";
        }
        
        // group
        $g = Group::getBySlug($datafile);
        if(is_null($g)){
            $g = Cura::getGroupOfFile($datafile);
        }
        else{
            $g->deleteMembers(); // only deletes asssociations between group and members
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
            $curaId = Cura::gqId($datafile, $line['NUM']);
            $p = Person::getBySlug($test->data['slug']);
            if(is_null($p)){
                // insert new person
                $p = new Person();
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NUM']);
                $p->addIdInSource($curaSource->data['slug'], $curaId);
                $new = [];
                $new['trust'] = Cura::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['occus'] = explode('+', $line['OCCU']);
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
                $p->computeSlug();
                $p->addHistory("cura $datafile tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $p->data['id'] = $p->insert(); // Storage
                $nInsert++;
            }
            else{
                // duplicate, person appears in more than one cura file
                foreach(explode('+', $line['OCCU']) as $occu){
                    $p->addOccu($occu);
                }
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NUM']);
                // does not addIdInSource($curaSource) to respect the definition of Gauquelin id
                $p->update(); // Storage
                if($reportType == 'full'){
                    $report .= "Duplicate {$test->data['slug']} : {$p->data['ids-in-sources']['cura']} = $curaId\n";
                }
                $nDuplicates++;
            }
            $g->addMember($p->data['id']);
        }
        $t2 = microtime(true);
        try{
            $g->data['id'] = $g->insert(); // HERE storage
        }
        catch(\Exception $e){
            // group already exists
            $g->insertMembers();
        }
        $dt = round($t2 - $t1, 5);
        if($reportType == 'full' && $nDuplicates != 0){
            $report .= "-------\n";
        }
        $report .= "$nInsert persons inserted, $nDuplicates updated ($dt s)\n";
        return $report;
    }
        
}// end class    

