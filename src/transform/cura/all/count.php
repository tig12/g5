<?php
/********************************************************************************
    Look at presence of different fields in csv files of data/5-tmp.
    
    @license    GPL
    @history    2019-06-07 22:29:12+02:00, Thierry Graff : creation
********************************************************************************/
namespace g5\transform\cura\all;

use g5\Config;
use g5\patterns\Command;
use g5\transform\cura\CuraRouter;
use tiglib\arrays\csvAssociative;

class count implements Command{
    
    // *****************************************
    // Implementation of Command
    /** 
        Called by : php run-g5.php cura D6 all
        @param $params  array with 2 elements : datafile and command
        @return         Empty string ; echoes the commands' reports
    **/
    public static function execute($params=[]): string{
        
        if(count($params) > 2){
            return "INVALID PARAMETER : " . $params[2] . " - csvcount doesn't need this parameter\n";
        }
        
        $datafiles = array_merge(
            CuraRouter::computeDatafiles('A'),
            CuraRouter::computeDatafiles('D6'),
            CuraRouter::computeDatafiles('D10'),
            CuraRouter::computeDatafiles('E1'),
            CuraRouter::computeDatafiles('E3')
        );
        
        $dir = Config::$data['dirs']['5-cura-csv'];
        
        // associative arrays holding the counts
        // keys = datafiles ; values = counts
        $N = $nGNAME
           = $nGEOID
           = $nPLACE
           = $nDTU
           = $nC2
           = array_fill_keys($datafiles, 0);
        $pGNAME = $pGEOID = $pPLACE = $pDTU = $pC2  = 0;
        
        foreach($datafiles as $datafile){
            $file = $dir . DS . $datafile . '.csv';
            $rows = csvAssociative::execute($file);
            foreach($rows as $row){
//echo "\n<pre>"; print_r($row); echo "</pre>\n"; exit;
                $N[$datafile]++;
                if(isset($row['GNAME']) && $row['GNAME']){
                    $nGNAME[$datafile]++;
                }
                if(isset($row['GEOID']) && $row['GEOID']){
                    $nGEOID[$datafile]++;
                }
                if(isset($row['PLACE']) && $row['PLACE']){
                    $nPLACE[$datafile]++;
                }
                if(isset($row['DTU']) && $row['DTU']){
                    $nDTU[$datafile]++;
                }
                if(isset($row['C2']) && $row['C2']){
                    $nC2[$datafile]++;
                }
            }
            
        }
        
        $res = '';
        $res .= '<table class="count wikitable margin">' . "\n";
        $res .= '<tr>'
             . '<th></th>'
             . '<th>N</th>'
             . '<th colspan="2">GNAME</th>'
             . '<th colspan="2">GEOID</th>'
             . '<th colspan="2">PLACE</th>'
             . '<th colspan="2">DTU</th>'
             . '<th colspan="2">C2</th></tr>' . "\n";
        foreach($datafiles as $datafile){
            $res .= '<tr>';
            $res .= '<td>' . $datafile . '</td>';
            $res .= '<td>' . $N[$datafile] . '</td>';
            //
            $p = $nGNAME[$datafile] * 100 / $N[$datafile];
            $pGNAME += $p;
            $res .= '<td>' . $nGNAME[$datafile] . '</td>';
            $res .= '<td>' . round($p, 2) . ' %</td>';
            //
            $p = $nGEOID[$datafile] * 100 / $N[$datafile];
            $pGEOID += $p;
            $res .= '<td>' . $nGEOID[$datafile] . '</td>';
            $res .= '<td>' . round($p, 2) . ' %</td>';
            //
            $p = $nPLACE[$datafile] * 100 / $N[$datafile];
            $pPLACE += $p;
            $res .= '<td>' . $nPLACE[$datafile] . '</td>';
            $res .= '<td>' . round($p, 2) . ' %</td>';
            //
            $p = $nDTU[$datafile] * 100 / $N[$datafile];
            $pDTU += $p;
            $res .= '<td>' . $nDTU[$datafile] . '</td>';
            $res .= '<td>' . round($p, 2) . '</td>';
            //
            $p = $nC2[$datafile] * 100 / $N[$datafile];
            $pC2 += $p;
            $res .= '<td>' . $nC2[$datafile] . '</td>';
            $res .= '<td>' . round($p, 2) . ' %</td>';
            //
            $res .= '</tr>' . "\n";
        }
        //
        $pGNAME = $pGNAME / count($datafiles);
        $pGEOID = $pGEOID / count($datafiles);
        $pPLACE = $pPLACE / count($datafiles);
        $pDTU = $pDTU / count($datafiles);
        $pC2 = $pC2 / count($datafiles);
        $res .= '<tr>';
        $res .= '<td>TOTAL</td>';
        $res .= '<td>' . array_sum($N) . '</td>';
        $res .= '<td>' . array_sum($nGNAME) . '</td>';
        $res .= '<td>' . round($pGNAME, 2) . ' %</td>';
        $res .= '<td>' . array_sum($nGEOID) . '</td>';
        $res .= '<td>' . round($pGEOID, 2) . ' %</td>';
        $res .= '<td>' . array_sum($nPLACE) . '</td>';
        $res .= '<td>' . round($pPLACE, 2) . ' %</td>';
        $res .= '<td>' . array_sum($nDTU) . '</td>';
        $res .= '<td>' . round($pDTU, 2) . ' %</td>';
        $res .= '<td>' . array_sum($nC2) . '</td>';
        $res .= '<td>' . round($pC2, 2) . ' %</td>';
        $res .= '</tr>' . "\n";
        $res .= "</table>\n";
        return $res . "\n";
    }
    
}// end class    

