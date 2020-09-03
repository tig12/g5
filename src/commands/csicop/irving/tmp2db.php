<?php
/********************************************************************************
    Loads files data/tmp/newalch/muller-402-it-writers.csv and muller-402-it-writers-raw.csv in database.
    Affects records imported from A1
    
    @license    GPL
    @history    2020-09-02 00:36:52+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\csicop\irving;

use g5\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\newalch\Newalch;
use g5\commands\cura\Cura;

class tmp2db implements Command {
    
    // *****************************************
    // Implementation of Command
    /**
        @param  $params Empty array
    **/
    public static function execute($params=[]): string {
        if(count($params) > 0){
            return "USELESS PARAMETER : " . $params[0] . "\n";
        }

        $report = "--- csicop irving tmp2db ---\n";
        
        // source corresponding to rawlins-ertel-data.csv - insert if does not already exist
        $source = Irving::getSource();
        try{
            $source->insert();
        }
        catch(\Exception $e){
            // already inserted, do nothing
        }
        
        // group
        $g = Group::getBySlug(Irving::GROUP_SLUG);
        if(is_null($g)){
            $g = new Group();
            $g->data['slug'] = Irving::GROUP_SLUG;
            $g->data['sources'][] = $source->data['slug'];
            $g->data['name'] = "CSICOP";
            $g->data['description'] = "CSICOP";
            $g->data['id'] = $g->insert();
        }
        else{
            $g->deleteMembers(); // only deletes asssociations between group and members
        }
        
        $nInsert = 0;
        $nUpdate = 0;
        $nDiffDates = 0;
        // both arrays share the same order of elements,
        // so they can be iterated in a single loop
        $lines = Irving::loadTmpFile();
        $linesRaw = Irving::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            if($line['GQID'] == ''){
                // Person not in Gauquelin data
                $p = new Person();
                $new = [];
                $new['trust'] = Irving::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = $line['CY'];
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                //
                $p->addOccu($line['SPORT']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['CSID']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("newalch irving tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $nInsert++;
                $p->data['id'] = $p->insert(); // Storage
            }
            else{
                // Person already in D10
                $new = [];
                $new['notes'] = [];
                [$curaFile, $NUM] = explode('-', $line['GQID']);
                $curaId = Cura::gqid($curaFile, $NUM);
                $p = Person::getBySourceId($curaFile, $NUM);
                if(is_null($p)){
                    throw new \Exception("$curaId : try to update an unexisting person");
                }
                // if Cura and csicop have different birth day
                $csiday = substr($line['DATE'], 0, 10);
                $curaday = substr($p->data['birth']['date'], 0, 10);
                if($csiday != $curaday){
                    $nDiffDates++;
                    $new['to-check'] = true;
                    $new['notes'][] = "TO CHECK - Cura D10 and Csicop Irving have different birth day";
                }
                $p->addOccu($line['SPORT']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['CSID']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("cura irving tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);                 
                $nUpdate++;
                $p->update(); // Storage
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
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        $report .= "$nDiffDates dates differ from D10\n";
        return $report;
    }
        
}// end class    

