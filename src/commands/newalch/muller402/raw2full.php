<?php
/********************************************************************************
    Import cura A files to 7-full/
    
    @license    GPL
    @history    2020-05-15 22:38:58+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\DB5;
use g5\model\Person;
use g5\model\Group;
//use g5\model\Source;
use g5\commands\newalch\Newalch;
use tiglib\arrays\sortByKey;
use tiglib\time\seconds2HHMMSS;

class raw2full implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Loads 5muller_writers.csv to g5 db.
        Considers that persons contained in raw file don't already exist in g5 db
        => doesn't check for doublons.
        @pre    YAML file conrresponfing to Müller 402 must exist in g5 db.
        @param  $params empty Array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        $report =  "Importing " . Muller402::rawFilename() . "\n";
        
        // Check source existence
        try{
            $source = Muller402::source();
        }
        catch(\Exception $e){
            return "UNABLE TO RUN COMMAND: " . $e->getMessage() . "\n";
        }
        $idSource = Muller402::ID_SOURCE;
        
        // create group
        $g = Group::newEmpty(Newalch::UID_PREFIX_GROUP . DB5::SEP . Muller402::ID_SOURCE);
        
        $pname = '/(\d+M)\s*(.*)\s*/';
        $pplace = '/(.*?) ([A-Z]{2})/';
        
        $nb_stored = 0;
        $raw = Muller402::loadRawFile();
        foreach($raw as $line){
            $p = Person::newEmpty();
            $p->addSource($idSource);
            
            $new = [];
            $fields = explode(Muller402::RAW_SEP, $line);
            
            $p->addRaw($idSource, $fields);
            
            $p->addOccu('WRI'); /////// HERE put wikidata occupation id ///////////
            
            preg_match($pname, $fields[0], $m);
            $p->addId($idSource, $m[1]);
            $tmp = explode(',', $m[2]);
            $new['name']['family'] = $tmp[0];
            $new['name']['given'] = trim($tmp[1]);
            $new['name']['usual'] = $new['name']['given'] . ' ' . $new['name']['family'];
            $new['birth']['date'] = $fields[1].'-'.$fields[2].'-'.$fields[3].' '.$fields[4].':'.$fields[5];
            preg_match($pplace, $fields[7], $m);
            $new['birth']['place']['name'] = $m[1];
            $new['birth']['place']['c2'] = $m[2];
            $new['birth']['place']['cy'] = 'IT';
            $new['birth']['place']['lg'] = -(int)$fields[9]; // minus sign, correction from raw here
            $new['birth']['place']['lat'] = $fields[8];
            $new['birth']['tz'] = self::compute_offset($fields[6], $new['birth']['place']['lg']);
            
            // log command effect on data in the person yaml
            $p->addHistory("newalch muller402 raw2full", $idSource, $new);
            
            $p->update($new);
echo "\nnew : "; print_r($p);
            
break;
            $p->save(); // HERE save to disk
$report .= "Wrote ".$p->file()."\n";
            $nb_stored ++;
            $g->add($p->uid());
        }
        
exit;
// @todo remove following code, kept to end dev of current function
        $report .= "--- Importing file $datafile ---\n";
        
        //
        // 4 - store result in 7-full
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
    
    
    // ******************************************************
    /**
        Conversion of TZ offset found in newalch file to standard sHH:MM offset.
        WARNING : possible mistake for "-0.6" :
            0.6*60 = 36
            "Problèmes de l'heure résolus pour le monde entier", Françoise Schneider-Gauquelin indicates 00:37
            Current implementation uses Gauquelin, but needs to be confirmed
        @param $offset  timezone offset as specified in newalch file
        @param $lg      longitude, as previously computed
    **/
    private static function compute_offset($offset, $lg){
        switch($offset){
        	case '-1': 
        	    return '+01:00';
        	break;
        	case '-0.83': 
        	    return '+00:50';
        	break;
        	case '-0.6': 
        	    return '+00:37';
        	break;
        	case 'LMT': 
        	    // happens for 5 records
        	    // convert longitude to HH:MM:SS
        	    $sec = $lg * 240; // 240 = 24 * 3600 / 360
        	    return '+' . seconds2HHMMSS::compute($sec);
        	break;
            default:
                throw new \Exception("Timezone offset not handled in Muller402 : $offset");
        }
    }
    
}// end class    

