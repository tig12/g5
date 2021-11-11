<?php
/********************************************************************************
    Code to examine data/tmp/ertel/ertel-4384-athletes.csv
    This code is not part of any build process - only informative.
    
    @license    GPL
    @history    2019-05-11 18:58:50+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\ertel\ertel4391;

use tiglib\patterns\Command;

class look implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php ertel ertel4391 look eminence
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
        self::$method();
        return '';
    }
    
    
    // ******************************************************
    /**
        Look at SPORT and IG columns.
    **/
    private static function look_sport(){
        $rows = Ertel4391::loadTmpFile();
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
                echo "Incoherent association sport / IG, line " . $row['NR'] . ' ' . $row['FNAME'] . ' ' . $row['GNAME']
                    . ' : ' . $sport . ' ' . $row['IG'] . "\n";
            }
            if(strlen($sport) == 3){
                echo 'Incoherent sport code : ' . $sport . ' ' . $row['NR'] . ' ' . $row['FNAME']
                        . ' ' . $row['GNAME'] . ' ' . $row['IG'] . "\n";
            }
        }
        // print
        ksort($res);
        foreach($res as $sport => $details){
            echo "{$details['IG']} $sport : {$details['n']}\n";
        }
    }
    
    // ******************************************************
    /**
        Look at QUEL column.
    **/
    private static function look_quel(){
        $rows = Ertel4391::loadTmpFile();
        $res = []; // assoc codes => nb of records with this code
        foreach($rows as $row){
            if(!isset($res[$row['QUEL']])){
                $res[$row['QUEL']] = 0;
            }
            $res[$row['QUEL']] ++;
        }
        ksort($res);
        echo "\n"; print_r($res); echo "\n";
    }
    
    // ******************************************************
    /**
        Look at DATE column.
    **/
    private static function look_date(){
        $rows = Ertel4391::loadTmpFile();
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
                echo 'BUG in date : ' . $row['NR'] . ' ' . $row['FNAME'] . ' ' . $row['GNAME'] . ' : ' . $row['DATE'] . "\n";
            }
        }
        // percent
        $pWith = round($nWith * 100 / $N, 2);
        $pWithout = round($nWithout * 100 / $N, 2);
        echo "N total : $N\n";
        echo "N with birth time : $nWith ($pWith %)\n";
        echo "N without birth time : $nWithout ($pWithout %)\n";
        echo "N without birth time from Gauquelin LERRCP : $nWithoutFromG\n";
    }
    
    // ******************************************************
    /**
        Look at eminence columns : ZITRANG ZITSUM ZITATE ZITSUM_OD
    **/
    private static function look_eminence(){
        $rows = Ertel4391::loadTmpFile();
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
        echo "\n"; print_r($ranks); echo "\n";
        echo "\n"; print_r($sums); echo "\n";
        echo "\n"; print_r($sources); echo "\n";
        arsort($sources);
        echo "\n"; print_r($sources); echo "\n";
    }
    
    // ******************************************************
    /**
        Look at links to other data sets
        Columns : G_NR PARA_NR CFEPNR CSINR G55
    **/
    private static function look_ids(){
        $rows = Ertel4391::loadTmpFile();
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
        echo "Total : $N\n";
        foreach($res as $k => $v){
            echo str_pad($labels[$k], 16) . str_pad($k, 8) . "$v ({$p[$k]} %)\n";
        }
        
        
    }
    
    // ******************************************************
    /**
        Look at mars sectors
        Columns : MARS, MA_, MA12
        Tests if there is a one to one correspondance between the values of the 3 columns
    **/
    private static function look_mars(){
        $rows = Ertel4391::loadTmpFile();
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
                echo "Problem for sector36, MA_ : $s36\n";
                $one_to_one = false;
            }
            if(count($value['MA12']) != 1){
                echo "Problem for sector36, MA12 : $s36\n";
                $one_to_one = false;
            }
        }
        if(!$one_to_one){
            return;
        }
        echo "<table class=\"wikitable margin center\">\n";
        echo "    <tr><th>MARS</th><th>MA12</th><th>MA_<br>(importance)</th></tr>\n";
        foreach($res as $s36 => $value){
            $s12 = $value['MA12'][0];
            $ipt = $value['MA_'][0]; // importance
            $tr = '<tr>';
            if($s36 == 9 || $s36 == 36){
                $tr = '<tr class="bold">';
            }
            echo "    $tr"
                . "<td>$s36</td>"
                . "<td>$s12</td>"
                . ($ipt == 2 ? "<td class=\"bold\">$ipt</td>" : "<td>$ipt</td>")
                . "</tr>\n";
        }
        echo "</table>\n";
    }
    
    // ******************************************************
    /**
        Look at records where QUEL = "GCPAR".
        Result :
        0 GCPAR without PARA_NR
    **/
    private static function look_cp(){
        $rows = Ertel4391::loadTmpFile();
        $nMiss = 0;
        foreach($rows as $row){
            if($row['QUEL'] != 'GCPAR'){
                continue;
            }
            if($row['PARA_NR'] == ''){
                $nMiss++;
            }
        }
        echo "$nMiss GCPAR without PARA_NR\n";
    }
    
    // ******************************************************
    /**
        Compares occupation codes (sport) of Ertel with A1 and CSICOP.
    **/
    private static function look_checkoccu(){
        die("TO IMPLEMENT\n");
    }
    
} // end class
