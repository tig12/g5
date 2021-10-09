<?php
/********************************************************************************
    Adds Gauquelin A6 or A4 NUM in column GQID tmp/newalch/muller-402-it-writers.csv
    using tmp/cura/A6.csv
    Must be executed before import in database.
    
    @license    GPL
    @history    2020-07-18 01:45:49+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\muller\afd1writers;

use g5\G5;
use tiglib\patterns\Command;
use g5\commands\gauq\LERRCP;
use g5\commands\gauq\Cura;

class gauq implements Command {
    
    /** Possible value for parameter 1 **/
    const POSSIBLE_PARAMS = [
        'update' => "Updates column GQID of file data/tmp/newalch/muller-402-it-writers.csv",
        'report' => "Echoes a html table to compare muller-402-it-writers.csv and A6.csv",
        'check' => "Echoes a html table comparing Muller402 to all Gauquelin data, not only A6",
    ];                                                                                                  
    
    /** 
        Assoc array NUM in A6 => ID in M402
        Manual additions to handle journalists
        WARNING - this may lead to erroneous information
        see https://tig12.github.io/gauquelin5/newalch-muller402.html
    **/
    const A6_M402_JO = [
        1354 => 1,
        1489 => 150,
        1568 => 235,
        1578 => 242,
    ];
    
    /** 
        Assoc array NUM in A6 => ID in M402
        Array built after a first execution, using $ambiguous and $nomatch
        Introduction of this array makes $ambiguous empty
    **/
    const A6_M402 = [
        //
        // === from $ambiguous ===
        //
        // Operti Piero Bra CN 1896-02-11
        926 => 317,
        // Tombari Fabio Fano PS 1899-12-21
        985 => 452,
        // Zucca Giuseppe Messina ME 1887-05-01
        1005 => 500,
        //
        // === from $nomatch ===
        //
        // Bontempelli Massimo Como CO 1878-05-11
        837 => 66,
        // Cecchi Emilio Firenze FI 1884-07-04
        852 => 107,
        // Chini Mario BORGO SAN LOREN FI 1876-07-29
        854 => 115,
        // De Libero Libero Fondi LT 1906-09-11
        868 => 158,
        // Della Massea Angelo Baschi TR 1892-12-17
        869 => 159,
        // Gabrielli Aldo Ripatransone AP 1898-04-21
        888 => 206,
        // Gastaldi Mario BEDIZZOLE DI BR BS 1902-08-28
        890 => 216,
        // Giordana Tullio Crema CR 1877-07-15
        895 => 229,
        // Prepositi Clemente Atri TE 1886-02-08
        941 => 362,
        // Profeta Ottavio Aidone EN 1896-10-10
        943 => 364,
        // Repaci Leonida PALMI CALABRIA RC 1898-04-24
        947 => 371,
        // Rosso S Secondo Piermaria Caltanissetta CL 1887-11-30
        953 => 387,
        // Sboto Edoardo Catania CT 1888-05-30
        962 => 407,
        // Traxler Augusto Fauglia PI 1905-06-09
        987 => 456,
        // Umani Giorgio 1892-08-14 Cupramontana AN
        989 => 461,
    ];
    
    
    /** 
        @param $param Array containing one element (a string)
                      Must be one of self::POSSIBLE_PARAMS
        @return Report
    **/
    public static function execute($params=[]): string{
        
        $possibleParams_str = '';
        foreach(self::POSSIBLE_PARAMS as $k => $v){
            $possibleParams_str .= "  '$k' : $v\n";
        }
        if(count($params) == 0){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER : {$params[1]}\n";
        }
        //
        if($params[0] == 'check'){
            return self::check();
        }
        //
        $reportType = $params[0];
        if(!in_array($reportType, array_keys(self::POSSIBLE_PARAMS))){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter :\n$possibleParams_str\n";
        }
        
        $report =  "--- muller402 addA6 ---\n";                                                                     

        $m402_ids = Muller402::loadTmpFile_id(); // Assoc array, keys = Müller id
        $m402_days = []; // Assoc array, keys = birth days
        foreach($m402_ids as $row402){
            $day = substr($row402['DATE'], 0, 10);
            if(!isset($m402_days[$day])){
                $m402_days[$day] = [];
            }
            $m402_days[$day][] = $row402;
        }
        
        // one birth day in A6 corresponds to one in M402
        // regular array - each entry is an assoc array like ['A6' => <A6 record>, 'M402' => <M402 record>]
        $match = [];
        // one birth day in A6 corresponds to several records in M402
        // regular array - each entry is an assoc array like ['A6' => <A6 record>, 'M402' => <array of M402 record>]
        $ambiguous = [];
        // in A6 but not in M402
        // regular array - each entry is a A6 record        
        $nomatch = [];
        $na6 = 0;
        
        // loop on a6, try to match muller402
        $a6 = Cura::loadTmpFile_num('A6');
        $matchingA6NUMs = array_keys(self::A6_M402);
        foreach($a6 as $a6row){
            if($a6row['CY'] != 'IT'){
                continue;
            }
            // NOTE: following test is ok only if tmp file A6 contains only occupation = writer or journalist
            // if code executed before current code modifies this, this leads to erroneous results.
            // (A6 records are modified - imaginative or realist - but this is done after, and modifs are done in db, not in the file)
            if($a6row['OCCU'] != 'writer'){
                continue; // skips JO journalists
                // NOTE : commenting previous line leads to incorrect matches
                // (some journalists of A6 have the same birth date as Muller 402 writers)
                // but shows 4 possible matches - see self::A6_M402_JO comments
            }
            $na6++;
            $dayA6 = substr($a6row['DATE-UT'], 0, 10);
            // no match
            if(!isset($m402_days[$dayA6])){
                if(in_array($a6row['NUM'], $matchingA6NUMs)){
                    // uses self::A6_M402 to remove nomatch
                    $match[] = [
                        'A6' => $a6row,
                        'M402' => $m402_ids[self::A6_M402[$a6row['NUM']]],
                    ];
                    continue;
                }
                $nomatch[] = $a6row;
                continue;
            }
            // ambiguous
            if(count($m402_days[$dayA6]) != 1){
                if(in_array($a6row['NUM'], $matchingA6NUMs)){
                    // uses self::A6_M402 to remove ambiguities
                    $match[] = [
                        'A6' => $a6row,
                        'M402' => $m402_ids[self::A6_M402[$a6row['NUM']]],
                    ];
                    continue;
                }
                // was useful at first execution to build self::A6_M402
                $cur = [];
                $cur['A6'] = $a6row;
                $cur['M402'] = [];
                foreach($m402_days[$dayA6] as $person){                                
                    $cur['M402'][] = $person;
                }
                $ambiguous[] = $cur;
                continue;
            }
            // match
            $match[] = [
                'A6' => $a6row,
                'M402' => $m402_days[$dayA6][0],
            ];
        }
        //
        // This test was positive at first execution, used to build self::A6_M402 from $ambiguous and $nomatch
        // could be removed, kept in case
        //
        if(count($ambiguous) != 0){
            // contained 3 entries when self::A6_M402 was not there
            $report .= "AMBIGUITIES\n";
            foreach($ambiguous as $amb){
                $report .= "\nA6 : " . implode(' ', [
                        $amb['A6']['NUM'],
                        $amb['A6']['FNAME'],
                        $amb['A6']['GNAME'],
                        $amb['A6']['PLACE'],
                        $amb['A6']['C2'],
                        $amb['A6']['DATE-UT'],
                ]) . "\n";
                $report .= 'M402 : ';
                foreach($amb['M402'] as $m402){
                    $report .= "\n    " . implode('  ', [
                            $m402['MUID'],
                            $m402['FNAME'],
                            $m402['GNAME'],
                            $m402['PLACE'],
                            $m402['C2'],
                            $m402['DATE'],
                    ]);
                }
            }
            if(true){
                $report .= "== NO MATCH (in A6 but not in m402)\n";
                foreach($nomatch as $row){
                    $report .= implode("\t", [
                        $row['NUM'],
                        $row['FNAME'],
                        $row['GNAME'],
                        $row['PLACE'],
                        $row['C2'],
                        $row['DATE-UT'],
                    ]) . "\n";
                }
            }
            $report .= "nb IT WR in cura A6 = $na6\n";
            $report .= "match : " . count($match) . "\n";
            $report .= "no match : " . count($nomatch) . "\n";
            return $report;
        }
        
        // Add journalists
        if(true){
            foreach(self::A6_M402_JO as $NUM => $MUID){
                $match[] = [
                    'A6' => $a6[$NUM],
                    'M402' => $m402_ids[$MUID],
                ];
            }
        }
        
        //
        // report
        //
        $nMatch = count($match);
        $nNomatch = count($nomatch);
        if($reportType == 'report'){
            $report .= self::list($match);
            $report .= "A6 contains $na6 italian writers ; $nMatch matches ; $nNomatch nomatch (Appolinaire)\n";
            return $report;
        }
        //
        // update
        //
        $match2 = [];
        foreach($match as $m){
            $NUM = $m['A6']['NUM'];
            $MUID = $m['M402']['MUID'];
            $match2[$MUID] = $NUM;
        }
        
        $res = implode(G5::CSV_SEP, Muller402::TMP_FIELDS) . "\n";
        $rows = Muller402::loadTmpFile();
        $nUpdate = 0;
        foreach($rows as $row){
            $MUID = $row['MUID'];
            //
            //
            // Particular case, for A4
            // TODO implement check() and see if other records come from non A6 file
            if($MUID == 27){
                $row['GQID'] = LERRCP::gauquelinId('A4', '1142');
                $nUpdate++;
            }
            else if($MUID == 42){
                $row['GQID'] = LERRCP::gauquelinId('A1', '268');
                $nUpdate++;
            }
            else if($MUID == 443){
                $row['GQID'] = LERRCP::gauquelinId('A2', '1595');
                $nUpdate++;
            }
            //
            //
            if(isset($match2[$MUID])){
                $row['GQID'] = LERRCP::gauquelinId('A6', $match2[$MUID]);
                $nUpdate++;
            }
            $res .= implode(G5::CSV_SEP, $row) . "\n";
        }
        $outfile = Muller402::tmpFilename();
        file_put_contents($outfile, $res);
        // $report .= "$nMatch matches ; $nNomatch nomatch\n";
        $report .= "Updated GQID in $nUpdate lines of $outfile\n";
        return $report;
    }
    
    
    // ******************************************************
    /**
        Builds a HTML table with matches between A6 and M402
        @return Report
    **/
    private static function list(&$match): string {
        $report = '';
        $report .= "<style>tr.spacer{height:2px} .diff{background:lightyellow;}</style>\n";
        // match
        $report .= '<table class="wikitable margin">' . "\n";
        $report .= "    <tr>";
        $report .= "<th></th>";
        $report .= "<th>Id</th>";
        $report .= "<th>Family name</th>";
        $report .= "<th>Given name</th>";
        $report .= "<th>Birth date</th>";
        $report .= "<th>Birth place</th>";                                       
        $report .= "<th>C2</th>";                                                
        $report .= "</tr>\n";
        $diff = ' class="diff"';
        foreach($match as $line){
            // clean
            $placeA6 = ucWords(strtolower($line['A6']['PLACE']));
            $dateA6 = substr($line['A6']['DATE-UT'], 0, 10);
            $date402 = substr($line['M402']['DATE'], 0, 10);
            //
            $diff_fname = ($line['A6']['FNAME'] != $line['M402']['FNAME'] ? $diff :'');
            $diff_gname = ($line['A6']['GNAME'] != $line['M402']['GNAME'] ? $diff :'');
            $diff_date = ($dateA6 != $date402 ? $diff :'');
            $diff_place = (strtolower($placeA6) != strtolower($line['M402']['PLACE']) ? $diff :'');
            $diff_a2 = ($line['A6']['C2'] != $line['M402']['C2'] ? $diff :'');
            $report .= '    <tr class="spacer"><td colspan="7"></td></tr>' . "\n";
            $report .= '    <tr>'
                . '<th>A6</th>'
                . "<td>" . $line['A6']['NUM'] . '</td>'
                . "<td$diff_fname>" . $line['A6']['FNAME'] . '</td>'
                . "<td$diff_gname>" . $line['A6']['GNAME'] . '</td>'
                . "<td$diff_date>" . $line['A6']['DATE-UT'] . '</td>'
                . "<td$diff_place>" . $placeA6 . '</td>'
                . "<td$diff_a2>" . $line['A6']['C2'] . '</td>'
                . "</tr>\n";
            $report .= '    <tr>'
                . '<th>M402</th>'
                . "<td>" . $line['M402']['MUID'] . '</td>'
                . "<td$diff_fname>" . $line['M402']['FNAME'] . '</td>'
                . "<td$diff_gname>" . $line['M402']['GNAME'] . '</td>'
                . "<td$diff_date>" . $line['M402']['DATE'] . ' ' . $line['M402']['TZO'] . '</td>'
                . "<td$diff_place>" . $line['M402']['PLACE'] . '</td>'
                . "<td$diff_a2>" . $line['M402']['C2'] . '</td>'
                . "</tr>\n";
        }
        $report .= "</table>\n";
        return $report;
    }
    
    /**
        Builds a HTML table with matches between all Gauquelin and M402
        @return Report
    **/
    private static function check(): string {
return "TODO : IMPLEMENT THIS FUNCTION\n";
        $report = '';
        $report .= "<style>tr.spacer{height:2px} .diff{background:lightyellow;}</style>\n";
        // match
        $report .= '<table class="wikitable margin">' . "\n";
        $report .= "    <tr>";
        $report .= "<th></th>";
        $report .= "<th>Id</th>";
        $report .= "<th>Family name</th>";
        $report .= "<th>Given name</th>";
        $report .= "<th>Birth date</th>";
        $report .= "<th>Birth place</th>";                                       
        $report .= "<th>C2</th>";                                                
        $report .= "</tr>\n";
        $diff = ' class="diff"';
        foreach($match as $line){
            // clean
            $placeA6 = ucWords(strtolower($line['A6']['PLACE']));
            $dateA6 = substr($line['A6']['DATE-UT'], 0, 10);
            $date402 = substr($line['M402']['DATE'], 0, 10);
            //
            $diff_fname = ($line['A6']['FNAME'] != $line['M402']['FNAME'] ? $diff :'');
            $diff_gname = ($line['A6']['GNAME'] != $line['M402']['GNAME'] ? $diff :'');
            $diff_date = ($dateA6 != $date402 ? $diff :'');
            $diff_place = (strtolower($placeA6) != strtolower($line['M402']['PLACE']) ? $diff :'');
            $diff_a2 = ($line['A6']['C2'] != $line['M402']['C2'] ? $diff :'');
            $report .= '    <tr class="spacer"><td colspan="7"></td></tr>' . "\n";
            $report .= '    <tr>'
                . '<th>A6</th>'
                . "<td>" . $line['A6']['NUM'] . '</td>'
                . "<td$diff_fname>" . $line['A6']['FNAME'] . '</td>'
                . "<td$diff_gname>" . $line['A6']['GNAME'] . '</td>'
                . "<td$diff_date>" . $line['A6']['DATE-UT'] . '</td>'
                . "<td$diff_place>" . $placeA6 . '</td>'
                . "<td$diff_a2>" . $line['A6']['C2'] . '</td>'
                . "</tr>\n";
            $report .= '    <tr>'
                . '<th>M402</th>'
                . "<td>" . $line['M402']['MUID'] . '</td>'
                . "<td$diff_fname>" . $line['M402']['FNAME'] . '</td>'
                . "<td$diff_gname>" . $line['M402']['GNAME'] . '</td>'
                . "<td$diff_date>" . $line['M402']['DATE'] . ' ' . $line['M402']['TZO'] . '</td>'
                . "<td$diff_place>" . $line['M402']['PLACE'] . '</td>'
                . "<td$diff_a2>" . $line['M402']['C2'] . '</td>'
                . "</tr>\n";
        }
        $report .= "</table>\n";
        return $report;
    }
    
}// end class

