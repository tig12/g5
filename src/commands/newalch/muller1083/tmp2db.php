<?php
/********************************************************************************
    Loads files data/tmp/newalch/1083MED.csv and 1083MED-raw.csv in database.
    
    @license    GPL
    @history    2020-08-20 10:46:02+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\newalch\muller1083;

use g5\patterns\Command;
use g5\DB5;
use g5\model\Source;
use g5\model\Group;
use g5\model\Person;
use g5\commands\newalch\Newalch;

class tmp2db implements Command {
                                                                                          
    // *****************************************
    // Implementation of Command
    /**
        @param  $params empty array
    **/
    public static function execute($params=[]): string {
        if(count($params) > 2){
            return "USELESS PARAMETER : " . $params[2] . "\n";
        }
        
        $report = "--- D10 tmp2db ---\n";
                                             
        // source corresponding to 5a_muller_medics - insert if does not already exist
        $source = Muller1083::getSource();
        try{
            $source->insert();
        }
        catch(\Exception $e){
            // already inserted, do nothing
        }
        
        // group
        $g = Group::getBySlug(Muller1083::GROUP_SLUG);
        if(is_null($g)){
            $g = new Group();
            $g->data['slug'] = Muller1083::GROUP_SLUG;
            $g->data['sources'][] = $source->data['slug'];
            $g->data['name'] = "Müller 1083 physicians";
            $g->data['description'] = "1083 physisicans of French Académie de médecine, gathered by Arno Müller";
            $g->data['id'] = $g->insert();
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
        $lines = Muller1083::loadTmpFile();
        $linesRaw = Muller1083::loadTmpRawFile();
        $N = count($lines);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];                                                                        
            $lineRaw = $linesRaw[$i];
            if($line['GNR'] == ''){
                // Person not in Gauquelin data
                $p = new Person();
                $new = [];
                $new['trust'] = Newalch::TRUST_LEVEL;
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $new['occus'] = ['PH']; // TODO put wikidata code
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = 'FR';
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                $nInsert++;
            }
            else{
                // Person already in A2 or E1
                $new = [];
                $new['notes'] = [];
                $curaId = Muller1083::gnr2cura($line['GNR']);
                $p = Person::getBySourceId('cura', $curaId);
                if(is_null($p)){
                    throw new \Exception("$curaId : try to update an unexisting person");
                }
                if($p->data['name']['family'] == "Gauquelin-$curaId"){
                    $nRestoredNames++;
                }
                // if Cura and Müller have different birth day
                $mulday = substr($line['DATE'], 0, 10);
                $curaday = substr($p->data['birth']['date'], 0, 10);
                if($mulday != $curaday){
                    $nDiffDates++;
                    $new['notes'][] = "TO CHECK - Cura and Müller have different birth day";
                }
                // update fields that are more precise in muller1083
                $new['birth']['date'] = $line['DATE']; // Cura contains only date-ut
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['name']['nobility'] = $line['NOB'];
                $new['name']['family'] = $line['FNAME'];
                // Consider Gauquelin gname as usual given name
                if($p->data['name']['given'] == ''){
                    // happens with names like Gauquelin-A1-258
                    $new['name']['given'] = $line['GNAME'];
                }
                // And Muller name as full name copied from birth act
                $new['name']['official']['given'] = $line['GNAME'];
                $nUpdate++;
            }
            
            $p->addSource($source->data['slug']);
            $p->addIdInSource($source->data['slug'], $line['NR']);
            $p->updateFields($new);
            $p->computeSlug();
            $p->addHistory("cura muller1083 tmp2db", $source->data['slug'], $new);
            $p->addRaw($source->data['slug'], $lineRaw);                 
            
            if($line['GNR'] == ''){
                $p->data['id'] = $p->insert();
            }
            else{
                $p->update();
            }
            $g->addMember($p->data['id']);
        }
        try{
            $g->data['id'] = $g->insert();
        }
        catch(\Exception $e){
            // group already exists
            $g->insertMembers();
        }
        $report .= "Inserted $nInsert, updated $nUpdate persons in database\n";
        $report .= "$nDiffDates different dates - $nRestoredNames names restored in A2\n";
        return $report;
    }
        
}// end class    

