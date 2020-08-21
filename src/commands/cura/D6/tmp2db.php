<?php
/********************************************************************************
    Loads files data/tmp/cura/D6.csv and D6-raw.csv in database.
    
    @license    GPL
    @history    2020-08-19 18:16:35+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\D6;

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
                        - "D6" (useless here)
                        - "tmp2db" (useless here)
    **/
    public static function execute($params=[]): string {
        if(count($params) > 2){
            return "USELESS PARAMETER : " . $params[2] . "\n";
        }
        $datafile = 'D6';
        
        $report = "--- $datafile tmp2db ---\n";
        
        // source corresponding to CURA - insert if does not already exist
        $curaSource = Cura::getSource();
        try{
            $curaSource->insert();
        }
        catch(\Exception $e){
            // already inserted, do nothing
        }
        
        // source corresponding to D6 file
        $source = Source::getBySlug($datafile);
        if(is_null($source)){
            $source = new Source();
            $source->data['slug'] = $datafile;
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
        $nStored = 0;
        $N = count($lines);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            // Here build an empty person because cura data are the first to be imported
            $p = new Person();
            $p->addSource($source->data['slug']);
            $p->addIdInSource($curaSource->data['slug'], Cura::gqId($datafile, $line['NUM']));
            $p->addIdInSource($source->data['slug'], $line['NUM']);
            $new = [];
            $new['trust'] = Cura::TRUST_LEVEL;
            $new['name']['family'] = $line['FNAME'];
            $new['name']['given'] = $line['GNAME'];
            $new['occus'] = ['SP']; // sportsman - TODO change with geoid code
            $new['birth'] = [];
            $new['birth']['date'] = $line['DATE'];
            $new['birth']['place']['cy'] = $line['CY'];
            $new['birth']['place']['lg'] = $line['LG'];
            $new['birth']['place']['lat'] = $line['LAT'];
            $p->updateFields($new);
            $p->computeSlug();
            $p->addHistory("cura $datafile tmp2db", $source->data['slug'], $new);
            $p->addRaw($source->data['slug'], $lineRaw);
            try{
                $p->data['id'] = $p->insert();
            }
            catch(\Exception $e){
                $p->data['id'] = $p->getIdFromSlug($p->data['slug']);
                $p->update();
            }
            $nStored ++;
            $g->addMember($p->data['id']);
        }
        try{
            $g->data['id'] = $g->insert();
        }
        catch(\Exception $e){
            // group already exists
            $g->insertMembers();
        }
        $report .= "Stored $nStored persons in database\n";
        return $report;
    }
        
}// end class    

