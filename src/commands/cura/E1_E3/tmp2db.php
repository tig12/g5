<?php
/********************************************************************************
    Loads files data/tmp/cura/E1.csv and E1-raw.csv in database.
    
    @license    GPL
    @history    2020-08-20 07:47:13+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\E1_E3;

use g5\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\cura\Cura;

class tmp2db implements Command {
                                                                                          
    // *****************************************
    // Implementation of Command
    /**
        @param  $params Array containing 2 elements :
                        - "E1" or "E3" 
                        - "tmp2db" (useless here)
    **/
    public static function execute($params=[]): string {
        if(count($params) > 2){
            return "USELESS PARAMETER : " . $params[2] . "\n";
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
        
        // source corresponding to E1 / E3 file
        $source = Source::getBySlug($datafile);
        if($source->isEmpty()){
            $source = new Source();
            $source->data['slug'] = $datafile;
            $source->data['name'] = "CURA file $datafile";
            $source->data['description'] = Cura::CURA_URLS[$datafile] . "\nDescribed by Cura as " . Cura::CURA_CLAIMS[$datafile][2];
            $source->data['source']['parents'][] = $curaSource->data['slug'];
            $source->data['id'] = $source->insert();
        }
        
        // group
        $g = Group::getBySlug($datafile);
        if($g->isEmpty()){
            $g = new Group();
            $g->data['slug'] = $datafile;
            $g->data['sources'][] = $source->data['slug'];
            $g->data['name'] = "Cura $datafile";
            $g->data['description'] = "According to Cura : " . Cura::CURA_CLAIMS[$datafile][2] . ".\n"
                . "In practice, contains " . Cura::CURA_CLAIMS[$datafile][1] . " persons.";
            $g->data['id'] = $g->insert();
        }
        else{
            // because Cura is imported before any other data
            $g->deleteMembers();
        }
        
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = Cura::loadTmpFile($datafile);
        $linesRaw = Cura::loadTmpRawFile($datafile);
        $nStored = 0;
        $N = count($lines);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            // Here build an empty person because cura data are the first to be imported
            $p = new Person();
            $p->addSource($source->data['slug']);
            $p->addIdInSource($source->data['slug'], $line['NUM']);
            // here, do not modify directly $p->data to permit a call to addHistory()
            // containing only new data
            $new = [];
            $new['trust'] = Cura::TRUST_LEVEL;
            $new['name']['family'] = $line['FNAME'];
            $new['name']['given'] = $line['GNAME'];
            $new['occus'] = explode('+', $line['OCCU']);
            $new['notes'] = self::expandNote($line['NOTE']);
            $new['birth'] = [];
            $new['birth']['date'] = $line['DATE'];
            $new['birth']['tzo'] = $line['TZO'];
            $new['birth']['place']['name'] = $line['PLACE'];
            $new['birth']['place']['c2'] = $line['C2'];
            $new['birth']['place']['c3'] = $line['C3'];
            $new['birth']['place']['cy'] = $line['CY'];
            $new['birth']['place']['lg'] = $line['LG'];
            $new['birth']['place']['lat'] = $line['LAT'];
            $new['birth']['place']['geoid'] = $line['GEOID'];
            $p->updateFields($new);
            $p->computeSlug();
            // log command effect on data in the person yaml         
            $p->addHistory("cura $datafile tmp2db", $source->data['slug'], $new);
            $p->addRaw($source->data['slug'], $lineRaw);
            try{
                $p->data['id'] = $p->insert(); // HERE storage
            }
            catch(\Exception $e){
                $p->update();
                $p->data['id'] = $p->getIdFromSlug($p->data['slug']);
            }
            $nStored ++;
            $g->addMember($p->data['id']);
        }
        try{
            $g->data['id'] = $g->insert(); // HERE storage
        }
        catch(\Exception $e){
            // group already exists
            $g->insertMembers();
        }
        $report .= "Stored $nStored persons in database\n";
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

