<?php
/********************************************************************************
    Import data/raw/newalchemypress.com/05-muller-writers/5muller_writers.csv
    to data/tmp/newalch/muller-402-it-writers.csv
    
    @license    GPL
    @history    2020-05-15 22:38:58+02:00, Thierry Graff : Creation
    @history    2020-08-17 23:36:30+02:00, Thierry Graff : Conert from raw2full to raw2tmp
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\G5;
use g5\Config;
use g5\patterns\Command;
use g5\model\DB5;
use g5\commands\newalch\Newalch;
use tiglib\arrays\sortByKey;
use tiglib\time\seconds2HHMMSS;

class raw2full implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        @param  $params empty array
        @return String report
    **/
    public static function execute($params=[]): string{
        
        $report =  "--- muller402 raw2tmp ---\n";
        
        $pname = '/(\d+)([MFK])\s*(.*)\s*/';
        $pplace = '/(.*?) ([A-Z]{2})/';
        
        $nb_stored = 0;
        $raw = Muller402::loadRawFile();
        foreach($raw as $line){
            $fields = explode(Muller402::RAW_SEP, $line);
            $p->addRaw($idSource, $fields);
            $p->addOccu('WRI'); /////// HERE put wikidata occupation id ///////////
            
            preg_match($pname, $fields[0], $m);
            
            $sex = $m[2];
            if($sex != 'M' && $sex != 'F'){
                // happens only for 478K Villaruel, Giuseppe
                // Comparision with scan of original Müller's AFD shows it's an OCR error
                $sex='M';
            }
            
            $mullerId = $m[1];
            $p->addId($idSource, $mullerId);
            
            $nameFields = explode(',', $m[3]);
            if(count($nameFields) == 2){
                // normal case
                $new['name']['family'] = $nameFields[0];
                $new['name']['given'] = trim($nameFields[1]);
            }
            else{
                // empty given names
                // @todo should be verified by human and included in tweaks
                if($mullerId == '310' || $mullerId == '387'){
                    $new['name']['family'] = $nameFields[0];
                    $new['name']['given'] = '';
                }
            }
            if($mullerId == '23'){
                $new['name']['given'] = 'Ambrogio'; // OCR error
            }
            $new['name']['usual'] = $new['name']['family'];
            
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
            // Fix C2
            if($new['birth']['place']['name'] == 'Verona'){
                // systematic error in M402 file
                $new['birth']['place']['c2'] = 'VR';
            }
            if($mullerId == '76'){
                $new['birth']['place']['c2'] = 'ME'; // OCR error
            }
            if($mullerId == '369'){
                $new['birth']['place']['c2'] = 'CH'; // OCR error
            }
                
            
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
        }
        $g->save(); // HERE save to disk
        $report .= "Wrote Müller402 group in ".$g->file()."\n";
        $report .= "Stored $nb_stored records\n";
        return $report;
    }
    
    
    private static function lglat(string $str): string {
        return str_replace(',', '.', $str);
    }
    
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

