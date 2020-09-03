<?php
/********************************************************************************
    Loads files data/tmp/cura/A*.csv and A-raw.csv in database.
    Must be exectued in alphabetical order (first A1, then A2 ... A6)
    to respect the defition of Gauquelin id (see https://tig12.github.io/gauquelin5/cura.html)
    
    @license    GPL
    @history    2020-08-19 05:23:25+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\A;

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
        
        $report = "--- $datafile tmp2db ---\n";
        
        // source corresponding to CURA - insert if does not already exist
        $curaSource = Cura::getSource();
        try{
            $curaSource->insert();
        }
        catch(\Exception $e){
            // already inserted, do nothing
        }
        
        // source corresponding to current A file
        $source = Source::getBySlug($datafile);
        if(is_null($source)){
            $source = new Source();
            $source->data['slug'] = $datafile; // ex A1
            $source->data['name'] = "CURA file $datafile";
            $source->data['description'] = Cura::CURA_URLS[$datafile] . "\nDescribed by Cura as " . Cura::CURA_CLAIMS[$datafile][2];
            $source->data['source']['parents'][] = $curaSource->data['slug'];
            $source->data['id'] = $source->insert();
        }
        
        // group
        $g = Group::getBySlug($datafile);
        if(is_null($g)){
            $g = new Group();
            $g->data['slug'] = $datafile;
            $g->data['sources'][] = $source->data['slug'];
            $g->data['name'] = "Cura $datafile";
            $g->data['description'] = "According to Cura : " . Cura::CURA_CLAIMS[$datafile][2] . ".\n"
                . "In practice, contains " . Cura::CURA_CLAIMS[$datafile][1] . " persons.";
            $g->data['id'] = $g->insert();
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
            $test->data['birth']['date-ut'] = $line['DATE-UT'];
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
                $new['occus'] = [$line['OCCU']];
                $new['birth'] = [];
                $new['birth']['date-ut'] = $line['DATE-UT'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['c3'] = $line['C3'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("cura $datafile tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $p->data['id'] = $p->insert(); // Storage
                $nInsert++;
            }
            else{
                // duplicate, person appears in more than one cura file
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
            $g->data['id'] = $g->insert(); // Storage
        }
        catch(\Exception $e){
            // group already exists
            $g->insertMembers();
        }
        $dt = $t2 - $t1;
        if($reportType == 'full' && $nDuplicates != 0){
            $report .= "-------\n";
        }
        $report .= "$nInsert persons inserted, $nDuplicates updated ($dt s)\n";
        return $report;
    }
        
}// end class    
