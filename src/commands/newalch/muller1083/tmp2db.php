<?php
/********************************************************************************
    Loads files data/tmp/newalch/1083MED.csv and 1083MED-raw.csv in database.
    Affects records imported in A2 and E1
    
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
use g5\commands\cura\Cura;

class tmp2db implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes the number of inserted / updated rows',
        'full'  => 'Lists details of names and dates restoration on A2 or E1',
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
        
        $report = "--- Muller1083 tmp2db ---\n";
        
        if($reportType == 'full'){
            $namesReport = '';
            $datesReport = '';
        }
                                             
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
        $t1 = microtime(true);
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
                $new['name']['nobility'] = $line['NOB'];
                // Müller name considered as = to full name copied from birth certificate
                $new['name']['official']['given'] = $line['GNAME'];
                $new['occus'] = ['PH']; // TODO put wikidata code
                $new['birth'] = [];
                $new['birth']['date'] = $line['DATE'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['birth']['place']['c2'] = $line['C2'];
                $new['birth']['place']['cy'] = 'FR';
                $new['birth']['place']['lg'] = $line['LG'];
                $new['birth']['place']['lat'] = $line['LAT'];
                //
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("cura muller1083 tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $nInsert++;
continue;
                $p->data['id'] = $p->insert(); // Storage
            }
            else{
                // Person already in A2 or E1
                $new = [];
                $new['notes'] = [];
                [$curaFile, $NUM] = Muller1083::gnr2cura($line['GNR']);
                $curaId = Cura::gqid($curaFile, $NUM);
                $p = Person::getBySourceId($curaFile, $NUM);
                if(is_null($p)){
                    throw new \Exception("$curaId : try to update an unexisting person");
                }
                if($p->data['name']['family'] == "Gauquelin-$curaId"){
                    $nRestoredNames++;
                    $namesReport .= "\nCura NUM $curaId\t {$p->data['name']['family']}\n";
                    $namesReport .= "Müller NR {$line['NR']}\t {$line['FNAME']} - {$line['GNAME']}\n";
                }
                // if Cura and Müller have different birth day
                $mulday = substr($line['DATE'], 0, 10);
                // from A2, stored in field 'date-ut' ; from E1, stored in field 'date'
                if(isset($p->data['ids_in_sources']['A2'])){
                    $curaday = substr($p->data['birth']['date-ut'], 0, 10);
                }
                else{
                    $curaday = substr($p->data['birth']['date'], 0, 10);
                }
                if($mulday != $curaday){
                    $nDiffDates++;
                    $new['notes'][] = "TO CHECK - Cura and Müller have different birth day";
                    $datesReport .= "\nCura $curaId\t $curaday {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                    $datesReport .= "Müller NR {$line['NR']}\t $mulday {$line['FNAME']} - {$line['GNAME']}\n";
// echo "\n"; print_r($line); echo "\n";
// echo "\n"; print_r($p); echo "\n";
// echo "mulday $mulday\n";
// echo "curaday $curaday\n";
// echo "$datesReport\n";
// exit;
                }
                // update fields that are more precise in muller1083
                $new['birth']['date'] = $line['DATE']; // Cura contains only date-ut
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['name']['nobility'] = $line['NOB'];
                $new['name']['family'] = $line['FNAME'];
                if($p->data['name']['given'] == ''){
                    // happens with names like Gauquelin-A1-258
                    $new['name']['given'] = $line['GNAME'];
                }
                // Müller name considered as = to full name copied from birth certificate
                $new['name']['official']['given'] = $line['GNAME'];
                //
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['NR']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("cura muller1083 tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);                 
                $nUpdate++;
continue;
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
        if($reportType == 'full'){
            //$report .= "=== Names fixed ===\n" . $namesReport;
            $report .= "\n=== Dates fixed ===\n" . $datesReport;
            $report .= "============\n";
        }
        $report .= "Inserted $nInsert, updated $nUpdate persons in database ($dt s)\n";
        $report .= "$nDiffDates different dates - $nRestoredNames names restored in A2\n";
        return $report;
    }
        
}// end class    

