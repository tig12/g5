<?php
/********************************************************************************
    
    Updates opengauquelin database from files located in data/db/enrich/death
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-02-01 16:45:07+01:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\enrich\deathfr;

use g5\app\Config;
use g5\model\DB5;
use g5\model\Person;
use g5\model\Place_fr;
use tiglib\patterns\Command;
use tiglib\arrays\csvAssociative;

class csv2db implements Command {
    
    private static \PDO $db5;
    
    /** Directory containing the csv files to process **/
    const string DATA_DIR = 'data/db/enrich/death-fr';
    
    /** 
        @param $params  Array containing zero or one element.
                        One element: path to a csv file to process, relative to data/db/enrich/death-fr/
                        Zero element: match all files of data/db/enrich/death-fr/
        @return Empty string, echoes its output
    **/
    public static function execute($params=[]) {
        //
        // check params
        //
        $msg = "this command needs zero or one parameter, indicating the file to match\n"
                . "Ex: php run-g5.php enrich deathfr csv2db     => process all the csv files\n"
                . "    php run-g5.php enrich deathfr csv2db data/db/enrich/death-fr/death-fr-ok.csv.bz2\n";
        if(count($params) > 1){
            return "INVALID CALL: $msg";
        }
        if(count($params) == 1){
            $files = [$params[0]];
        }
        else{
            $tmp = glob(self::DATA_DIR . DS . '*');
            $files = [];
            foreach($tmp as $file){
                if(strpos($file, 'README') === false){
                    $files[] = $file;
                }
            }
        }
        //
        // main loop
        //
        self::$db5 = DB5::getDblink();
        foreach($files as $file){
            try{
                $file = 'compress.bzip2://' . Config::$data['dirs']['ROOT'] . DS . $file;
                echo "======= Processing $file =======\n";
                $input = csvAssociative::compute($file);
            }
            catch(\Exception $e){
                echo "Unable to open file $file\n";
                return;
            }
            $N_input = count($input) / 3;
            $N_updated = 0;
            for($i=0; $i < $N_input; $i++){
                $row_g5 = $input[3 * $i];
                if(strtolower($row_g5['V']) == 'n'){
                    continue; // record marked as non valid match
                }
                $row_deathfr = $input[3 * $i + 1];
                $slug = $row_g5['ID'];
                // uncomment to check that the file is ok (column V must be filled only on g5 lines)
                // if($row_deathfr['V'] != ''){
                //     echo "$slug\n";
                // }
                // continue;
                $p = Person::createFromSlug($slug);
                $new = []; // for history
                // death date
                $p->data['death']['date'] = $row_deathfr['DDAY'];
                $new['death']['date'] = $p->data['death']['date'];
                // death country
                $p->data['death']['place']['cy'] = 'FR';
                $new['death']['place']['cy'] = $p->data['death']['place']['cy'];
                // death place c2 code
                if(strlen($row_deathfr['DCODE']) == 5){
                    $c2 = substr($row_deathfr['DCODE'], 0, 2);
                    if($c2 == '97'){
                        $c2 = substr($row_deathfr['DCODE'], 0, 3); // outremer
                    }
                    if($c2 != '99'){ // foreign country (not France)
                        $p->data['death']['place']['c2'] = $c2;
                        $new['death']['place']['c2'] = $p->data['death']['place']['c2'];
                    }
                }
                // birth place name
                if($p->data['birth']['place']['name'] == ''){
                    $p->data['birth']['place']['name'] = Place_fr::ucwords($row_deathfr['BPLACE']);
                    $new['birth']['place']['name'] = $p->data['birth']['place']['name'];
                }
                $fixedPlace = self::fixPlaceName($p);
                if(count($fixedPlace) != 0) {
                    $new = array_replace_recursive($new, $fixedPlace);
                }
                $p->addHistory(
                    command: 'enrich deathfr csv2db',
                    sourceSlug: Deathfr::SOURCE_SLUG,
                    newdata: $new,
                    rawdata: $row_deathfr,
                );
                $p->addIdInSource(Deathfr::SOURCE_SLUG, "");
                $p->update(); // DB
                $N_updated++;
            } // end loop on input
            echo "Updated $N_updated persons\n";
        } // end loop on $files
        
        
    }
    
    /**
        For Paris and Lyon, fixes when arrondissement is specified.
        @return     Array containing the modified fields.
    **/
    public static function fixPlaceName(Person $p): array {
        $res = [];
        $name = strtolower($p->data['birth']['place']['name']);
        $fixables = [
            'paris'     => '75',
            'lyon'      => '69',
            'marseille' => '13',
        ];
        foreach(array_keys($fixables) as $fixable){
            if(str_starts_with($name, $fixable)){
                $pattern = '/^(\w+)\s+0?(\d+)/';
                preg_match($pattern, $name, $m);
                if(count($m) == 3){
                    // deathfr source is considered more reliable than other sources,
                    // so don't check if name or c2 or c3 already exist
                    $p->data['birth']['place']['name']  = ucfirst($m[1]);
                    $p->data['birth']['place']['c2']    = $fixables[$fixable];
                    $p->data['birth']['place']['c3']    = $m[2];
                    $res['birth']['place']['name']  = $p->data['birth']['place']['name'];
                    $res['birth']['place']['c2']    = $p->data['birth']['place']['c2'];
                    $res['birth']['place']['c3']    = $p->data['birth']['place']['c3'];
                }
            }
        }
        return $res;
    }
    
}// end class
