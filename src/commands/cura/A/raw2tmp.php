<?php
/********************************************************************************
    Import cura A files to csv file in data/tmp/cura
    
    This code uses file 902gdN.html to retrieve the names, but this could have been done using only 902gdA*y.html files
    (for example, 902gdA1y.html could have been used instead of using 902gdA1y.html and 902gdN.html).
    
    @license    GPL
    @history    2019-12-26 22:30:04+01:00, Thierry Graff : creation from raw2csv
********************************************************************************/
namespace g5\commands\cura\A;

use g5\G5;
use g5\model\DB5;
//use g5\model\Source;
use g5\model\Person;
use g5\model\Group;
use g5\model\Source;
use g5\Config;
use g5\patterns\Command;
use g5\model\Names;
use g5\model\Names_fr;
use g5\commands\cura\Cura;
use g5\commands\cura\CuraNames;
use tiglib\arrays\sortByKey;


class raw2tmp implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        Parses one html cura file of serie A (locally stored in directory data/raw/cura.free.fr)
        Stores each person of the file in a distinct yaml files, in 7-full/persons/
        
        Merges the original list (without names) with names contained in file 902gdN.html
        Merge is done using birthdate.
        Merge is not complete because of doublons (persons born the same day).
        
        @param  $params Array containing 3 elements :
                        - a string identifying what is processed (ex : 'A1')
                        - "raw2full" (useless here)
                        - The report type. Can be "small" or "full"
        @return String report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 3){
            return "INVALID PARAMETER : " . $params[3] . " - raw2full doesn't need this parameter\n";
        }
        $msg = "raw2full needs a parameter to specify which output it displays. Can be :\n"
             . "  small : echoes only global results\n"
             . "  full : prints the details of problematic rows\n";
        if(count($params) < 3){
            return "MISSING PARAMETER : $msg";
        }
        if(!in_array($params[2], ['small', 'full'])){
            return "INVALID PARAMETER : $msg";
        }
        
        $report_type = $params[2];
        $datafile = $params[0];
        
        $report =  "--- Importing file $datafile ---\n";
        $raw = Cura::loadRawFile($datafile);
        $file_datafile = Cura::rawFilename($datafile);
        $file_names = CuraNames::rawFilename(); // = 902gdN.html
        //
        // 1 - parse first list (without names) - store by birth date to prepare matching
        //
        $res1 = [];
        preg_match('#<pre>\s*(YEA.*?CITY)\s*(.*?)\s*</pre>#sm', $raw, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse first list (without names) in " . $file_datafile);
        }
        $fieldnames1 = explode(Cura::HTML_SEP, $m[1]);
        $lines1 = explode("\n", $m[2]);
        foreach($lines1 as $line1){
            $fields = explode(Cura::HTML_SEP, $line1);
            $tmp = [];
            for($i=0; $i < count($fields); $i++){
                $tmp[$fieldnames1[$i]] = $fields[$i]; // ex: $tmp['YEA'] = '1817'
            }
            $day = Cura::computeDay($tmp);
            if(!isset($res1[$day])){
                $res1[$day] = [];
            }
            $res1[$day][] = $tmp;
        }
        //
        // 2 - prepare names - store by birth date to prepare matching
        //
        $res2 = [];
        $names = CuraNames::parse()[$datafile];
        foreach($names as $fields){
            $day = $fields['day'];
            if(!isset($res2[$day])){
                $res2[$day] = [];
            }
            $res2[$day][] = $fields;
        }
        // Hack to fix error for Jean Lebris
        // Should logically be in tweaks
        // put here because useful to merge the lists 
        if($datafile == 'A1'){
            $res2['1817-03-25'] = [['day' => '1817-03-25', 'pro' => 'SP', 'name' => 'Lebris Jean']];
            unset($res2['1817-03-05']); // possible because this date is unique within $res2.
        }
        //
        // 3 - merge res1 and res2 (name list)
        //
        $res = [];
        // variables used only for report
        $n_ok = 0;                              // correctly merged
        $n1 = 0; $missing_in_names = [];        // date present in list 1 and not in name list
        $n2 = 0; $doublons_same_nb = [];        // multiple persons born the same day ; same nb of persons in list 1 and name list
        $n3 = 0; $doublons_different_nb = [];   // multiple persons born the same day ; different nb of persons in list 1 and name list
        foreach($res1 as $day1 => $array1){
            if(!isset($res2[$day1])){
                // date in list 1 and not in name list
                foreach($array1 as $tmp){
                    $n1++;
                    $missing_in_names[] = [
                        'LINE' => implode("\t", $tmp),
                        'NUM' => $tmp['NUM'],
                    ];
                    // store in $res with fabricated name
                    $tmp['FNAME'] = A::compute_replacement_name($datafile, $tmp['NUM']);
                    $res[] = $tmp;
                }
                continue;
            }
            $array2 = $res2[$day1];
            if(count($array2) != count($array1)){
                // date both in list 1 and in name list
                // but different nb of elements => ambiguity
                // store in $res with fabricated name
                foreach($array1 as $tmp){
                    $n3++;
                    $tmp['FNAME'] = A::compute_replacement_name($datafile, $tmp['NUM']);
                    $res[] = $tmp;
                }
                $new_doublon = [$file_datafile => [], $file_names => []];
                foreach($array1 as $tmp){
                    $new_doublon[$file_datafile][] = [
                        'LINE' => implode("\t", $tmp),
                        'NUM' => $tmp['NUM'],
                    ];
                }
                foreach($array2 as $tmp){
                    $new_doublon[$file_names][] = [
                        'LINE' => implode("\t", $tmp),
                        'FNAME' => $tmp['name'],
                    ];
                }
                $doublons_different_nb[] = $new_doublon;
                continue;
            }
            else{
                // $array1 and $array2 have the same nb of elements
                if(count($array1) == 1){
                    // OK no ambiguity => add to res
                    $tmp = $array1[0];
                    $tmp['FNAME'] = $array2[0]['name'];
                    $res[] = $tmp;
                    $n_ok++;
                }
                else{
                    // more than one persons share the same birth date => ambiguity
                    // store in $res with fabricated name
                    foreach($array1 as $tmp){
                        $n2++;
                        $tmp['FNAME'] = A::compute_replacement_name($datafile, $tmp['NUM']);
                        $res[] = $tmp;
                    }
                    // fill $doublons_same_nb with all candidate lines
                    $new_doublon = [$file_datafile => [], $file_names => []];
                    foreach($array1 as $tmp){
                        $new_doublon[$file_datafile][] = [
                            'LINE' => implode("\t", $tmp),
                            'NUM' => $tmp['NUM'],
                        ];
                    }
                    foreach($array2 as $tmp){
                        $new_doublon[$file_names][] = [
                            'LINE' => implode("\t", $tmp),
                            'FNAME' => $tmp['name'],
                        ];
                    }
                    $doublons_same_nb[] = $new_doublon;
                }
            }
        }
        $res = sortByKey::compute($res, 'NUM');
        //
        // 1955 corrections
        //
        if(isset(A::CORRECTIONS_1955[$datafile])){
            [$n_ok_fix, $n1_fix, $n2_fix] = self::corrections1955($res, $missing_in_names, $doublons_same_nb, $datafile, $file_datafile, $file_names);
        }
        else{
            $n_ok_fix = $n1_fix = $n2_fix = 0;
        }
        //
        // FNAME, GNAME
        //
        foreach(array_keys($res) as $k){
            // try to separate FNAME and GNAME
            [$res[$k]['FNAME'], $res[$k]['GNAME']] = Names::familyGiven($res[$k]['FNAME']);
            // correct accents
            $res[$k]['GNAME'] = Names_fr::accentGiven($res[$k]['GNAME']);
        }
        //
        // report
        //
        $n_correction_1955 = isset(A::CORRECTIONS_1955[$datafile]) ? count(A::CORRECTIONS_1955[$datafile]) : 0;
        $n_bad = $n1 + $n2 + $n3 - $n_ok_fix - $n1_fix - $n2_fix;
        $n_good = $n_ok + $n_ok_fix + $n1_fix + $n2_fix;
        $percent_ok = round($n_good * 100 / count($lines1), 2);
        $percent_not_ok = round($n_bad * 100 / count($lines1), 2);
        if($report_type == 'full'){
            $report .= "nb in list1 ($file_datafile) : " . count($lines1) . " - nb in list2 ($file_names) : " . count($names) . "\n";
            $report .= "case 1 : $n1 dates present in $file_datafile and missing in $file_names - $n1_fix fixed by 1955\n";
            $report .=  print_r($missing_in_names, true) . "\n";
            $report .= "case 2 : $n2 date ambiguities with same nb - $n2_fix fixed by 1955\n";
            $report .= print_r($doublons_same_nb, true) . "\n";
            $report .= "case 3 : $n3 date ambiguities with different nb\n";
            $report .= print_r($doublons_different_nb, true) . "\n";
        }
        $n = $n_bad + $n_good;
        $report .= "Corrections from 1955 book : $n_correction_1955\n";
        $report .= "names : nb match = $n_good / $n ($percent_ok %)\n";
        $report .= "    nb NOT match = $n_bad ($percent_not_ok %)\n";
        
        //
        // 4 - store result in data/tmp
        //
        $nbStored = 0;
        $csv = implode(G5::CSV_SEP, A::TMP_FIELDS) . "\n";
        $csv_raw = '';
        foreach($res as $cur){
            $new = array_fill_keys(A::TMP_FIELDS, '');
            $new['NUM'] = trim($cur['NUM']);
            $new['FNAME'] = trim($cur['FNAME']);
            $new['GNAME'] = trim($cur['GNAME']);
            /////// TODO put wikidata occupation id ///////////
            $new['OCCU'] = A::compute_profession($datafile, $cur['PRO'], $new['NUM']);
            // date time
            $day = Cura::computeDay($cur);
            $hour = Cura::computeHHMMSS($cur);
            $TZ = trim($cur['TZ']);
            if($TZ != 0 && $TZ != -1){
                throw new \Exception("timezone not handled : $TZ");
            }
            // note that when $TZ = -1, +01:00 is stored
            $timezone = $TZ == 0 ? '+00:00' : '+01:00';
            $new['DATE'] = "$day $hour$timezone";
            // place
            $new['PLACE'] = trim($cur['CITY']);
            [$new['CY'], $new['C2']] = A::compute_country($cur['COU'], $cur['COD']);
            $new['LG'] = Cura::computeLg($cur['LON']);
            $new['LAT'] = Cura::computeLat($cur['LAT']);
            $new['GEOID'] = '';
            $new['NOTES'] = '';
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            $nbStored ++;
        }
        $csvfile = Cura::tmpFilename($datafile);
        $dir = dirname($csvfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($csvfile, $csv);
        $report .= "Stored $nbStored lines in $csvfile\n";
        return $report;
    }
    
    
    // ******************************************************
    /**
        Auxiliary of raw2full()
        @return [$n_ok_fix, $n1_fix, $n2_fix]
    **/
    private static function corrections1955(&$res, &$missing_in_names, &$doublons_same_nb, $datafile, $file_datafile, $file_names){
        $n_ok_fix = $n1_fix = $n2_fix = 0;
        //
        // Remove cases in $missing_in_names solved by 1955
        // useful only for report
        // computes $n1_fix
        //
        for($i=0; $i < count($missing_in_names); $i++){
            if(in_array($missing_in_names[$i]['NUM'], A::CORRECTIONS_1955[$datafile])){
                unset($missing_in_names[$i]);
                $n1_fix++;
            }
        }
        //
        // Resolve doublons
        // computes $n2_fix
        //
        $NUMS_1955 = array_keys(A::CORRECTIONS_1955[$datafile]);
        $N_DOUBLONS = count($doublons_same_nb);
        for($i=0; $i < $N_DOUBLONS; $i++){
            if(count($doublons_same_nb[$i][$file_datafile]) != 2){                
                // resolution works only for doublons (not triplets or more elements)
                continue;
            }
            $found = false;
            if(in_array($doublons_same_nb[$i][$file_datafile][0]['NUM'], $NUMS_1955)){
                $NUM = $doublons_same_nb[$i][$file_datafile][0]['NUM'];
                $NAME = A::CORRECTIONS_1955[$datafile][$NUM];
                $found = 0;
            }
            else if(in_array($doublons_same_nb[$i][$file_datafile][1]['NUM'], $NUMS_1955)){
                $NUM = $doublons_same_nb[$i][$file_datafile][1]['NUM'];
                $NAME = A::CORRECTIONS_1955[$datafile][$NUM];
                $found = 1;
            }
            if($found !== false){
                // resolve first
                $idx_num = ($found === 0 ? 1 : 0);
                $idx_name = ($doublons_same_nb[$i][$file_names][0]['FNAME'] == $NAME ? 1 : 0); // HERE use of exact name spelling in A::CORRECTIONS_1955
                $new_num1 = $doublons_same_nb[$i][$file_datafile][$idx_num]['NUM'];
                $new_name1 = $doublons_same_nb[$i][$file_names][$idx_name]['FNAME'];
                // inject doublon resolution in $res
                for($j=0; $j < count($res); $j++){
                    if($res[$j]['NUM'] == $new_num1){
                        $res[$j]['FNAME'] = $new_name1;
                        break;
                    }
                }
                // resolve second
                $idx_num = ($idx_num == 0 ? 1 : 0);
                $idx_name = ($idx_name == 0 ? 1 : 0);
                $new_num2 = $doublons_same_nb[$i][$file_datafile][$idx_num]['NUM'];
                $new_name2 = $doublons_same_nb[$i][$file_names][$idx_name]['FNAME'];
                // inject doublon resolution in $res
                for($j=0; $j < count($res); $j++){
                    if($res[$j]['NUM'] == $new_num2){
                        $res[$j]['FNAME'] = $new_name2;
                        break;
                    }
                }
                $n2_fix += 2;
                unset($doublons_same_nb[$i]); // useful only for report
            }
        }
        //
        // directly fix the result with data of A::CORRECTIONS_1955
        // only for cases not solved by doublons
        // computes $n_ok_fix
        //
        $n = count($res);
        for($i=0; $i < $n; $i++){
            $NUM = $res[$i]['NUM'];
            // test on strpos done to avoid counting cases solved by doublons
            if(isset(A::CORRECTIONS_1955[$datafile][$NUM]) && strpos($res[$i]['FNAME'], 'Gauquelin-') === 0){
                $n_ok_fix++;
                $res[$i]['FNAME'] = A::CORRECTIONS_1955[$datafile][$NUM];
            }
        }
        return [$n_ok_fix, $n1_fix, $n2_fix];
    }
    
    
}// end class    

