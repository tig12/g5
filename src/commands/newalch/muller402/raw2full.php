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
        
        $pname = '/(\d+)([MFK])\s*(.*)\s*/';
        $pplace = '/(.*?) ([A-Z]{2})/';
        
        $nb_stored = 0;
        $raw = Muller402::loadRawFile();
        foreach($raw as $line){
            $p = Person::newEmpty();
            $p->addSource($idSource);
            
            $new = [];
            $new['trust'] = Muller402::TRUST;
            
            $fields = explode(Muller402::RAW_SEP, $line);
            $p->addRaw($idSource, $fields);
            $p->addOccu('WRI'); /////// HERE put wikidata occupation id ///////////
            
            preg_match($pname, $fields[0], $m);
            
            $sex = $m[2];
            if($sex != 'M' && $sex != 'F'){
                // happens only for 478K Villaruel, Giuseppe
                // Comparision with scan of original Müller's AFD shows it's a OCR error
                // => included here, not in tweaks
                $sex='M';
            }
            
            $mullerId = $m[1];
            $p->addId($idSource, $mullerId);
            
            $nameFields = explode(',', $m[3]);
            if(count($nameFields) == 2){
                // normal case
                $new['name']['family'] = $nameFields[0];
                $new['name']['given'] = trim($nameFields[1]);
                $new['name']['usual'] = $new['name']['given'] . ' ' . $new['name']['family'];
            }
            else{
                // temporary fixes
                // @todo should be verified and included in tweaks
                // echo "\n<pre>"; print_r($nameFields); echo "</pre>\n";           
                // echo "\n<pre>"; print_r($fields); echo "</pre>\n"; continue;
                if($mullerId == '310' || $mullerId == '387'){
                    $new['name']['family'] = $nameFields[0];
                    $new['name']['given'] = '';
                    $new['name']['usual'] = $new['name']['family'];
                }
            }
            
            $new['sex'] = $sex;
            
            $new['birth']['date'] = $fields[1].'-'.$fields[2].'-'.$fields[3];
            if($fields[4] != '' && $fields[5] != ''){
                $new['birth']['date'] .= ' '.$fields[4].':'.$fields[5];
            }
            
            //
            // keep only records with complete birth time (at least YYYY-MM-DD HH:MM)
            //
            if(strlen($new['birth']['date']) < 16){
                continue;
            }
            
            $new['birth']['tz'] = '';
            preg_match($pplace, $fields[7], $m);
            $new['birth']['place']['name'] = $m[1];
            $new['birth']['place']['c2'] = $m[2];
            $new['birth']['place']['cy'] = 'IT';
            $new['birth']['place']['lg'] = self::lglat(-(int)$fields[9]); // minus sign, correction from raw here
            $new['birth']['place']['lat'] = self::lglat($fields[8]);
            $new['birth']['tz'] = self::compute_offset($fields[6], $new['birth']['place']['lg']);
            
            // log command effect on data in the person yaml
            $p->addHistory("newalch muller402 raw2full", $idSource, $new);
            
            $p->update($new);
            //$p->clean();
            $nb_stored ++;
            $p->save(); // HERE save to disk
            $g->add($p->uid());
//break;
        }
//echo "\ng : "; print_r($g); exit;
        $g->save(); // HERE save to disk
        $report .= "Wrote ".$g->file()."\n";
        $report .= "Stored $nb_stored records\n";
        return $report;
    }
    
    
    // ******************************************************
    /**
        @param $
    **/
    private static function lglat(string $str): string {
        return str_replace(',', '.', $str);
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
        if($offset == 'LMT'){ 
            // happens for 5 records
            // convert longitude to HH:MM:SS
            $sec = $lg * 240; // 240 = 24 * 3600 / 360
            return '+' . seconds2HHMMSS::compute($sec);
        }
        $offset = (int)$offset;
        switch($offset){
        	case 0: 
        	    return '+00:00';
        	break;
        	case -1: 
        	    return '+01:00';
        	break;
        	case -0.83: 
        	    return '+00:50';
        	break;
        	case -0.6: 
        	    return '+00:37';
        	break;
            default:
                throw new \Exception("Timezone offset not handled in Muller402 : $offset");
        }
    }
    
}// end class    

