<?php
/********************************************************************************
    Import cura A files to 7-full/
    
    @license    GPL
    @history    2020-05-15 22:38:58+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\G5;
use g5\model\G5DB;
//use g5\model\Source;
use g5\model\Person;
use g5\model\Group;
use g5\model\Source;
use g5\Config;
use g5\patterns\Command;
use tiglib\arrays\sortByKey;


class raw2full implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Parses one html cura file of serie A (locally stored in directory data/raw/cura.free.fr)
        Stores each person of the file in a distinct yaml files, in 7-full/persons/
        
        Merges the original list (without names) with names contained in file 902gdN.html
        Merge is done using birthdate.
        Merge is not complete because of doublons (persons born the same day).
        
        @param  $params empty Array
        @return String report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        $report =  '';
        
Source::reindexIdUid(); exit;
        //$source = Muller402::source();
        
        //$pname = '/(\d+M)\s*(.*?)\,\s*(.*?)\s*/';
        $pname = '/(\d+M)\s*(.*)\s*/';
        $pplace = '/(.*?) ([A-Z]{2})/';
        
        $raw = Muller402::loadRawFile();
        foreach($raw as $line){
            $new = [];
            $fields = explode(Muller402::RAW_SEP, $line);
echo "\n<pre>"; print_r($fields); echo "</pre>\n";
            preg_match($pname, $fields[0], $m);
            $new['ids']['muller402'] = $m[1];
            $tmp = explode(',', $m[2]);
//echo "\n<pre>"; print_r($tmp); echo "</pre>\n";
            $new['name']['family'] = $tmp[0];
            $new['name']['given'] = trim($tmp[1]);
            $new['birth']['date'] = $fields[1].'-'.$fields[2].'-'.$fields[3].' '.$fields[4].':'.$fields[5];
            $new['birth']['tz'] = $fields[6]; // TODO convert to HH:MM
            preg_match($pplace, $fields[7], $m);
            $new['birth']['place']['name'] = $m[1];
            $new['birth']['c2'] = $m[2];
            $new['birth']['place']['cy'] = 'IT';
            $new['birth']['place']['lg'] = -$fields[9]; // minus sign, correction from raw here
            $new['birth']['place']['lat'] = $fields[8];
echo "\n<pre>"; print_r($new); echo "</pre>\n";
            
break;
        }
        
exit;
        $report .= "--- Importing file $datafile ---\n";
        
        //
        // 4 - store result in 7-full
        // create 1 source, 1 group, N persons
        //
        // "cura" source
        $source->data['uid'] = UID_PREFIX_SOURCE . G5DB::SEP . $datafile; // source/cura/A1
        $source->data['file'] = G5DB::$DIR . DS . str_replace(G5DB::SEP, DS, $source->data['uid']) . '.yml'; // /path/to/full/source/cura/A1.yml
        // group
        $g = Group::newEmpty(Cura::UID_PREFIX_GROUP . G5DB::SEP . $datafile);
        $nb_stored = 0;
        foreach($res as $cur){
            
            foreach(array_keys($cur) as $k){ $cur[$k] = trim($cur[$k]); }
            
            $p = Person::newEmpty();
            
            $p->addSource($datafile);
            
            $p->addRaw($datafile, $cur);

            $NUM = $cur['NUM'];
            
            $p->addId(Cura::IDSOURCE, Cura::gqid($datafile, $NUM));
            
            $new = [];
            $new['name']['family'] = $cur['FNAME'];
            $new['name']['given'] = $cur['GNAME'];
            $new['name']['usual'] = trim($new['name']['given'] . ' ' . $new['name']['family']);
            /////// HERE put wikidata occupation id ///////////
            $p->addOccu(A::compute_profession($datafile, $cur['PRO'], $NUM));
            // date time
            $day = Cura::computeDay($cur);
            $hour = Cura::computeHHMMSS($cur);
            $TZ = trim($cur['TZ']);
            if($TZ != 0 && $TZ != -1){
                throw new \Exception("timezone not handled : $TZ");
            }
            // TZ computation specific to A cura files - conform to Cura Notice
            $timezone = $TZ == 0 ? '+00:00' : '-01:00';
            $new['birth'] = [];
            $new['birth']['date-ut'] = "$day $hour$timezone"; // HERE not storing in date but in date-ut
            // place
            $new['birth']['place']['name'] = $cur['CITY'];
            [$new['birth']['place']['cy'], $new['birth']['c2']] = A::compute_country($cur['COU'], $cur['COD']);
            $new['birth']['place']['lg'] = Cura::computeLg($cur['LON']);
            $new['birth']['place']['lat'] = Cura::computeLat($cur['LAT']);
            
            // log command effect on data in the person yaml
            $p->addHistory("cura $datafile raw2full", $datafile, $new);
            
            $p->update($new);
            
            $p->save(); // HERE save to disk
$report .= "Wrote ".$p->file()."\n";
            $nb_stored ++;
            $g->add($p->uid());
break;
        }
        $g->save(); // HERE save to disk
        $report .= "Wrote ".$g->file()."\n";
        $report .= "Stored $nb_stored records\n";
        return $report;
    }
    
    
}// end class    

