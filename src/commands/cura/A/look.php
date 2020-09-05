<?php
/********************************************************************************
    Generates stats about csv files of 5-cura-csv/.
    
    This command is informative only - does not perform any transformation on files
    
    @license    GPL
    @history    2019-06-07 22:29:12+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\A;

use g5\Config;
use g5\patterns\Command;
use g5\commands\cura\CuraRouter;
use tiglib\arrays\csvAssociative;

class look implements Command {
    
    /** 
        Possible values of the command, for ex :
        php run-g5.php newalch ertel4391 look eminence
    **/
    const POSSIBLE_PARAMS = [
        'count',
    ];
    
    // *****************************************
    // Implementation of Command
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
        $method = 'look_' . $param;
        return self::$method();
    }
        
    // ******************************************************
    /**
        Counts records in data/tmp/cura files.
    **/
    private static function look_count(){
        $datafiles = CuraRouter::computeDatafiles('A');
        $dir = Config::$data['dirs']['tmp'] . DS . 'cura';
        
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
                if($row['DATE']){
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
    
}// end class    

