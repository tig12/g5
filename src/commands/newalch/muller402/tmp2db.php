<?php
/********************************************************************************
    Loads files data/tmp/newalch/muller-402-it-writers.csv and muller-402-it-writers-raw.csv in database.
    Affects records imported from A6
    
    @license    GPL
    @history    2020-08-25 18:18:41+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

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
        'full'  => 'Lists details of names and dates restoration on A6',
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
        
        $report = "--- Muller402 tmp2db ---\n";
        
        if($reportType == 'full'){
            $namesReport = '';
            $datesReport = '';
        }
                                             
        // source corresponding to 5a_muller_medics - insert if does not already exist
        $source = Muller402::getSource();
        try{
            $source->insert();
        }
        catch(\Exception $e){
            // already inserted, do nothing
        }
        
        // group
        $g = Group::getBySlug(Muller402::GROUP_SLUG);
        if(is_null($g)){
            $g = new Group();
            $g->data['slug'] = Muller402::GROUP_SLUG;
            $g->data['sources'][] = $source->data['slug'];
            $g->data['name'] = "Müller 402 Italian writers";
            $g->data['description'] = "402 Italian writers, gathered by Arno Müller";
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
        $lines = Muller402::loadTmpFile();
        $linesRaw = Muller402::loadTmpRawFile();
        $N = count($lines);
        $t1 = microtime(true);
        for($i=0; $i < $N; $i++){
            $line = $lines[$i];
            $lineRaw = $linesRaw[$i];
            if($line['GQID'] == ''){
                // Person not in Gauquelin data
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
                //
                $p->addOccu($line['OCCU']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['MUID']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("newalch muller402 tmp2db", $source->data['slug'], $new);
                $p->addRaw($source->data['slug'], $lineRaw);
                $nInsert++;
                $p->data['id'] = $p->insert(); // Storage
            }
            else{
                // Person already in A6
                $new = [];
                $new['notes'] = [];
                [$curaFile, $NUM] = explode('-', $line['GQID']);
                $curaId = Cura::gqid($curaFile, $NUM);
                $p = Person::getBySourceId($curaFile, $NUM);
                if(is_null($p)){
                    throw new \Exception("$curaId : try to update an unexisting person");
                }
                if($p->data['name']['family'] == "Gauquelin-$curaId"){
                    $nRestoredNames++;
                    if($reportType == 'full'){
                        $namesReport .= "\nCura\t $curaId\t {$p->data['name']['family']}\n";
                        $namesReport .= "Müller\t {$line['MUID']}\t {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                // if Cura and Müller have different birth day
                $mulday = substr($line['DATE'], 0, 10);
                // in E6, stored in field 'date-ut'
                $curaday = substr($p->data['birth']['date-ut'], 0, 10);
                if($mulday != $curaday){
                    $nDiffDates++;
                    $new['to-check'] = true;
                    $new['notes'][] = "CHECK: birth day - $curaId $curaday / Muller402 {$line['MUID']} $mulday";
                    if($reportType == 'full'){
                        $datesReport .= "\nCura\t $curaId\t $curaday {$p->data['name']['family']} - {$p->data['name']['given']}\n";
                        $datesReport .= "Müller\t {$line['MUID']}\t $mulday {$line['FNAME']} - {$line['GNAME']}\n";
                    }
                }
                // update fields that are more precise in muller402
                $new['birth']['date'] = $line['DATE']; // Cura contains only date-ut
                $new['birth']['tzo'] = $line['TZO'];
                $new['birth']['note'] = $line['LMT'];
                $new['birth']['place']['name'] = $line['PLACE'];
                $new['name']['family'] = $line['FNAME'];
                $new['name']['given'] = $line['GNAME'];
                $p->addOccu($line['OCCU']);
                $p->addSource($source->data['slug']);
                $p->addIdInSource($source->data['slug'], $line['MUID']);
                $p->updateFields($new);
                $p->computeSlug();
                $p->addHistory("cura muller402 tmp2db", $source->data['slug'], $new);
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
        if($reportType == 'full'){
            $report .= "=== Names fixed ===\n" . $namesReport;
            $report .= "\n=== Dates fixed ===\n" . $datesReport;
            $report .= "============\n";
        }
        $report .= "$nInsert persons inserted, $nUpdate updated ($dt s)\n";
        $report .= "$nDiffDates dates differ from A6";
        $report .= " - $nRestoredNames names restored in A6\n";
        return $report;
    }
        
}// end class    

