<?php
/********************************************************************************
    Loads A files in database files in data/tmp/cura
    
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
    
    // *****************************************
    // Implementation of Command
    /**
        @param $params  Empty array
        @param  $params Array containing 2 elements :
                        - a string identifying what is processed (ex : 'A1')
                        - "tmp2db" (useless here)
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 2){
            return "USELESS PARAMETER : " . $params[2] . "\n";
        }
        $datafile = $params[0];
        $report = "--- tmp2db ---\n";
        
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
        if($source->isEmpty()){
            $source = new Source();
            $source->data['slug'] = $datafile; // ex A1
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
        
        $lines = Cura::loadTmpFile($datafile);
        foreach($lines as $cur){
print_r($cur);
            // Here build an empty person because cura data are the first to be imported
            $p = new Person();
            $p->addSource($source->data['slug']);
            $p->addRaw($datafile, $cur);
            $p->addIdInSource($curaSource->data['slug'], Cura::gqId($datafile, $cur['NUM']));
            $p->addIdInSource($source->data['slug'], $cur['NUM']);
            // here, do not modify directly $p->data to permit a call to addHistory()
            // containing only new data
            $new = [];
            $new['trust'] = Cura::TRUST_LEVEL;
            $new['name']['family'] = $cur['FNAME'];
            $new['name']['given'] = $cur['GNAME'];
            $new['occu'] = [$cur['OCCU']];
            $new['birth'] = [];
            $new['birth']['date'] = $cur['DATE'];
            $new['birth']['date-ut'] = $cur['DATE-UT'];
            $new['birth']['tzo'] = $cur['TZO'];
            $new['birth']['place']['name'] = $cur['PLACE'];
            $new['birth']['place']['c2'] = $cur['C2'];
            $new['birth']['place']['c3'] = $cur['C3'];
            $new['birth']['place']['cy'] = $cur['CY'];
            $new['birth']['place']['lg'] = $cur['LG'];
            $new['birth']['place']['lat'] = $cur['LAT'];
echo "\n<pre>"; print_r($new); echo "</pre>\n"; exit;
            $p->updateFields($new);
            $p->computeSlug();
            // log command effect on data in the person yaml
            $p->addHistory("cura $datafile tmp2db", $datafile, $new);
echo "\n<pre>"; print_r($p); echo "</pre>\n"; exit;
            $p->data['id'] = Person::insert($p); // HERE storage
            $nb_stored ++;
            $g->addMember($p->data['id']);
        }
        
        return $report;
    }
        
}// end class    

