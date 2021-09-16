<?php
/********************************************************************************
    Generates informations about Cura A files.
    
    Informative only - does not perform any transformation on files
    
    @license    GPL
    @history    2019-06-07 22:29:12+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\A;

use g5\app\Config;
use tiglib\patterns\Command;
use g5\commands\cura\Cura;
use g5\commands\cura\CuraNames;
use g5\commands\cura\CuraRouter;
use tiglib\arrays\csvAssociative;

class look implements Command {
    
    /** 
        Possible values of the command
    **/
    const POSSIBLE_PARAMS = [
        'count',
        'lists',
        'names',
    ];
    
    /** 
        Called by       : php run-g5.php cura A count
        So              : php run-g5.php cura A look count
        Is the same as  : php run-g5.php cura A1 count
        @param $params  array with 3 elements :
                        - the datafile to process (ex A1) 
                        - name of this command (useless here) 
                        - the action to perform
        @return         String containing the html tables with the comparisons
    **/
    public static function execute($params=[]): string {
        if(count($params) > 3){
            return "INVALID PARAMETER : " . $params[3] . " - this command doesn't need this parameter\n";
        }
        $possibleParams_str = implode(', ', self::POSSIBLE_PARAMS);
        if(count($params) != 3){
            return "PARAMETER MISSING\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $param = $params[2];
        if(!in_array($param, self::POSSIBLE_PARAMS)){
            return "INVALID PARAMETER\n"
                . "Possible values for parameter : $possibleParams_str\n";
        }
        $datafile = $params[0];
        $method = 'look_' . $param;
        return self::$method($datafile);
    }
    
    /**
        Counts records in data/tmp/cura files.
        @param  $datafile Useless here
    **/
    private static function look_count($datafile){
        $datafiles = CuraRouter::computeDatafiles('A');
        $dir = Cura::tmpDirname();
        
        $N = $nNAME = $nDATE = $nGEOID = array_fill_keys($datafiles, 0);
        $missNAME = $missDATE = $missGEOID = 0;
        
        foreach($datafiles as $datafile){
            $file = $dir . DS . $datafile . '.csv';
            $rows = csvAssociative::compute($file);
            foreach($rows as $row){
                
                $N[$datafile]++;
                
                if(substr($row['FNAME'], 0, 11) != 'Gauquelin-A'){
                    $nNAME[$datafile]++;
                }
                if($row['DATE-UT']){
                    $nDATE[$datafile]++;
                }
                if($row['GEOID']){
                    $nGEOID[$datafile]++;
                }
            }
            
        }
        
        $report = '';
        $report .= '<table class="count-A wikitable margin">' . "\n";
        $report .= '<tr>'
             . '<th colspan="2"></th>'
             . '<th colspan="3">NAME</th>'
             . '<th colspan="3">DATE</th>'
             . '<th colspan="3">GEOID</th>'
             . "</tr>\n"
             . '<tr>'
             . '<th></th>'
             . '<th>N</th>'
             . '<th>N ok</th><th>N miss</th><th>% ok</th>'
             . '<th>N ok</th><th>N miss</th><th>% ok</th>'
             . '<th>N ok</th><th>N miss</th><th>% ok</th>'
             . "</tr>\n";
        foreach($datafiles as $datafile){
            $report .= '<tr>';
            $report .= '<td>' . $datafile . '</td>';
            $report .= '<td>' . $N[$datafile] . '</td>';
            //
            $p = $nNAME[$datafile] * 100 / $N[$datafile];
            $miss = $N[$datafile] - $nNAME[$datafile];
            $missNAME += $miss;
            $report .= '<td>' . $nNAME[$datafile] . '</td>';
            $report .= '<td>' . $miss . '</td>';
            $report .= '<td>' . round($p, 2) . ' %</td>';
            //
            $p = $nDATE[$datafile] * 100 / $N[$datafile];
            $miss = $N[$datafile] - $nDATE[$datafile];
            $missDATE += $miss;
            $report .= '<td>' . $nDATE[$datafile] . '</td>';
            $report .= '<td>' . $miss . '</td>';
            $report .= '<td>' . round($p, 2) . ' %</td>';
            //
            $p = $nGEOID[$datafile] * 100 / $N[$datafile];
            $miss = $N[$datafile] - $nGEOID[$datafile];
            $missGEOID += $miss;
            $report .= '<td>' . $nGEOID[$datafile] . '</td>';
            $report .= '<td>' . $miss . '</td>';
            $report .= '<td>' . round($p, 2) . ' %</td>';
            //
            $report .= '</tr>' . "\n";
        }
        //
        $totalAll = array_sum($N);
        $totalNAME = array_sum($nNAME);
        $totalDATE = array_sum($nNAME);
        $totalGEOID = array_sum($nGEOID);
        $pNAME = $totalNAME * 100 / $totalAll;
        $pDATE = $totalDATE * 100 / $totalAll;
        $pGEOID = $totalGEOID * 100 / $totalAll;
        $report .= '<tr>';
        $report .= '<td>TOTAL</td>';
        $report .= '<td>' . $totalAll . '</td>';
        //
        $report .= '<td>' . $totalNAME . '</td>';
        $report .= '<td>' . $missNAME . '</td>';
        $report .= '<td>' . round($pNAME, 2) . ' %</td>';
        //
        $report .= '<td>' . $totalDATE . '</td>';
        $report .= '<td>' . $missDATE . '</td>';
        $report .= '<td>' . round($pDATE, 2) . ' %</td>';
        //
        $report .= '<td>' . $totalGEOID . '</td>';
        $report .= '<td>' . $missGEOID . '</td>';
        $report .= '<td>' . round($pGEOID, 2) . ' %</td>';
        $report .= '</tr>' . "\n";
        $report .= "</table>\n";
        return $report . "\n";
    }
    
    /**
        Tests if two lists contained in 902gdA*y.html files (ex 902gdA1y.html) contain the same number of elements.
        - First list contains the detailed birth data but not the names.
        - Second list is chronologial order list with names.
        
        Result (execution 2019-03-31) : 
        Serie A1 - nb of elements : list1 : 2087 - list2 : 2082
        Serie A2 - nb of elements : list1 : 3643 - list2 : 3637
        Serie A3 - nb of elements : list1 : 3046 - list2 : 2963
        Serie A4 - nb of elements : list1 : 2720 - list2 : 1469
        Serie A5 - nb of elements : list1 : 2410 - list2 : 1400
        Serie A6 - nb of elements : list1 : 2026 - list2 : 1337
        
        Conclusion : the lists differ.
    **/
    private static function look_lists($datafile){
        $report =  "Comparing the two lists in file $datafile - ";
        $raw = Cura::loadRawFile($datafile);
        //
        // 1 - parse first list (without names)
        //
        $res1 = [];
        preg_match('#<pre>\s*(YEA.*?CITY)\s*(.*?)\s*</pre>#sm', $raw, $m);
        $lines1 = explode("\n", $m[2]);
        foreach($lines1 as $line1){
            $fields = explode(Cura::HTML_SEP, $line1);
            $day = Cura::computeDay(['YEA' => $fields[0], 'MON' => $fields[1], 'DAY' => $fields[2]]);
            $res1[] = $day;
        }
        //
        // 2 - Parse chronologial list with names
        //
        $res2 = [];
        preg_match('#CHRONOLOGICAL ORDER \(with names\)</b></font>\s*?<div id="contenu2"><pre>\s*?YEA.*?NAME\s*(.*?)\s*</pre>#smi', $raw, $m);
        $lines2 = explode("\n", $m[1]);
        foreach($lines2 as $line2){
            $fields = explode(Cura::HTML_SEP, $line2);
            $day = Cura::computeDay(['YEA' => $fields[0], 'MON' => $fields[1], 'DAY' => $fields[2]]);
            $res2[] = $day;
        }
        //
        // 3 - Compare both lists
        //
        $report .= 'nb of elements : ';
        $report .= 'list1 : ' . count($res1);
        $report .= ' - list2 : ' . count($res2) . "\n";
        //
        // 4 - Report
        //
        return $report;
    }
    
    /** 
        Count the names present in 902gdN.html
        Result of execution 2017-04-27 12:05:54+02:00
        A1 : 2082
        A2 : 3637
        A3 : 2963
        A4 : 2709
        A5 : 2398
        A6 : 1338
        D10 : 1396
        D6 : 449
        E1 : 2153
        E3 : 1539
        
        Decision : use 902gdN.html (contain the same nb of names as in *y.html files)
        because it permits to write only one parsing for all files.
        @param $datafile    Useless here
        @history    2017-04-27 11:16:42+02:00, Thierry Graff : creation   
        @history    2020-09-07, Thierry Graff : Integration to g5\commands
        
    **/
    private static function look_names($datafile){
        $report = '';
        $names = CuraNames::parse();
        ksort($names);
        $report .= "Number of names in the different files\n";                                                                        
        foreach($names as $k => $v){
            $report .= $k . ' : ' . count($v) . "\n";
        }
        return $report;
    }
    
} // end class

