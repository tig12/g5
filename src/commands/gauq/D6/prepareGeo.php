<?php
/********************************************************************************
    Builds file data/db/init/geonames/D6.csv
    This step is not executed when building the database from scratch.
    
    This code can be interrupted in the middle of execution and called several times.
    
    At first execution, file data/tmp/gauq/lerrcp/D6.csv is copied to data/db/init/geonames/D6.csv
    Following executions modify data/db/init/geonames/D6.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2017-04-27 22:04:25+02:00, Thierry Graff : creation
    @history    2025-08-16 14:57:08+02:00, Thierry Graff : split previous addGeo to prepareGeao and addGeo
********************************************************************************/
namespace g5\commands\gauq\D6;

use g5\G5;
use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use g5\model\Geonames;
use tiglib\misc\dosleep;
use tiglib\arrays\csvAssociative;
use tiglib\geonames\webservice\cityFromLgLat;
use tiglib\timezone\offset;

class prepareGeo implements Command {
    
    /** String written in field PLACE to indicate that a call to geonames webservice failed **/
    const FAILURE_MARK = 'XXX';
        
    /**
        Add missing geographic informations to data/db/init/geonames/D6.csv.
    **/
    public static function execute($params=[]): string {
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - this command doesn't need this parameter\n";
        }
        
        $datafile = 'D6';
        
        $tmpFile = LERRCP::tmpFilename($datafile);
        $geofile = D6::GEONAMES_FILE;
        
        $pdo_geonames = Geonames::compute_dblink();
        
        if(!is_file($tmpFile)){
            echo "Missing file $tmpFile\n";
            echo "You must run first : php run-g5.php gauq D6 raw2tmp\n";
            exit;
        }
        
        if(!is_file($geofile)){
            copy($tmpFile, $geofile); // at first exec only
        }
        
        while(true){
            $res_geo = '';
            $raw_geo = trim(file_get_contents($geofile));
            $lines_geo = explode("\n", $raw_geo);
            $N_total_lines = count($lines_geo);
            $N_treated_lines = 0;
            $newinfo = false; // true if a new geo info has been written in previous iteration
            foreach($lines_geo as $line_geo){
                if($N_treated_lines == $N_total_lines && $newinfo == false){
                    break;
                }
                $N_treated_lines++;
                if($newinfo){
                    // copy the rest of the csv file 
                    $res_geo .= $line_geo . "\n";
                    continue;
                }
                
                $fields = explode(G5::CSV_SEP, $line_geo);
                
                if($fields[D6::TMP_FIELD_NUM] == 'NUM'){
                    // first line
                    $res_geo .= $line_geo . "\n";
                    continue;
                }
                if($fields[D6::TMP_FIELD_PLACE] != ''){
                    // line already completed with geo informations
                    $res_geo .= $line_geo . "\n";
                    continue;
                }
                
                // here a new line is treated
                
                $line_id = $fields[D6::TMP_FIELD_NUM] . ' ' . $fields[D6::TMP_FIELD_FNAME] . ' ' . $fields[D6::TMP_FIELD_GNAME]; // for report only
                // failure or success when calling geonames webservice, a new information will go in the file.
                $newinfo = true;
                $lg = $fields[D6::TMP_FIELD_LG];
                $lat = $fields[D6::TMP_FIELD_LAT];
                //
                // here call to geonames web service
                //
                $geonames = cityFromLgLat::compute(Config::$data['geonames']['username'], $lg, $lat, false);
                echo $line_id . " : call to geonames web service\n";
                if($geonames['error']){                       
                    echo $line_id . " Call to geonames webservice failed: " . print_r($geonames, true) . "\n";
                    $fields[D6::TMP_FIELD_PLACE] = self::FAILURE_MARK;
                    $res_geo .= implode(G5::CSV_SEP, $fields) . "\n";
                    continue;
                }
                
                // here, call to geonames web service was sucessful
                
                // Call to geonames web service doesn't return C2, needed for timezone computation
                // => retrieve C2 from local geodb
                $schema = strtolower($geonames['result']['country']);
                $query = "select admin2_code from $schema.cities where geoid=:geonamesId";
                $rst_c2 = $pdo_geonames->prepare($query);
                $rst_c2->execute(['geonamesId' => $geonames['result']['geoid']]);
                if($rst_c2->rowCount() != 1){
                    echo $line_id . " : Could not compute admin2_code\n";
                    $C2 = '';
                }
                else{
                    $rows = $rst_c2->fetchAll(\PDO::FETCH_ASSOC);
                    $C2 = $rows[0]['admin2_code'] ?? '';
                }
                $fields[D6::TMP_FIELD_PLACE] = $geonames['result']['name'];
                $fields[D6::TMP_FIELD_CY] = $geonames['result']['country'];
                $fields[D6::TMP_FIELD_C2] = $C2;
                $fields[D6::TMP_FIELD_GEOID] = $geonames['result']['geoid'];
                $res_geo .= implode(G5::CSV_SEP, $fields) . "\n";
            }
            
            // Write back the csv
            // Saving the csv at each iteration permits to interrupt program execution without loosing information
            if($newinfo){
                file_put_contents($geofile, $res_geo);
            }
            if($N_treated_lines == $N_total_lines && $newinfo == false){
                break;
            }
            dosleep::execute(1.5); // keep cool with geonames.org web service
        } // end while true
        
        // new execution of geo2csv is necessary
        // if current execution retrieves 0 information from geonames web service
        echo  "Geographic information computed\n";
        return ''; // $report meaningless here as this is not a normal command
    }
    
}// end class    
