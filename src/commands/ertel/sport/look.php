<?php
/********************************************************************************
    Code to examine data/tmp/ertel/ertel-4384-athletes.csv
    This code is not part of any build process - only informative.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2019-05-11 18:58:50+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\ertel\sport;

use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use g5\commands\csicop\irving\Irving;

class look implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php ertel sport look eminence
    **/
    const POSSIBLE_PARAMS = [
        'sport',
        'quel',
        'date',
        'eminence',
        'ids',
        'mars',
        'cp',
        'checkoccu',
        'checkgqid',
    ];
    
    // *****************************************
    /** 
        Routes to the different actions, based on $param
        @param $param Array containing one element (a string)
                      Must be one of self::POSSIBLE_PARAMS
    **/
    public static function execute($params=[]): string{
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[0];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $method = 'look_' . $param;
        return self::$method();
    }
    
    
    // ******************************************************
    /**
        Look at SPORT and IG columns.
    **/
    private static function look_sport(){
        $report = '';
        $rows = ErtelSport::loadTmpFile();
        $res = []; // assoc array keys = sport codes ; values = [IG, n]
        foreach($rows as $row){
            $sport = $row['SPORT'];
            if(!isset($res[$sport])){
                $res[$sport] = [
                    'IG' => $row['IG'],
                    'n' => 0,
                ];
            }
            $res[$sport]['n'] ++;
            // coherence check
            if($res[$sport]['IG'] != $row['IG']){
                $report .= "Incoherent association sport / IG, line " . $row['NR'] . ' ' . $row['FNAME'] . ' ' . $row['GNAME']
                    . ' : ' . $sport . ' ' . $row['IG'] . "\n";
            }
            if(strlen($sport) == 3){
                $report .= 'Incoherent sport code : ' . $sport . ' ' . $row['NR'] . ' ' . $row['FNAME']
                        . ' ' . $row['GNAME'] . ' ' . $row['IG'] . "\n";
            }
        }
        // print
        ksort($res);
        foreach($res as $sport => $details){
            $report .= "{$details['IG']} $sport : {$details['n']}\n";
        }
        return $report;
    }
    
    // ******************************************************
    /**
        Look at QUEL column.
    **/
    private static function look_quel(){
        $report = '';
        $rows = ErtelSport::loadTmpFile();
        $res = []; // assoc codes => nb of records with this code
        foreach($rows as $row){
            if(!isset($res[$row['QUEL']])){
                $res[$row['QUEL']] = 0;
            }
            $res[$row['QUEL']] ++;
        }
        ksort($res);
        $report .= "\n" . print_r($res, true) . "\n";
        return $report;
    }
    
    // ******************************************************
    /**
        Look at DATE column.
    **/
    private static function look_date(){
        $report = '';
        $rows = ErtelSport::loadTmpFile();
        $N = 0;             // total nb lines
        $nWith = 0;         // nb lines with birth time
        $nWithout = 0;      // nb lines without birth time
        $nWithoutFromG = 0; // nb lines without birth time from Gauquelin LERRCP
        $GCodes = ['*G:D10', 'G:A01', 'G:D06', 'G:D10'];
        foreach($rows as $row){
            $N++;
            $date = $row['DATE'];
            if(strlen($date) == 10){
                $nWithout++;
                if(in_array($row['QUEL'], $GCodes)){
                    $nWithoutFromG++;
                }
            }
            else if(strlen($date) == 16){
                $nWith++;
            }
            else{
                $report .= 'BUG in date : ' . $row['NR'] . ' ' . $row['FNAME'] . ' ' . $row['GNAME'] . ' : ' . $row['DATE'] . "\n";
            }
        }
        // percent
        $pWith = round($nWith * 100 / $N, 2);
        $pWithout = round($nWithout * 100 / $N, 2);
        $report .= "N total : $N\n";
        $report .= "N with birth time : $nWith ($pWith %)\n";
        $report .= "N without birth time : $nWithout ($pWithout %)\n";
        $report .= "N without birth time from Gauquelin LERRCP : $nWithoutFromG\n";
        return $report;
    }
    
    // ******************************************************
    /**
        Look at eminence columns : ZITRANG ZITSUM ZITATE ZITSUM_OD
    **/
    private static function look_eminence(){
        $report = '';
        $rows = ErtelSport::loadTmpFile();
        $ranks = []; // assoc array rank => nb records with this rank (ZITRANG)
        $sums = []; // assoc array sums => nb records with this sum (ZITSUM)
        $sources = []; // assoc array sources => nb of records found in this source
        foreach($rows as $row){
            if(!isset($ranks[$row['ZITRANG']])){
                $ranks[$row['ZITRANG']] = 0;
            }
            $ranks[$row['ZITRANG']]++;
            //
            if(!isset($sums[$row['ZITSUM']])){
                $sums[$row['ZITSUM']] = 0;
            }
            $sums[$row['ZITSUM']]++;
            //
            for($i=0; $i < strlen($row['ZITATE']); $i++){
                $char = substr($row['ZITATE'], $i, 1);
                if(!isset($sources[$char])){
                    $sources[$char] = 0;
                }
                $sources[$char]++;
            }
        }
        ksort($ranks);
        ksort($sums);
        ksort($sources);
        $report .= "\n" . print_r($ranks, true) . "\n";
        $report .= "\n" . print_r($sums, true) . "\n";
        $report .= "\n" . print_r($sources, true) . "\n";
        arsort($sources);
        $report .= "\n" . print_r($sources, true) . "\n";
        return $report;
    }
    
    // ******************************************************
    /**
        Look at links to other data sets
        Columns : G_NR PARA_NR CFEPNR CSINR G55
    **/
    private static function look_ids(){
        $report = '';
        $rows = ErtelSport::loadTmpFile();
        $N = 0;
        $res = [
            'G_NR' => 0,
            'G55' => 0,
            'PARA_NR' => 0,
            'CSINR' => 0,
            'CFEPNR' => 0,
        ];
        foreach($rows as $row){
            $N++;
            if(trim($row['G_NR']) != ''){
                $res['G_NR']++;
            }
            if(trim($row['G55']) != ''){
                $res['G55']++;
            }
            if(trim($row['PARA_NR']) != ''){
                $res['PARA_NR']++;
            }
            if(trim($row['CSINR']) != ''){
                $res['CSINR']++;
            }
            if(trim($row['CFEPNR']) != ''){
                $res['CFEPNR']++;
            }
        }
        $p = []; // percentages
        $p['G_NR'] = round($res['G_NR'] * 100 / $N, 2);
        $p['G55'] = round($res['G55'] * 100 / $N, 2);
        $p['PARA_NR'] = round($res['PARA_NR'] * 100 / $N, 2);
        $p['CSINR'] = round($res['CSINR'] * 100 / $N, 2);
        $p['CFEPNR'] = round($res['CFEPNR'] * 100 / $N, 2);
        //
        $labels = [
            'G_NR' => 'Gauquelin',
            'G55' => 'Gauquelin 1955',
            'PARA_NR' => 'Comite Para',
            'CSINR' => 'CSICOP',
            'CFEPNR' => 'CFEPP',
        ];
        //
        $report .= "Total : $N\n";
        foreach($res as $k => $v){
            $report .= str_pad($labels[$k], 16) . str_pad($k, 8) . "$v ({$p[$k]} %)\n";
        }
        return $report;
    }
    
    // ******************************************************
    /**
        Look at mars sectors
        Columns : MARS, MA_, MA12
        Tests if there is a one to one correspondance between the values of the 3 columns
    **/
    private static function look_mars(){
        $report = '';
        $rows = ErtelSport::loadTmpFile();
        $N = 0;
        $res = [];
        for($i=1; $i <= 36; $i++){
            $res[$i] = [
                'MA_' => [],
                'MA12' => [],
            ];
        }
        foreach($rows as $row){
            if(!in_array($row['MA_'], $res[$row['MARS']]['MA_'])){
                $res[$row['MARS']]['MA_'][] = $row['MA_'];
            }
            if(!in_array($row['MA12'], $res[$row['MARS']]['MA12'])){
                $res[$row['MARS']]['MA12'][] = $row['MA12'];
            }
        }
        $one_to_one = true;
        foreach($res as $s36 => $value){
            if(count($value['MA_']) != 1){
                $report .= "Problem for sector36, MA_ : $s36\n";
                $one_to_one = false;
            }
            if(count($value['MA12']) != 1){
                $report .= "Problem for sector36, MA12 : $s36\n";
                $one_to_one = false;
            }
        }
        if(!$one_to_one){
            return;
        }
        $report .= "<table class=\"wikitable margin center\">\n";
        $report .= "    <tr><th>MARS</th><th>MA12</th><th>MA_<br>(importance)</th></tr>\n";
        foreach($res as $s36 => $value){
            $s12 = $value['MA12'][0];
            $ipt = $value['MA_'][0]; // importance
            $tr = '<tr>';
            if($s36 == 9 || $s36 == 36){
                $tr = '<tr class="bold">';
            }
            $report .= "    $tr"
                . "<td>$s36</td>"
                . "<td>$s12</td>"
                . ($ipt == 2 ? "<td class=\"bold\">$ipt</td>" : "<td>$ipt</td>")
                . "</tr>\n";
        }
        $report .= "</table>\n";
        return $report;
    }
    
    // ******************************************************
    /**
        Look at records where QUEL = "GCPAR".
        Result :
        0 GCPAR without PARA_NR
    **/
    private static function look_cp(){
        $report = '';
        $rows = ErtelSport::loadTmpFile();
        $nMiss = 0;
        foreach($rows as $row){
            if($row['QUEL'] != 'GCPAR'){
                continue;
            }
            if($row['PARA_NR'] == ''){
                $nMiss++;
            }
        }
        $report .= "$nMiss GCPAR without PARA_NR\n";
        return $report;
    }
    
    // ******************************************************
    /**
        Compares occupation codes (sport) of Ertel with A1 and CSICOP.
    **/
    private static function look_checkoccu(){
        $report = '';
        $erRows = ErtelSport::loadTmpFile();
        $a1Rows = LERRCP::loadTmpFile_num('A1');
        $csiRows = Irving::loadTmpfile_csid();
        $reportA1 = $reportCSI = '';
        foreach($erRows as $erId => $erRow){
            $erSport = ErtelSport::computeSport($erRow);
            if($erRow['QUEL'] == 'G:A01'){
                $num = str_replace('A1-', '', $erRow['GQID']);
                $a1Sport = $a1Rows[$num]['OCCU'];
                if($a1Sport != $erSport){
                    $reportA1 .= "ertel $erId: $erSport / A1 $num: $a1Sport\n";
                }
// https://www.sudouest.fr/2013/09/03/le-bts-peut-compter-sur-les-anciens-1157295-3566.php?nic
            }
            
        }
        $reportA1 = "========= A1 differences: =========\n";
        return $report;
    }
    
    // ******************************************************
    /**
        Looks for doublons in Gauquelin codes.
        Written after identifying an error while computing Comite Para group.
        Found that Ertel 2408 Le Quellec Yann had a wrong G_NR (A1-1697 instead of A1-1696)
        A1-1697 corresponds to Ertel 2738 Massy Arnaud
        Permitted to identify 6 errors:
            php run-g5.php ertel sport look checkgqid
            === N with GQID: 2883
            A1-574 => 299 (Beaumais Marcel) --- 340 (Benedetto Valere)
            A1-999 => 471 (Blomme Maurice) --- 4356 (Zampini Donato)
            D10-323 => 1348 (Demarco Robert A.) --- 1349 (Demont Richard)
            D10-538 => 2049 (Hamill Dorothy) --- 2051 (Hanburger Christian)
            A1-1697 => 2408 (LeQuellec Yan) --- 2738 (Massy Arnaud)
            D10-822 => 2685 (Maris Roger) --- 2785 (McCullough Earl)
        Doublons found here are fixed in tweak2tmp.
        After the fixes, no more doublons are left:
            php run-g5.php ertel sport look checkgqid
            === N with GQID: 2889
    **/
    private static function look_checkgqid(){
        $report = '';
        $rows = ErtelSport::loadTmpFile();
        $gq = []; // assoc array ; key = GQID ; value = NR (Ertel id)
        foreach($rows as $row){
            $GQID = $row['GQID'];
            if($GQID == ''){
                continue;
            }
            $ERID = $row['NR'];
            if(!isset($gq[$GQID])){
                $gq[$GQID] = [];
            }
            $gq[$GQID][] = $ERID . ' (' . $row['FNAME'] . ' ' . $row['GNAME'] . ')';
        }
        echo '=== N with GQID: ' . count($gq) . "\n";
        foreach($gq as $GQID => $values){
            if(count($values) == 1){
                continue;
            }
            $report .= "$GQID => " . implode(' --- ', $values) . "\n";
        }
        return $report;
    }
    
} // end class
