<?php
/********************************************************************************
    Adds Gauquelin (A6) id to Müller 402
    
    @license    GPL
    @history    2020-07-18 01:45:49+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\newalch\muller402;

use g5\Config;
use g5\patterns\Command;
use g5\commands\cura\Cura;

class lookA6 implements Command {
    
    /** 
        Assoc array NUM in A6 => ID in M402
        Array built after a first execution, using $ambiguous and $nomatch
        Introduction of this array makes $ambiguous empty
    **/
    const MATCHING = [
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
        // Cecchi Emilio Firenze FI 1884-07-04
        852 => 107,
        // Chini Mario BORGO SAN LOREN FI 1876-07-29
        854 => 115,
        // De Libero Libero Fondi LT 1906-09-11
        858 => 158,
        // Della Massea Angelo  Baschi TR 1892-12-17
        869 => 159,
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
        // Rosso S Secondo Piermaria  Caltanissetta CL 1887-11-30
        953 => 387,
        // Sboto Edoardo Catania CT 1888-05-30
        962 => 407,
        // De Libero Libero Fondi LT 1906-09-11
        868 => 158,
    ];
    
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php newalch muller402 lookA6
        @param $params empty array 
        @return Report
    **/
    public static function execute($params=[]): string{
        
        if(count($params) != 0){
            return "WRONG USAGE : this command does not take parameter\n";
        }
        
        $m402_days = []; // Assoc array, keys = birth days
        $m402_ids = []; // Assoc array, keys = M402 ids - used to fill match from self::MATCHING
        
        $lines = Muller402::loadTmpFile();
        foreach($lines as $line){
            $day = substr($line['DATE'], 0, 10);
            if(!isset($m402_days[$day])){
                $m402_days[$day] = [];
            }
            $m402_days[$day][] = $line;
            $m402_ids[$line['MUID']] = $line;
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
        $a6 = Cura::loadTmpFile('A6');
        $matching_keys = array_keys(self::MATCHING);
        foreach($a6 as $a6row){
            $a6row['PLACE'] = ucWords(strtolower($a6row['PLACE']));
            if($a6row['CY'] != 'IT'){
                continue;
            }
            if($a6row['OCCU'] != 'WR'){
                continue; // skips JO journalists
            }
            $na6++;
            $dayA6 = substr($a6row['DATE'], 0, 10);
            // no match
            if(!isset($m402_days[$dayA6])){
                
                if(in_array($a6row['NUM'], $matching_keys)){
                    // uses self::MATCHING to remove nomatch
                    $match[] = [
                        'A6' => $a6row,
                        'M402' => $m402_ids[self::MATCHING[$a6row['NUM']]],
                    ];
                    continue;
                }
                $nomatch[] = $a6row;
                continue;
            }
            // ambiguous
            if(count($m402_days[$dayA6]) != 1){
                if(in_array($a6row['NUM'], $matching_keys)){
                    // uses self::MATCHING to remove ambiguities
                    $match[] = [
                        'A6' => $a6row,
                        'M402' => $m402_ids[self::MATCHING[$a6row['NUM']]],
                    ];
                    continue;
                }
                // was useful at first execution to build self::MATCHING
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
        // report
        //
        $report = "A6 contains $na6 italian writers\n";
        $report .= self::list($match);
        return $report;
        
        //
        // Following code is now useless
        //
        // was used to build self::MATCHING from $ambiguous and $nomatch
        // can be removed, kept in case
        if(count($ambiguous) != 0){
            // contained 3 entries when self::MATCHING was not there
            $report .= "AMBIGUITIES\n";
            foreach($ambiguous as $amb){
                $report .= "\nA6 : " . implode(' ', [
                        $amb['A6']['NUM'],
                        $amb['A6']['FNAME'],
                        $amb['A6']['GNAME'],
                        $amb['A6']['PLACE'],
                        $amb['A6']['C2'],
                        $amb['A6']['DATE'],
                ]) . "\n";
                $report .= 'M402 : ';
                foreach($amb['M402'] as $m402){
                    $report .= "\n    " . implode('  ', [
                            $m402->data['ids']['muller402'],
                            $m402->data['name']['family'],
                            $m402->data['name']['given'],
                            $m402->data['birth']['place']['name'],
                            $m402->data['birth']['place']['c2'],
                            $m402->data['birth']['date'],
                    ]);
                }
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
                    $row['DATE'],
                ]) . "\n";
            }
        }
        
        $report .= "nb IT WR in cura A6 = $na6\n";
        $report .= "match : " . count($match) . "\n";
        $report .= "no match : " . count($nomatch) . "\n";
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
            $date402 = $line['M402']['DATE'] . $line['M402']['TZO'];
            $diff_fname = ($line['A6']['FNAME'] != $line['M402']['FNAME'] ? $diff :'');
            $diff_gname = ($line['A6']['GNAME'] != $line['M402']['GNAME'] ? $diff :'');
            $diff_date = ($line['A6']['DATE'] != $date402 ? $diff :'');
            $diff_place = ($line['A6']['PLACE'] != $line['M402']['PLACE'] ? $diff :'');
            $diff_a2 = ($line['A6']['C2'] != $line['M402']['C2'] ? $diff :'');
            $report .= '    <tr class="spacer"><td colspan="7"></td></tr>' . "\n";
            $report .= '    <tr>'
                . '<th>A6</th>'
                . "<td>" . $line['A6']['NUM'] . '</td>'
                . "<td$diff_fname>" . $line['A6']['FNAME'] . '</td>'
                . "<td$diff_gname>" . $line['A6']['GNAME'] . '</td>'
                . "<td$diff_date>" . $line['A6']['DATE'] . '</td>'
                . "<td$diff_place>" . $line['A6']['PLACE'] . '</td>'
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

