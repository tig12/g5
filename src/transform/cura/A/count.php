<?php
/********************************************************************************
    Generates stats about csv files of 5-cura-csv/.
    
    This command is informative only - does not perform any transformation on files
    
    @license    GPL
    @history    2019-06-07 22:29:12+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\A;

use g5\Config;
use g5\patterns\Command;
use g5\transform\cura\CuraRouter;
use tiglib\arrays\csvAssociative;

class count implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura A count
        Note : can be indifferently called with any datafile of serie A
        So : php run-g5.php cura A count
        Is the same as : php run-g5.php cura A1 count
        @param $params  array with 2 elements : datafile and command name
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - csvcount doesn't need this parameter\n";
        }
        
        $datafiles = CuraRouter::computeDatafiles('A');
        $dir = Config::$data['dirs']['5-cura-csv'];
        
        // associative arrays holding the counts
        // keys = datafiles ; values = counts
        $N = $nNAME
           = $nDATE_C
           = $nGEOID
           = array_fill_keys($datafiles, 0);
        $pNAME = $pDATE_C = $pGEOID = 0;
        $missNAME = $missDATE_C = $missGEOID = 0;
        
        foreach($datafiles as $datafile){
            $file = $dir . DS . $datafile . '.csv';
            $rows = csvAssociative::compute($file);
            foreach($rows as $row){
                
                $N[$datafile]++;
                
                if(substr($row['FNAME'], 0, 11) != 'Gauquelin-A'){
                    $nNAME[$datafile]++;
                }
                if($row['DATE_C']){
                    $nDATE_C[$datafile]++;
                }
                if($row['GEOID']){
                    $nGEOID[$datafile]++;
                }
            }
            
        }
        
        $res = '';
        $res .= '<table class="count-A wikitable margin">' . "\n";
        $res .= '<tr>'
             . '<th colspan="2"></th>'
             . '<th colspan="3">NAME</th>'
             . '<th colspan="3">DATE_C</th>'
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
            $res .= '<tr>';
            $res .= '<td>' . $datafile . '</td>';
            $res .= '<td>' . $N[$datafile] . '</td>';
            //
            $p = $nNAME[$datafile] * 100 / $N[$datafile];
            $pNAME += $p;
            $miss = $N[$datafile] - $nNAME[$datafile];
            $missNAME += $miss;
            $res .= '<td>' . $nNAME[$datafile] . '</td>';
            $res .= '<td>' . $miss . '</td>';
            $res .= '<td>' . round($p, 2) . ' %</td>';
            //
            $p = $nDATE_C[$datafile] * 100 / $N[$datafile];
            $pDATE_C += $p;
            $miss = $N[$datafile] - $nDATE_C[$datafile];
            $missDATE_C += $miss;
            $res .= '<td>' . $nDATE_C[$datafile] . '</td>';
            $res .= '<td>' . $miss . '</td>';
            $res .= '<td>' . round($p, 2) . ' %</td>';
            //
            $p = $nGEOID[$datafile] * 100 / $N[$datafile];
            $pGEOID += $p;
            $miss = $N[$datafile] - $nGEOID[$datafile];
            $missGEOID += $miss;
            $res .= '<td>' . $nGEOID[$datafile] . '</td>';
            $res .= '<td>' . $miss . '</td>';
            $res .= '<td>' . round($p, 2) . ' %</td>';
            //
            $res .= '</tr>' . "\n";
        }
        //
        $pNAME = $pNAME / count($datafiles);
        $pDATE_C = $pDATE_C / count($datafiles);
        $pGEOID = $pGEOID / count($datafiles);
        $res .= '<tr>';
        $res .= '<td>TOTAL</td>';
        $res .= '<td>' . array_sum($N) . '</td>';
        //
        $res .= '<td>' . array_sum($nNAME) . '</td>';
        $res .= '<td>' . $missNAME . '</td>';
        $res .= '<td>' . round($pNAME, 2) . ' %</td>';
        //
        $res .= '<td>' . array_sum($nDATE_C) . '</td>';
        $res .= '<td>' . $missDATE_C . '</td>';
        $res .= '<td>' . round($pDATE_C, 2) . ' %</td>';
        //
        $res .= '<td>' . array_sum($nGEOID) . '</td>';
        $res .= '<td>' . $missGEOID . '</td>';
        $res .= '<td>' . round($pGEOID, 2) . ' %</td>';
        $res .= '</tr>' . "\n";
        $res .= "</table>\n";
        return $res . "\n";
    }
    
}// end class    

