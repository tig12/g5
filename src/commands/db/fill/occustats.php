<?php
/******************************************************************************
    
    Fills field N of table occu.
    
    @license    GPL
    @history    2021-08-01 10:19:10+02:00, Thierry Graff : Creation
********************************************************************************/
namespace g5\commands\db\fill;

use g5\patterns\Command;
use g5\model\DB5;
use g5\model\Occupation;

class occustats implements Command {
    
    /** 
        @param  $params Empty array
        @return report.
    **/
    public static function execute($params=[]): string {
        if(count($params) != 0){
            return "INVALID USAGE - useless parameter: {$params[0]}\n";
        }
        
        $report = "--- db fill occustats ---\n";
        
        $allAncestors = Occupation::getAllAncestors();
        $N = array_fill_keys(array_keys($allAncestors), 0); // slug person => Nb of occurences
        
        $dblink = DB5::getDbLink();
        $stmt = $dblink->query("select occus from person");
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row){
            $occus = json_decode($row['occus'], true);
            foreach($occus as $occu){
                $N[$occu]++;
                foreach($allAncestors[$occu] as $ancestor){
                    $N[$ancestor]++;
                }
            }
//break;
//echo "\n<pre>"; print_r($N); echo "</pre>\n"; exit;
        }
        
//exit;
        //
        // insert
        //
        $stmt = $dblink->prepare("update occu set N=? where slug=?");
        foreach($N as $slug => $n){
            $stmt->execute([$n, $slug]);
        }
        return $report;
    }
    
} // end class
