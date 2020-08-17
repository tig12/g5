<?php
/********************************************************************************
    Counts data found on cura.free.fr
    This command is informative only - does not perform any transformation on files
    
    @license    GPL
    @history    2020-07-26 03:32:38+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\commands\cura\all;

use g5\Config;
use g5\patterns\Command;
use g5\commands\cura\Cura;
use g5\commands\cura\CuraRouter;
use tiglib\arrays\csvAssociative;

class count implements Command {
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura all count
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
        
        $datafiles = CuraRouter::computeDatafiles('all');
        $dir = Config::$data['dirs']['5-cura-csv'];
        
        $N = array_fill_keys($datafiles, 0);

        $totalCura = 0;
        foreach($datafiles as $datafile){
            $file = $dir . DS . $datafile . '.csv';
            $rows = csvAssociative::compute($file);
            foreach($rows as $row){
                $N[$datafile]++;
            }
            $totalCura += Cura::CURA_CLAIMS[$datafile][0];
        }
        $totalG5 = array_sum($N);
        
        $res = '';
        $res .= '<table class="count-A wikitable margin">' . "\n";
        $res .= '<tr>'
             . '<th>File</th>'
             . '<th>Cura claims</th>'
             . '<th>N <sub>Cura</sub></th>'
             . '<th>N <sub>g5</sub></th>'
             . '<th>&Delta;</th>'
             . '<th>Explanation</th>'
             . "</tr>\n";
        foreach($datafiles as $datafile){
            $delta = Cura::CURA_CLAIMS[$datafile][0] - $N[$datafile];
            $res .= '<tr>';
            $res .= '<td>' . $datafile . '</td>';
            $res .= '<td>' . Cura::CURA_CLAIMS[$datafile][1] . '</td>';
            $res .= '<td class="right">' . number_format(Cura::CURA_CLAIMS[$datafile][0], 0, '.', ' ') . '</td>';
            $res .= '<td class="right bold">' . number_format($N[$datafile], 0, '.', ' ') . '</td>';
            $res .= '<td>' . $delta . '</td>';
            $res .= '<td>' . Cura::CURA_CLAIMS[$datafile][2] . '</td>';
            $res .= '</tr>' . "\n";
        }
        
        $res .= '<tr class="big2">';
        $res .= '<td colspan="2" class="right">TOTAL</td>';
        $res .= '<td class="right">' . number_format($totalCura, 0, '.', ' ') . '</td>';
        $res .= '<td class="right bold">' . number_format($totalG5, 0, '.', ' ') . '</td>';
        $res .= '</tr>' . "\n";
        $res .= "</table>\n";
        return $res . "\n";
    }
    
}// end class    

