<?php
/********************************************************************************
    Adds columns GQID ERID CPID in data/tmp/cfepp/cfepp-1120-nienhuys.csv
    Uses Ertel 4391 file.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2022-03-27 23:30:58+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\cfepp\final3;

use g5\G5;
use tiglib\patterns\Command;

class ids implements Command {
    
    /** 
        @param $param Empty array
        @return Report
    **/
    public static function execute($params=[]): string{
        if(count($params) > 0){
            return "USELESS PARAMETER : {$params[1]}\n";
        }
        
        $report =  "--- cfepp final3 ids ---\n";                                                                     

        $m402_ids = M1writers::loadTmpFile_id(); // Assoc array, keys = Müller id
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
        
        // loop on a6, try to match M402
        $a6 = LERRCP::loadTmpFile_num('A6');
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
        
        $res = implode(G5::CSV_SEP, M1writers::TMP_FIELDS) . "\n";
        $rows = M1writers::loadTmpFile();
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
        $outfile = M1writers::tmpFilename();
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
    
} // end class
