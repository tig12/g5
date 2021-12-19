<?php
/********************************************************************************
    Import Gauquelin A files to csv file in data/tmp/gauq/lerrcp
    For each A file, 2 files are generated :
    - An.csv (ex A1.csv) - includes corrections
    - An-raw.csv (ex A1-raw.csv) - without corrections, to keep a trace of the original raw values
    
    This code uses file 902gdN.html to retrieve the names, but this could have been done using only 902gdA*y.html files
    (for example, 902gdA1y.html could have been used instead of using 902gdA1y.html and 902gdN.html).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-12-26 22:30:04+01:00, Thierry Graff : creation from raw2csv
********************************************************************************/
namespace g5\commands\gauq\A;

use g5\G5;
use g5\model\DB5;
use g5\commands\gauq\LERRCP;
use g5\commands\gauq\Cura5;
use g5\commands\gauq\A\ACuraNames;
use g5\model\Names;
use g5\model\Names_fr;
use tiglib\patterns\Command;
use tiglib\arrays\sortByKey;

class raw2tmp implements Command {
    
    const REPORT_TYPE = [
        'small' => 'Echoes only global results',
        'full'  => 'Prints the details of all problematic rows',
        'same'  => 'Prints the details of unsolved repeats (with same number in list 1 and list 2)',
    ];
    
    /** 
        Parses one html cura5 file of serie A (locally stored in directory data/raw/gauq/lerrcp)
        
        Merges the original list (without names) with names contained in file 902gdN.html
        Merge is done using birthdate.
        Merge is not complete because of repeats (persons born the same day).
        
        @param  $params Array containing 3 elements :
                        - a string identifying what is processed (ex : 'A1')
                        - "raw2tmp" (useless here)
                        - The report type. Can be one of REPORT_TYPE keys.
        @return String report
        @throws Exception if unable to parse
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 3){
            return "USELESS PARAMETER : " . $params[3] . "\n";
        }
        $msg = "Possible values for parameter:\n";
        foreach(self::REPORT_TYPE as $k => $v){
            $msg .= "  '$k' : $v\n";
        }
        if(count($params) < 3){
            return "MISSING PARAMETER - This command needs a parameter to specify which output it displays.\n$msg";
        }
        if(!in_array($params[2], array_keys(self::REPORT_TYPE))){
            return "INVALID PARAMETER '{$params[2]}':\n$msg";
        }
        
        $report_type = $params[2];
        $datafile = $params[0];
        
        $report =  "--- gauq $datafile raw2tmp ---\n";
        $html = LERRCP::loadRawFile($datafile);
        $file_datafile = LERRCP::rawFilename($datafile);
        $file_names = ACuraNames::rawFilename(); // = 902gdN.html
        //
        // 1 - parse first list (without names) - store by birth date to prepare matching
        //
        // $raw is used to keep trace of an exact copy of the raw fields ; assoc array, keys = NUM
        $raw = [];
        $res1 = [];
        preg_match('#<pre>\s*(YEA.*?CITY)\s*(.*?)\s*</pre>#sm', $html, $m);
        if(count($m) != 3){
            throw new \Exception("Unable to parse first list (without names) in " . $file_datafile);
        }
        $fieldnames1 = explode(Cura5::HTML_SEP, $m[1]); // exactly equal to A::RAW_FIELDS
        $lines1 = explode("\n", $m[2]);
        foreach($lines1 as $line1){
            $fields = explode(Cura5::HTML_SEP, $line1);
            $tmp = [];
            for($i=0; $i < count($fields); $i++){
                $tmp[$fieldnames1[$i]] = $fields[$i]; // ex: $tmp['YEA'] = '1817'
            }
            $day = Cura5::computeDay($tmp);
            if(!isset($res1[$day])){
                $res1[$day] = [];
            }
            $res1[$day][] = $tmp;
            $raw[$tmp['NUM']] = $tmp;
        }
        //
        // 2 - prepare names - store by birth date to prepare matching
        //
        $res2 = [];
        $names = ACuraNames::parse()[$datafile];
        foreach($names as $fields){
            $day = $fields['day'];
            if(!isset($res2[$day])){
                $res2[$day] = [];
            }
            $res2[$day][] = $fields;
        }
        // Hack to fix error for Jean Lebris
        // Should logically be in tweak files
        // put here because useful to merge the lists 
        if($datafile == 'A1'){
            $res2['1817-03-25'] = [['day' => '1817-03-25', 'pro' => 'SP', 'name' => 'Lebris Jean']];
            unset($res2['1817-03-05']); // possible because this date is unique within $res2.
        }
        //
        // 3 - merge res1 and res2
        //
        $res = [];
        // variables used only for report
        $n_ok = 0;                              // correctly merged
        $n1 = 0; $missing_in_names = [];        // date present in list 1 and not in name list
        $n2 = 0; $repeats_same_nb = [];         // multiple persons born the same day ; same nb of persons in list 1 and name list
        $n3 = 0; $repeats_different_nb = [];    // multiple persons born the same day ; different nb of persons in list 1 and name list
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
                    $tmp['FNAME'] = self::computeReplacementName($datafile, $tmp['NUM']);
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
                    $tmp['FNAME'] = self::computeReplacementName($datafile, $tmp['NUM']);
                    $res[] = $tmp;
                }
                $new_repeat = [$file_datafile => [], $file_names => []];
                foreach($array1 as $tmp){
                    $new_repeat[$file_datafile][] = [
                        'LINE' => implode("\t", $tmp),
                        'NUM' => $tmp['NUM'],
                    ];
                }
                foreach($array2 as $tmp){
                    $new_repeat[$file_names][] = [
                        'LINE' => implode("\t", $tmp),
                        'FNAME' => $tmp['name'],
                    ];
                }
                $repeats_different_nb[] = $new_repeat;
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
                        $tmp['FNAME'] = self::computeReplacementName($datafile, $tmp['NUM']);
                        $res[] = $tmp;
                    }
                    // fill $repeats_same_nb with all candidate lines
                    $new_repeat = [$file_datafile => [], $file_names => []];
                    foreach($array1 as $tmp){
                        $new_repeat[$file_datafile][] = [
                            'LINE' => implode("\t", $tmp),
                            'NUM' => $tmp['NUM'],
                        ];
                    }
                    foreach($array2 as $tmp){
                        $new_repeat[$file_names][] = [
                            'LINE' => implode("\t", $tmp),
                            'FNAME' => $tmp['name'],
                        ];
                    }
                    $repeats_same_nb[] = $new_repeat;
                }
            }
        }
        $res = sortByKey::compute($res, 'NUM');
        //
        // 1955 corrections
        //
        $n1_fix = 0;    // cases in $missing_in_names solved by 1955
        $n2_fix = 0;    // cases in $repeats_same_nb solved by 1955
        $n_ok_fix = 0;  // other cases solved by 1955, directly solved by A::CORRECTIONS_1955
        if(isset(A::CORRECTIONS_1955[$datafile])){
            [$n_ok_fix, $n1_fix, $n2_fix] = self::corrections1955($res, $missing_in_names, $repeats_same_nb, $datafile, $file_datafile, $file_names);
        }
        //
        // Manual corrections
        // php run-g5.php gauq A2 raw2tmp same
        //
        $n2bis_fix = 0;    // cases solved by A::CORRECTIONS_BY_HAND ($repeats_same_nb only)
        if(isset(A::CORRECTIONS_BY_HAND[$datafile])){
            $n2bis_fix = self::correctionsByhand($res, $datafile, $file_datafile);
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
        $n_bad = $n1 + $n2 + $n3 - $n_ok_fix - $n1_fix - $n2_fix - $n2bis_fix;
        $n_good = $n_ok + $n_ok_fix + $n1_fix + $n2_fix + $n2bis_fix;
        $percent_ok = round($n_good * 100 / count($lines1), 2);
        $percent_not_ok = round($n_bad * 100 / count($lines1), 2);
        if($report_type == 'full'){
            $report .= "nb in list1 ($file_datafile) : " . count($lines1) . " - nb in list2 ($file_names) : " . count($names) . "\n";
            //
            if(count($missing_in_names) > 0){
                $report .= "\n======= case 1 : $n1 dates present in $file_datafile and missing in $file_names =======\n"
                    . "$n1_fix fixed by 1955\n"
                    . "Remains " . ($n1 - $n1_fix) . " to fix\n";
                foreach($missing_in_names as $entry){
                    $report .= $entry['LINE'] . "\n";
                }
            }
            //
            if(count($repeats_different_nb) > 0){
                $report .= "\n======= case 3 : $n3 date ambiguities with different nb =======\n";
                $repeats_different_nb_keys = array_keys($repeats_different_nb[0]); // ex ['902gdA2y.html', '902gdN.html']
                foreach($repeats_different_nb as $entry){
                    $report .= "\n";
                    foreach($entry[$repeats_different_nb_keys[0]] as $entry2){
                        $report .=  $entry2['LINE'] . "\n";
                    }
                    foreach($entry[$repeats_different_nb_keys[1]] as $entry2){
                        $report .=  $entry2['LINE'] . "\n";
                    }
                }
            }
        }
        if(($report_type == 'full' || $report_type == 'same') && count($repeats_same_nb) > 0){
            $res_num = [];
            foreach($res as $entry){
                $res_num[$entry['NUM']] = $entry;
            }
            $report .= "\n======= case 2 : $n2 date ambiguities with same nb =======\n"
                . "$n2_fix fixed by 1955\n"
                . "$n2bis_fix fixed by manual corrections\n"
                . "Remains " . ($n2 - $n2_fix - $n2bis_fix) . " to fix\n";
            [$key1, $key2] = array_keys($repeats_same_nb[0]); // ex ['902gdA2y.html', '902gdN.html']
            $i = 1;
            foreach($repeats_same_nb as $repeat){
                $all_fixed = true;
                foreach($repeat[$key1] as $elt){
                    if(strpos($res_num[$elt['NUM']]['FNAME'], 'Gauquelin-') === 0){
                        $all_fixed = false;
                        break;
                    }
                }
                if($all_fixed){
                    continue;
                }
                $report .= "--- $i ---\n";
                foreach($repeat[$key1] as $elt){
                    $report .= $elt['LINE'] . "\n";
                }
                foreach($repeat[$key2] as $elt){
                    $report .= $elt['LINE'] . "\n";
                }
                $i++;
            }
        }
        $n = $n_bad + $n_good;
        $report .= "N = $n ; corrections from 1955 book : $n_correction_1955\n";
        $report .= "names : nb match = $n_good ($percent_ok %) - nb NOT match = $n_bad ($percent_not_ok %)\n";
        
        //
        // 4 - Generate result and store in data/tmp
        //
        $nbStored = 0;
        $csv = implode(G5::CSV_SEP, A::TMP_FIELDS) . "\n";
        $csv_raw = implode(G5::CSV_SEP, A::RAW_FIELDS) . "\n";
        foreach($res as $cur){
            $new = array_fill_keys(A::TMP_FIELDS, '');
            $new['NUM'] = trim($cur['NUM']);
            $new['FNAME'] = trim($cur['FNAME']);
            $new['GNAME'] = trim($cur['GNAME']);
            $new['OCCU'] = self::computeProfession($datafile, $cur['PRO'], $new['NUM']);
            // date time
            $day = Cura5::computeDay($cur);
            $hour = Cura5::computeHHMMSS($cur);
            $date = "$day $hour";
            $TZ = trim($cur['TZ']);
            if($TZ != 0 && $TZ != -1){
                throw new \Exception("timezone not handled : $TZ"); // does not occur
            }
            if($TZ == 0){
                $new['DATE-UT'] = "$day $hour";
            }
            else{
                // convert all dates to UT
                $dt = new \DateTime($date);
                $interval = new \DateInterval('PT1H'); // 1 hour
                $dt->sub($interval);
                $new['DATE-UT'] = $dt->format('Y-m-d H:i:s');
            }
            // place
            [$new['PLACE'], $new['C3']] = self::computePlace($cur['CITY']);
            [$new['CY'], $new['C2']] = self::computeCountry($cur['COU'], $cur['COD']);
            $new['LG'] = Cura5::computeLg($cur['LON']);
            $new['LAT'] = Cura5::computeLat($cur['LAT']);
//            $new['GEOID'] = '';
//            $new['NOTES'] = '';
            $csv .= implode(G5::CSV_SEP, $new) . "\n";
            $csv_raw .= implode(G5::CSV_SEP, $raw[$new['NUM']]) . "\n";
            $nbStored ++;
        }
        
        $csvfile = LERRCP::tmpFilename($datafile);
        $dir = dirname($csvfile);
        if(!is_dir($dir)){
            mkdir($dir, 0755, true);
        }
        file_put_contents($csvfile, $csv);
        $report .= "Stored $nbStored lines in $csvfile\n";
        // second file keeping a trace of the original values
        $csvfile = LERRCP::tmpRawFilename($datafile);
        file_put_contents($csvfile, $csv_raw);
        $report .= "Stored $nbStored lines in $csvfile\n";
        
        return $report;
    }
    
    /**
        @return String like 'Gauquelin-A1-243'
    **/
    private static function computeReplacementName($datafile, $NUM){
        return 'Gauquelin-' . LERRCP::gauquelinId($datafile, $NUM);
    }
    
    /** 
        Computes precise profession when possible
        First compute not-detailed profession from $pro
        Then computes precise profession from $num, if possible
    **/
    private static function computeProfession($datafile, $pro, $num){
        $res = A::PROFESSIONS_NO_DETAILS[$datafile][$pro];
        if(isset(A::PROFESSIONS_DETAILS[$datafile])){
            $tmp = A::PROFESSIONS_DETAILS[$datafile];
            foreach($tmp as $elts){
                // $elts looks like that : ['athletics-competitor', 1, 86],
                if($num >= $elts[1] && $num <= $elts[2]){
                    $res = $elts[0];
                    break;
                }
            }
        }
        return $res;
    }
    
    /**
        Computes place name and C3 (arrondissement) for Paris and Lyon
    **/
    private static function computePlace($str){
        $str = trim($str);
        preg_match('/(.*?) (\d+).*/', $str, $m);
        if(count($m) > 0){
            // Paris, Lyon
            return [$m[1], $m[2]];
        }
        // most common case
        return [$str, ''];
    }
    
    /**
        Computes the ISO 3166 country code from fields COU and COD of cura5 files.
    **/
    private static function computeCountry($COU, $COD){
        $COU = A::COUNTRIES[$COU];
        $COD = trim($COD);
        if($COU == 'FR' && $COD== 'ALG'){
            $COU = 'DZ';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'MON'){
            $COU = 'MC';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'BEL'){
            $COU = 'BE';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'B'){
            $COU = 'BE';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'SCHW'){
            $COU = 'CH';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'G'){
            $COU = 'DE';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'ESP'){
            $COU = 'ES';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'I'){
            $COU = 'IT';
            $COD= '';
        }
        else if($COU == 'FR' && $COD == 'N'){
            $COU = 'NL';
            $COD= '';
        }
        return [$COU, $COD];
    }
    
    /**
        Auxiliary of raw2tmp()
        Modifies $res passed by reference
        @return [$n_ok_fix, $n1_fix, $n2_fix]
    **/
    private static function corrections1955(&$res, &$missing_in_names, &$repeats_same_nb, $datafile, $file_datafile, $file_names){
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
        // Resolve repeats
        // computes $n2_fix
        //
        $NUMS_1955 = array_keys(A::CORRECTIONS_1955[$datafile]);
        $N_REPEATS = count($repeats_same_nb);
        for($i=0; $i < $N_REPEATS; $i++){
            if(count($repeats_same_nb[$i][$file_datafile]) != 2){                
                // resolution works only for doublon repeats (not triplets or more elements)
                continue;
            }
            $found = false;
            if(in_array($repeats_same_nb[$i][$file_datafile][0]['NUM'], $NUMS_1955)){
                $NUM = $repeats_same_nb[$i][$file_datafile][0]['NUM'];
                $NAME = A::CORRECTIONS_1955[$datafile][$NUM];
                $found = 0;
            }
            else if(in_array($repeats_same_nb[$i][$file_datafile][1]['NUM'], $NUMS_1955)){
                $NUM = $repeats_same_nb[$i][$file_datafile][1]['NUM'];
                $NAME = A::CORRECTIONS_1955[$datafile][$NUM];
                $found = 1;
            }
            if($found !== false){
                // resolve first
                $idx_num = ($found === 0 ? 1 : 0);
                $idx_name = ($repeats_same_nb[$i][$file_names][0]['FNAME'] == $NAME ? 1 : 0); // HERE use of exact name spelling in A::CORRECTIONS_1955
                $new_num1 = $repeats_same_nb[$i][$file_datafile][$idx_num]['NUM'];
                $new_name1 = $repeats_same_nb[$i][$file_names][$idx_name]['FNAME'];
                // inject repeat resolution in $res
                for($j=0; $j < count($res); $j++){
                    if($res[$j]['NUM'] == $new_num1){
                        $res[$j]['FNAME'] = $new_name1;
                        break;
                    }
                }
                // resolve second
                $idx_num = ($idx_num == 0 ? 1 : 0);
                $idx_name = ($idx_name == 0 ? 1 : 0);
                $new_num2 = $repeats_same_nb[$i][$file_datafile][$idx_num]['NUM'];
                $new_name2 = $repeats_same_nb[$i][$file_names][$idx_name]['FNAME'];
                // inject repeat resolution in $res
                for($j=0; $j < count($res); $j++){
                    if($res[$j]['NUM'] == $new_num2){
                        $res[$j]['FNAME'] = $new_name2;
                        break;
                    }
                }
                $n2_fix += 2;
                unset($repeats_same_nb[$i]);
            }
        }
        //
        // directly fix the result with data of A::CORRECTIONS_1955
        // only for cases not solved by repeats
        // computes $n_ok_fix
        //
        $n = count($res);
        for($i=0; $i < $n; $i++){
            $NUM = $res[$i]['NUM'];
            // test on strpos done to avoid counting cases solved by repeats
            if(isset(A::CORRECTIONS_1955[$datafile][$NUM]) && strpos($res[$i]['FNAME'], 'Gauquelin-') === 0){
                $n_ok_fix++;
                $res[$i]['FNAME'] = A::CORRECTIONS_1955[$datafile][$NUM];
            }
        }
        return [$n_ok_fix, $n1_fix, $n2_fix];
    }
    
    
    // ******************************************************
    /**
        Auxiliary of raw2tmp()
        Modifies $res passed by reference
        WARNING: this function does not modify $repeats_same_nb, even if it should in fact
        (because the repeats are solved by A::CORRECTIONS_BY_HAND)
        @return $n2bis_fix
    **/
    private static function correctionsByhand(&$res, $datafile, $file_datafile){
        $n2bis_fix = 0;
        $n = count($res);
        // first pass, on $res, to correct the names
        for($i=0; $i < $n; $i++){
            $NUM = $res[$i]['NUM'];
            if(isset(A::CORRECTIONS_BY_HAND[$datafile][$NUM])){
                $n2bis_fix++;
                $res[$i]['FNAME'] = A::CORRECTIONS_BY_HAND[$datafile][$NUM];
            }
        }
        return $n2bis_fix;
    }
} // end class    
